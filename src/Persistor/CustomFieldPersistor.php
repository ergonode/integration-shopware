<?php

declare(strict_types=1);

namespace Strix\Ergonode\Persistor;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetDefinition;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetEntity;
use Shopware\Core\System\CustomField\CustomFieldDefinition;
use Strix\Ergonode\Modules\Attribute\Api\AttributeStreamResultsProxy;
use Strix\Ergonode\Provider\CustomFieldProvider;
use Strix\Ergonode\Transformer\CustomFieldTransformer;
use Strix\Ergonode\Util\Constants;

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

        $entities = $this->persistCustomFieldSet($context);

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

        return array_merge_recursive(
            $entities,
            [
                CustomFieldDefinition::ENTITY_NAME => $written->getPrimaryKeys(CustomFieldDefinition::ENTITY_NAME),
            ]
        );
    }

    public function persistCustomFieldSet(Context $context): array
    {
        if ($this->customFieldProvider->getCustomFieldSet($context) instanceof CustomFieldSetEntity) {
            return [];
        }

        $written = $this->customFieldSetRepository->create([
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

        return [
            CustomFieldSetDefinition::ENTITY_NAME => $written->getPrimaryKeys(CustomFieldSetDefinition::ENTITY_NAME),
        ];
    }
}
