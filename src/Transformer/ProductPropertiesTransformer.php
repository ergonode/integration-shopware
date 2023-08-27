<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Entity\ErgonodeMappingExtension\ErgonodeMappingExtensionEntity;
use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Ergonode\IntegrationShopware\Model\ProductAttribute;
use Ergonode\IntegrationShopware\Model\ProductSelectAttribute;
use Ergonode\IntegrationShopware\Provider\PropertyGroupOptionProvider;
use Ergonode\IntegrationShopware\Util\CodeBuilderUtil;
use Shopware\Core\Content\Product\Aggregate\ProductOption\ProductOptionDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductProperty\ProductPropertyDefinition;
use Shopware\Core\Framework\Context;

use function array_filter;
use function array_flip;
use function array_intersect_key;
use function array_values;

class ProductPropertiesTransformer implements ProductDataTransformerInterface
{
    private const FIELD_PROPERTIES = 'properties';
    private const FIELD_OPTIONS    = 'options';

    private PropertyGroupOptionProvider $optionProvider;

    public function __construct(
        PropertyGroupOptionProvider $optionProvider
    ) {
        $this->optionProvider = $optionProvider;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $ergonodeData = $productData->getErgonodeData();

        $selectAttributes = $ergonodeData->getAttributesByTypes([
            ProductAttribute::TYPE_SELECT,
            ProductAttribute::TYPE_MULTI_SELECT,
        ]);

        $optionCodes = $this->getOptionCodes($selectAttributes, $ergonodeData->getMappings());

        $optionMapping = $this->getOptionsMapping($optionCodes, $context);

        $swData = $productData->getShopwareData();

        $properties = array_values(array_intersect_key($optionMapping, array_flip($optionCodes)));
        $swData->setProperties($properties);
        if ($productData->isVariant()) {
            $codes = $productData->getBindingCodes();
            $bindingOptions = $this->arrayFilterStartsWith($optionCodes, $codes);

            $options = array_values(array_intersect_key($optionMapping, array_flip($bindingOptions)));
            $swData->setOptions($options);
        }

        $productData->setShopwareData($swData);

        $productData->addEntitiesToDelete(
            ProductPropertyDefinition::ENTITY_NAME,
            $this->getProductPropertiesDeletePayload($productData)
        );

        $productData->addEntitiesToDelete(
            ProductOptionDefinition::ENTITY_NAME,
            $this->getProductPropertiesDeletePayload($productData, self::FIELD_OPTIONS)
        );

        return $productData;
    }

    /**
     * @return array[string, array[string, string]]
     */
    private function getOptionsMapping(array $options, Context $context): array
    {
        $optionEntities = $this->optionProvider->getOptionsByMappingArray(array_unique($options), $context);

        $optionsMapping = [];
        foreach ($optionEntities as $option) {
            $extension = $option->getExtension(AbstractErgonodeMappingExtension::EXTENSION_NAME);

            if ($extension instanceof ErgonodeMappingExtensionEntity) {
                $optionsMapping[$extension->getCode()] = [
                    'id' => $option->getId(),
                ];
            }
        }

        return $optionsMapping;
    }

    private function arrayFilterStartsWith(array $haystacks, array $needles): array
    {
        return array_filter($haystacks, function (string $haystack) use ($needles) {
            $matched = array_filter(
                $needles,
                fn(string $needle) => str_starts_with(
                    $haystack,
                    sprintf('%s%s', $needle, CodeBuilderUtil::EXTENDED_JOIN)
                )
            );

            return false === empty($matched);
        });
    }

    private function getProductPropertiesDeletePayload(
        ProductTransformationDTO $dto,
        string $field = self::FIELD_PROPERTIES
    ): array {
        if (
            false === in_array($field, [self::FIELD_OPTIONS, self::FIELD_PROPERTIES]) || null === $dto->getSwProduct()
        ) {
            return [];
        }

        $getter = sprintf('get%s', ucfirst($field));

        $currentProperties = $dto->getSwProduct()->$getter();
        if (null === $currentProperties) {
            return [];
        }

        $newProperties = $dto->getSwProduct()->get($field) ?? [];
        if (empty($newProperties)) {
            return [];
        }

        $newPropertyIds = array_filter(
            array_map(fn(array $property) => $property['id'] ?? null, $newProperties)
        );
        $propertyIds = $currentProperties->getIds();

        $idsToDelete = array_diff($propertyIds, $newPropertyIds);

        return array_map(fn(string $id) => [
            'productId' => $dto->getSwProduct()->getId(),
            'optionId' => $id,
        ], $idsToDelete);
    }

    /**
     * @param ProductSelectAttribute[] $selectAttributes
     * @return string[]
     */
    private function getOptionCodes(array $selectAttributes, array $existingMappings): array
    {
        $optionCodes = [];
        foreach ($selectAttributes as $selectAttribute) {
            if (!$selectAttribute instanceof ProductSelectAttribute) {
                continue;
            }

            if (in_array($selectAttribute->getCode(), $existingMappings)) {
                continue;
            }

            foreach ($selectAttribute->getOptions() as $option) {
                $optionCodes[] = CodeBuilderUtil::buildExtended($selectAttribute->getCode(), $option->getCode());
            }
        }

        return $optionCodes;
    }
}
