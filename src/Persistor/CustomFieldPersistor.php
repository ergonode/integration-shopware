<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Persistor;

use Ergonode\IntegrationShopware\Api\AttributeStreamResultsProxy;
use Ergonode\IntegrationShopware\Provider\CustomFieldProvider;
use Ergonode\IntegrationShopware\Transformer\CustomFieldTransformer;
use Ergonode\IntegrationShopware\Util\Constants;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetDefinition;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetEntity;
use Shopware\Core\System\CustomField\CustomFieldDefinition;

class CustomFieldPersistor
{
    private EntityRepositoryInterface $customFieldRepository;

    private EntityRepositoryInterface $customFieldSetRepository;

    private CustomFieldTransformer $customFieldTransformer;

    private CustomFieldProvider $customFieldProvider;

    public function __construct(
        EntityRepositoryInterface $customFieldRepository,
        EntityRepositoryInterface $customFieldSetRepository,
        CustomFieldTransformer $customFieldTransformer,
        CustomFieldProvider $customFieldProvider
    ) {
        $this->customFieldRepository = $customFieldRepository;
        $this->customFieldSetRepository = $customFieldSetRepository;
        $this->customFieldTransformer = $customFieldTransformer;
        $this->customFieldProvider = $customFieldProvider;
    }

    public function persistStream(AttributeStreamResultsProxy $attributes, Context $context): array
    {
        $payloads = [];

        $this->persistCustomFieldSet($context);

        foreach ($attributes->getEdges() as $attribute) {
            if (empty($node = $attribute['node'])) {
                continue;
            }

            $payloads[] = array_merge(
                $this->customFieldTransformer->transformAttributeNode($node, $context),
                [
                    'customFieldSetId' => $this->customFieldProvider->getErgonodeCustomFieldSetId($context),
                ]
            );
        }

        if (empty($payloads)) {
            return [];
        }

        $written = $this->customFieldRepository->upsert($payloads, $context);

        return $written->getPrimaryKeys(CustomFieldDefinition::ENTITY_NAME);
    }

    private function persistCustomFieldSet(Context $context): void
    {
        if (null !== $this->customFieldProvider->getCustomFieldSet($context)) {
            return;
        }

        $this->customFieldSetRepository->create([
            [
                'name' => Constants::PRODUCT_CUSTOM_FIELD_SET_NAME,
                'config' => [
                    'label' => [
                        'en-GB' => 'Ergonode Custom Fields',
                    ],
                ],
                'relations' => [
                    [
                        'entityName' => ProductDefinition::ENTITY_NAME,
                    ],
                ],
            ],
        ], $context);
    }
}
