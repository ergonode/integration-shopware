<?php

declare(strict_types=1);

namespace Strix\Ergonode\Persistor;

use Shopware\Core\Content\Product\Aggregate\ProductOption\ProductOptionDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Strix\Ergonode\DTO\PropertyGroupTransformationDTO;
use Strix\Ergonode\Modules\Attribute\Api\AttributeDeletedStreamResultsProxy;
use Strix\Ergonode\Modules\Attribute\Api\AttributeStreamResultsProxy;
use Strix\Ergonode\Provider\PropertyGroupProvider;
use Strix\Ergonode\Service\EntityRemover;
use Strix\Ergonode\Transformer\PropertyGroupTransformer;

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
        $deleted = $this->propertyGroupOptionRepository->delete(array_merge([], ...$optionDeletePayloads), $context);

        return [
            PropertyGroupDefinition::ENTITY_NAME . '.upserted' =>
                $upserted->getPrimaryKeys(PropertyGroupDefinition::ENTITY_NAME),
            PropertyGroupOptionDefinition::ENTITY_NAME . '.deleted' =>
                $deleted->getPrimaryKeys(PropertyGroupOptionDefinition::ENTITY_NAME),
        ];
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