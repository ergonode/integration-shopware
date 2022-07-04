<?php

declare(strict_types=1);

namespace Strix\Ergonode\Persistor;

use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Strix\Ergonode\Modules\Attribute\Api\AttributeDeletedStreamResultsProxy;
use Strix\Ergonode\Modules\Attribute\Api\AttributeStreamResultsProxy;
use Strix\Ergonode\Provider\PropertyGroupProvider;
use Strix\Ergonode\Transformer\PropertyGroupTransformer;

class PropertyGroupPersistor
{
    private EntityRepositoryInterface $propertyGroupRepository;

    private PropertyGroupTransformer $propertyGroupTransformer;

    private PropertyGroupProvider $propertyGroupProvider;

    public function __construct(
        EntityRepositoryInterface $propertyGroupRepository,
        PropertyGroupTransformer $propertyGroupTransformer,
        PropertyGroupProvider $propertyGroupProvider
    ) {
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->propertyGroupTransformer = $propertyGroupTransformer;
        $this->propertyGroupProvider = $propertyGroupProvider;
    }

    public function persistStream(AttributeStreamResultsProxy $attributes, Context $context): array
    {
        $payloads = [];

        foreach ($attributes->getEdges() as $attribute) {
            if (empty($node = $attribute['node'])) {
                continue;
            }

            $payload = $this->propertyGroupTransformer->transformAttributeNode($node, $context);
            if (empty($payload)) {
                continue;
            }

            $payloads[] = $payload;
        }

        if (empty($payloads)) {
            return [];
        }

        $written = $this->propertyGroupRepository->upsert($payloads, $context);

        return [
            PropertyGroupDefinition::ENTITY_NAME => $written->getPrimaryKeys(PropertyGroupDefinition::ENTITY_NAME),
            PropertyGroupOptionDefinition::ENTITY_NAME => $written->getPrimaryKeys(PropertyGroupOptionDefinition::ENTITY_NAME),
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