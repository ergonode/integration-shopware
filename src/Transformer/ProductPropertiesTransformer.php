<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Entity\ErgonodeMappingExtension\ErgonodeMappingExtensionEntity;
use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Ergonode\IntegrationShopware\Service\PropertyGroupOptionService;
use Ergonode\IntegrationShopware\Util\CodeBuilderUtil;
use Shopware\Core\Content\Product\Aggregate\ProductOption\ProductOptionDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductProperty\ProductPropertyDefinition;
use Shopware\Core\Framework\Context;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use function array_filter;
use function array_flip;
use function array_intersect_key;
use function array_values;

class ProductPropertiesTransformer implements ProductDataTransformerInterface
{
    const FIELD_PROPERTIES = 'properties';
    const FIELD_OPTIONS = 'options';

    private PropertyGroupOptionService $optionService;

    private TranslationTransformer $translationTransformer;

    public function __construct(
        PropertyGroupOptionService $optionService,
        TranslationTransformer $translationTransformer
    ) {
        $this->optionService = $optionService;
        $this->translationTransformer = $translationTransformer;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $ergonodeData = $productData->getErgonodeData();

        $selectAttributes = $this->getSelectAttributes($ergonodeData);

        $options = $this->transformSelectAttributes($selectAttributes);

        $optionMapping = $this->getOptionsMapping($options, $context);

        $swData = $productData->getShopwareData();
        $swData['properties'] = array_values(array_intersect_key($optionMapping, array_flip($options)));

        if ($productData->isVariant()) {
            $codes = $productData->getBindingCodes();
            $bindingOptions = $this->arrayFilterStartsWith($options, $codes);

            $swData['options'] = array_values(array_intersect_key($optionMapping, array_flip($bindingOptions)));
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

    private function getSelectAttributes(array $ergonodeData): array
    {
        $types = [
            AttributeTypesEnum::SELECT,
            AttributeTypesEnum::MULTISELECT,
        ];

        return array_filter(
            $ergonodeData['attributeList']['edges'] ?? [],
            fn(array $attribute) => in_array(AttributeTypesEnum::getNodeType($attribute['node']['attribute']), $types)
        );
    }

    private function transformSelectAttributes(array $selectAttributes): array
    {
        $transformed = [];

        foreach ($selectAttributes as $attribute) {
            $node = $attribute['node'];
            if (empty($node) || empty($node['translations'])) {
                continue;
            }

            $translated = $this->translationTransformer->transform($node['translations']);

            $value = reset($translated); // assuming that the attribute is GLOBAL
            if (!$value) {
                continue;
            }

            /** If array is associative for a select option, convert it to multidimensional array as for multiselect */
            if (isset($value['code'])) {
                $value = [$value];
            }

            $optionCodes = [];
            foreach ($value as $option) {
                $optionCodes[] = CodeBuilderUtil::buildExtended($node['attribute']['code'], $option['code']);
            }

            $transformed = array_merge($transformed, $optionCodes);
        }

        return $transformed;
    }

    /**
     * @return array[string, array[string, string]]
     */
    private function getOptionsMapping(array $options, Context $context): array
    {
        $optionEntities = $this->optionService->getOptionsByMappingArray(array_unique($options), $context);

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
                fn(string $needle) => str_starts_with($haystack, sprintf('%s%s', $needle, CodeBuilderUtil::EXTENDED_JOIN))
            );

            return false === empty($matched);
        });
    }

    private function getProductPropertiesDeletePayload(
        ProductTransformationDTO $dto,
        string $field = self::FIELD_PROPERTIES
    ): array {
        if (
            false === in_array($field, [self::FIELD_OPTIONS, self::FIELD_PROPERTIES]) ||
            null === $dto->getSwProduct()
        ) {
            return [];
        }

        $getter = sprintf('get%s', ucfirst($field));

        $currentProperties = $dto->getSwProduct()->$getter();
        if (null === $currentProperties) {
            return [];
        }

        $newProperties = $dto->getShopwareData()[$field] ?? [];
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
}
