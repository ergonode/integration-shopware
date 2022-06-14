<?php

declare(strict_types=1);

namespace Strix\Ergonode\Persistor;

use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Strix\Ergonode\Extension\PropertyGroup\PropertyGroupExtension;
use Strix\Ergonode\Modules\Attribute\Api\AttributeDeletedStreamResultsProxy;
use Strix\Ergonode\Modules\Attribute\Api\AttributeStreamResultsProxy;
use Strix\Ergonode\Provider\ErgonodeMappingProvider;
use Strix\Ergonode\Transformer\AttributeNodeTransformer;

class PropertyGroupPersistor
{
    private EntityRepositoryInterface $propertyGroupRepository;

    private AttributeNodeTransformer $attributeNodeTransformer;

    private ErgonodeMappingProvider $ergonodeMappingProvider;

    public function __construct(
        EntityRepositoryInterface $propertyGroupRepository,
        AttributeNodeTransformer $attributeNodeTransformer,
        ErgonodeMappingProvider $ergonodeMappingProvider
    ) {
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->attributeNodeTransformer = $attributeNodeTransformer;
        $this->ergonodeMappingProvider = $ergonodeMappingProvider;
    }

    public function persistStream(AttributeStreamResultsProxy $attributes, Context $context): array
    {
        $payloads = [];

        foreach ($attributes->getEdges() as $attribute) {
            if (empty($node = $attribute['node'])) {
                continue;
            }

            $payloads[] = $this->attributeNodeTransformer->transformNode($node, $context);
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

        $ids = $this->ergonodeMappingProvider->getIdsByType($codes, PropertyGroupExtension::ERGONODE_TYPE, $context);

        if (empty($ids)) {
            return [];
        }

        $deleted = $this->propertyGroupRepository->delete($ids, $context);

        return [
            PropertyGroupDefinition::ENTITY_NAME => $deleted->getPrimaryKeys(PropertyGroupDefinition::ENTITY_NAME),
            PropertyGroupOptionDefinition::ENTITY_NAME => $deleted->getPrimaryKeys(PropertyGroupOptionDefinition::ENTITY_NAME),
        ];
    }
}