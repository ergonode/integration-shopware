<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Attribute\Service;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Strix\Ergonode\Modules\Attribute\Provider\AttributeMappingProvider;
use Strix\Ergonode\Modules\Attribute\Provider\ErgonodeAttributeProvider;

class AttributeMappingService
{
    private DefinitionInstanceRegistry $definitionInstanceRegistry;

    private ErgonodeAttributeProvider $ergonodeAttributeProvider;

    private AttributeMappingProvider $mappingProvider;

    public function __construct(
        DefinitionInstanceRegistry $definitionInstanceRegistry,
        ErgonodeAttributeProvider $ergonodeAttributeProvider,
        AttributeMappingProvider $mappingProvider
    ) {
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
        $this->ergonodeAttributeProvider = $ergonodeAttributeProvider;
        $this->mappingProvider = $mappingProvider;
    }

    public function mapShopwareKey(string $key, Context $context): ?string
    {
        $mapping = $this->mappingProvider->provideByShopwareKey($key, $context);

        if (null !== $mapping) {
            return $mapping->getErgonodeKey();
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function mapErgonodeKey(string $key, Context $context): array
    {
        $shopwareKeys = [];

        $mappings = $this->mappingProvider->provideByErgonodeKey($key, $context);
        foreach ($mappings as $mapping) {
            $shopwareKeys[] = $mapping->getShopwareKey();
        }

        return $shopwareKeys;
    }

    /**
     * @return string[]
     */
    public function getMappableShopwareAttributes(): array
    {
        $definition = $this->definitionInstanceRegistry->getByEntityName(ProductDefinition::ENTITY_NAME);

        $fields = $definition->getTranslatedFields(); // todo define list of mappable sw fields ?

        return array_keys($fields);
    }

    public function getAllErgonodeAttributes(): array
    {
        $attributeCodes = [];

        $generator = $this->ergonodeAttributeProvider->provideProductAttributes();

        foreach ($generator as $attributes) {
            foreach ($attributes->getEdges() as $attribute) {
                $attributeCodes[] = $attribute['node']['code'] ?? '';
            }
        }

        return $attributeCodes;
    }
}