<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Persistor;

use Ergonode\IntegrationShopware\Api\AttributeStreamResultsProxy;
use Ergonode\IntegrationShopware\DTO\PropertyGroupTransformationDTO;
use Ergonode\IntegrationShopware\Processor\Attribute\AttributeCustomProcessorResolver;
use Ergonode\IntegrationShopware\Provider\PropertyGroupProvider;
use Ergonode\IntegrationShopware\Transformer\PropertyGroupTransformer;
use Ergonode\IntegrationShopware\Util\YesNo;
use Ergonode\IntegrationShopware\Service\ConfigService;
use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

use function array_map;
use function array_merge;

class PropertyGroupPersistor
{
    private EntityRepository $propertyGroupRepository;

    private EntityRepository $propertyGroupOptionRepository;

    private PropertyGroupTransformer $propertyGroupTransformer;

    private PropertyGroupProvider $propertyGroupProvider;

    private AttributeCustomProcessorResolver $attributeCustomProcessorResolver;

    public function __construct(
        EntityRepository $propertyGroupRepository,
        EntityRepository $propertyGroupOptionRepository,
        PropertyGroupTransformer $propertyGroupTransformer,
        PropertyGroupProvider $propertyGroupProvider,
        AttributeCustomProcessorResolver $attributeCustomProcessorResolver,
        private ConfigService $configService
    ) {
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->propertyGroupOptionRepository = $propertyGroupOptionRepository;
        $this->propertyGroupTransformer = $propertyGroupTransformer;
        $this->propertyGroupProvider = $propertyGroupProvider;
        $this->attributeCustomProcessorResolver = $attributeCustomProcessorResolver;
    }

    public function persistStream(AttributeStreamResultsProxy $attributes, Context $context): array
    {
        $propertyGroupPayloads = [];
        $optionDeletePayloads = [];

        foreach ($attributes->getEdges() as $attribute) {
            if (empty($node = $attribute['node']) || empty($code = $node['code'])) {
                continue;
            }

            if ($customProcessor = $this->attributeCustomProcessorResolver->resolve($node, $context)) {
                $customProcessor->process($node, $context);
                continue;
            }

            // start devEcommerce change
            $skipMetaHideAttribute = false;
            foreach ($node['metadata'] as $metadata) {
                if (($metadata['key'] === $this->configService->getHideMetaDataKey() && YesNo::cast($metadata['value']))) {
                    $skipMetaHideAttribute = true;
                    break;
                }
            }
            if ($skipMetaHideAttribute) {
                continue;
            }
            // end devEcommerce change

            $propertyGroup = $this->propertyGroupProvider->getPropertyGroupByMapping($code, $context);

            $dto = new PropertyGroupTransformationDTO($node);
            $dto->setSwPropertyGroup($propertyGroup);

            $dto = $this->propertyGroupTransformer->transformAttributeNode($dto);

            $propertyGroupPayload = $dto->getPropertyGroupPayload();
            if (empty($propertyGroupPayload)) {
                continue;
            }

            $propertyGroupPayloads[] = $propertyGroupPayload;

            $deletePayload = $dto->getOptionDeletePayload();
            if (empty($deletePayload)) {
                continue;
            }

            $optionDeletePayloads[] = $deletePayload;
        }

        $upserted = $this->propertyGroupRepository->upsert($propertyGroupPayloads, $context);

        $this->propertyGroupOptionRepository->delete(array_merge([], ...$optionDeletePayloads), $context);

        return $upserted->getPrimaryKeys(PropertyGroupDefinition::ENTITY_NAME);
    }

    public function removeByCodes(array $codes, Context $context): array
    {
        $ids = $this->propertyGroupProvider->getIdsByCodes($codes, $context);

        if (empty($ids)) {
            return [];
        }

        $deleted = $this->propertyGroupRepository->delete(array_map(static fn($id) => ['id' => $id], $ids), $context);

        return $deleted->getPrimaryKeys(PropertyGroupDefinition::ENTITY_NAME);
    }
}
