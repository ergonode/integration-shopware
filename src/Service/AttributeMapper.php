<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service;

use Ergonode\IntegrationShopware\Provider\AttributeMappingProvider;
use Ergonode\IntegrationShopware\Provider\ErgonodeAttributeProvider;
use Ergonode\IntegrationShopware\Util\Constants;
use Shopware\Core\Framework\Context;

class AttributeMapper
{
    private ErgonodeAttributeProvider $ergonodeAttributeProvider;

    private AttributeMappingProvider $mappingProvider;

    public function __construct(
        ErgonodeAttributeProvider $ergonodeAttributeProvider,
        AttributeMappingProvider $mappingProvider
    ) {
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
        return Constants::SW_PRODUCT_MAPPABLE_FIELDS;
    }

    public function getAllErgonodeAttributes(array $types = []): array
    {
        $attributeCodes = [];

        $generator = $this->ergonodeAttributeProvider->provideProductAttributes();

        foreach ($generator as $attributes) {
            if (!empty($types)) {
                $attributes = $attributes->filterByAttributeTypes($types);
            }

            foreach ($attributes->getEdges() as $attribute) {
                $attributeCodes[] = $attribute['node']['code'] ?? '';
            }
        }

        return $attributeCodes;
    }
}