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
        return \array_keys(Constants::SW_PRODUCT_MAPPABLE_FIELDS);
    }

    public function getMappableShopwareAttributesWithTypes(): array
    {
        $attributes = [];
        foreach (Constants::SW_PRODUCT_MAPPABLE_FIELDS as $code => $type) {
            $attributes[] = [
                'code' => $code,
                'type' => $type
            ];
        }

        return $attributes;
    }

    public function getAllErgonodeAttributes(array $types = []): array
    {
        return \array_map(
            fn($data) => $data['code'],
            $this->getAllErgonodeAttributesWithTypes($types)
        );
    }

    public function getAllErgonodeAttributesWithTypes(array $types = []): array
    {
        $attributeCodes = [];

        $generator = $this->ergonodeAttributeProvider->provideProductAttributes();

        foreach ($generator as $attributes) {
            if (!empty($types)) {
                $attributes = $attributes->filterByAttributeTypes($types);
            }

            foreach ($attributes->getEdges() as $attribute) {
                $node = $attribute['node'] ?? null;
                $code = $node['code'] ?? null;
                if (null === $node || null === $code) {
                    continue;
                }

                $attributeCodes[] = [
                    'code' => $code,
                    'type' => $this->resolveAttributeType($node)
                ];
            }
        }

        return $attributeCodes;
    }

    private function resolveAttributeType(array $node): string
    {
        foreach ($node as $key => $value) {
            if (0 === \strpos($key, 'type_')) {
                return \substr($key, 5);
            }
        }

        return 'unknown';
    }
}