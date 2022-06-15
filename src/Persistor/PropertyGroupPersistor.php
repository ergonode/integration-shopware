<?php

declare(strict_types=1);

namespace Strix\Ergonode\Persistor;

use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Strix\Ergonode\Modules\Attribute\Api\AttributeStreamResultsProxy;
use Strix\Ergonode\Transformer\AttributeNodeTransformer;

class PropertyGroupPersistor
{
    private EntityRepositoryInterface $propertyGroupRepository;

    private AttributeNodeTransformer $attributeNodeTransformer;

    public function __construct(
        EntityRepositoryInterface $propertyGroupRepository,
        AttributeNodeTransformer $attributeNodeTransformer
    ) {
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->attributeNodeTransformer = $attributeNodeTransformer;
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
}