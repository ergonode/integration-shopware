<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Persistor;

use Ergonode\IntegrationShopware\Api\AttributeStreamResultsProxy;
use Ergonode\IntegrationShopware\DTO\PropertyGroupTransformationDTO;
use Ergonode\IntegrationShopware\Processor\Attribute\AttributeCustomProcessorProvider;
use Ergonode\IntegrationShopware\Provider\PropertyGroupProvider;
use Ergonode\IntegrationShopware\Transformer\PropertyGroupTransformer;
use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

use function array_map;
use function array_merge;

class PropertyGroupPersistor
{
    private EntityRepositoryInterface $propertyGroupRepository;

    private EntityRepositoryInterface $propertyGroupOptionRepository;

    private PropertyGroupTransformer $propertyGroupTransformer;

    private PropertyGroupProvider $propertyGroupProvider;

    private AttributeCustomProcessorProvider $attributeCustomProcessorProvider;

    public function __construct(
        EntityRepositoryInterface $propertyGroupRepository,
        EntityRepositoryInterface $propertyGroupOptionRepository,
        PropertyGroupTransformer $propertyGroupTransformer,
        PropertyGroupProvider $propertyGroupProvider,
        AttributeCustomProcessorProvider $attributeCustomProcessorProvider
    ) {
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->propertyGroupOptionRepository = $propertyGroupOptionRepository;
        $this->propertyGroupTransformer = $propertyGroupTransformer;
        $this->propertyGroupProvider = $propertyGroupProvider;
        $this->attributeCustomProcessorProvider = $attributeCustomProcessorProvider;
    }

    public function persistStream(AttributeStreamResultsProxy $attributes, Context $context): array
    {
        $propertyGroupPayloads = [];
        $optionDeletePayloads = [];

        foreach ($attributes->getEdges() as $attribute) {
            //dump($attribute);
            if (empty($node = $attribute['node']) || empty($code = $node['code'])) {
                continue;
            }

            if ($customProcessor = $this->attributeCustomProcessorProvider->provide($node, $context)) {
                $customProcessor->process($node, $context);
                continue;
            }

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
