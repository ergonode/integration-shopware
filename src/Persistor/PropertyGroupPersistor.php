<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Persistor;

use Ergonode\IntegrationShopware\Api\AttributeDeletedStreamResultsProxy;
use Ergonode\IntegrationShopware\Api\AttributeStreamResultsProxy;
use Ergonode\IntegrationShopware\DTO\PropertyGroupTransformationDTO;
use Ergonode\IntegrationShopware\Provider\PropertyGroupProvider;
use Ergonode\IntegrationShopware\Transformer\PropertyGroupTransformer;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

use function array_filter;
use function array_map;
use function array_merge;

class PropertyGroupPersistor
{
    private EntityRepositoryInterface $propertyGroupRepository;

    private EntityRepositoryInterface $propertyGroupOptionRepository;

    private PropertyGroupTransformer $propertyGroupTransformer;

    private PropertyGroupProvider $propertyGroupProvider;

    public function __construct(
        EntityRepositoryInterface $propertyGroupRepository,
        EntityRepositoryInterface $propertyGroupOptionRepository,
        PropertyGroupTransformer $propertyGroupTransformer,
        PropertyGroupProvider $propertyGroupProvider
    ) {
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->propertyGroupOptionRepository = $propertyGroupOptionRepository;
        $this->propertyGroupTransformer = $propertyGroupTransformer;
        $this->propertyGroupProvider = $propertyGroupProvider;
    }

    public function persistStream(AttributeStreamResultsProxy $attributes, Context $context): array
    {
        $propertyGroupPayloads = [];
        $optionDeletePayloads = [];

        foreach ($attributes->getEdges() as $attribute) {
            if (empty($node = $attribute['node']) || empty($code = $node['code'])) {
                continue;
            }

            $propertyGroup = $this->propertyGroupProvider->getPropertyGroupByMapping($code, $context);

            $dto = new PropertyGroupTransformationDTO($node);
            $dto->setSwPropertyGroup($propertyGroup);

            $dto = $this->propertyGroupTransformer->transformAttributeNode($dto, $context);

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

    public function remove(AttributeDeletedStreamResultsProxy $attributes, Context $context): array
    {
        $codes = $attributes->map(fn(array $node) => $node['node'] ?? null);
        $codes = array_filter($codes);

        $ids = $this->propertyGroupProvider->getIdsByCodes($codes, $context);

        if (empty($ids)) {
            return [];
        }

        $deleted = $this->propertyGroupRepository->delete(array_map(fn($id) => ['id' => $id], $ids), $context);

        return [
            PropertyGroupDefinition::ENTITY_NAME => $deleted->getPrimaryKeys(PropertyGroupDefinition::ENTITY_NAME),
            PropertyGroupOptionDefinition::ENTITY_NAME => $deleted->getPrimaryKeys(PropertyGroupOptionDefinition::ENTITY_NAME),
        ];
    }
}