<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Entity\ErgonodeMappingExtension\ErgonodeMappingExtensionEntity;
use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Ergonode\IntegrationShopware\Provider\PropertyGroupOptionProvider;
use Ergonode\IntegrationShopware\Util\PropertyGroupOptionUtil;
use Shopware\Core\Content\Product\Aggregate\ProductProperty\ProductPropertyDefinition;
use Shopware\Core\Framework\Context;

use function array_filter;
use function array_flip;
use function array_intersect_key;
use function array_values;

class ProductPropertiesTransformer implements ProductDataTransformerInterface
{
    private PropertyGroupOptionProvider $optionProvider;

    private TranslationTransformer $translationTransformer;

    public function __construct(
        PropertyGroupOptionProvider $optionProvider,
        TranslationTransformer $translationTransformer
    ) {
        $this->optionProvider = $optionProvider;
        $this->translationTransformer = $translationTransformer;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $ergonodeData = $productData->getErgonodeData();

        $selectAttributes = $this->getSelectAttributes($ergonodeData);

        $options = $this->transformSelectAttributes($selectAttributes);

        $optionMapping = $this->getOptionsMapping($options, $context);

        $propertyIds = array_values(array_intersect_key($optionMapping, array_flip($options)));

        $swData = $productData->getShopwareData();
        $swData['properties'] = $propertyIds;

        if ($productData->isVariant()) {
            $swData['options'] = $propertyIds;
        }

        $productData->setShopwareData($swData);

        $productData->addEntitiesToDelete(
            ProductPropertyDefinition::ENTITY_NAME,
            $this->getProductOptionsDeletePayload($productData)
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
            if (empty($node) || empty($node['valueTranslations'])) {
                continue;
            }

            $translated = $this->translationTransformer->transform($node['valueTranslations']);

            $value = reset($translated); // assuming that the attribute is GLOBAL
            if (!$value) {
                continue;
            }

            if (!\is_array($value)) {
                $value = [$value];
            }

            foreach ($value as &$optionCode) {
                $optionCode = CodeBuilderUtil::buildOptionCode($node['attribute']['code'], $optionCode);
            }

            $transformed = array_merge($transformed, $value);
        }

        return $transformed;
    }

    /**
     * @return string[]
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

    private function getProductOptionsDeletePayload(ProductTransformationDTO $dto): array
    {
        if (null === $dto->getSwProduct()) {
            return [];
        }

        $properties = $dto->getSwProduct()->getProperties();
        if (null === $properties) {
            return [];
        }

        $newPropertyIds = array_filter(
            array_map(fn(array $property) => $property['id'] ?? null, $dto->getShopwareData()['properties'])
        );
        $propertyIds = $properties->getIds();

        $idsToDelete = array_diff($propertyIds, $newPropertyIds);

        return array_map(fn(string $id) => [
            'productId' => $dto->getSwProduct()->getId(),
            'optionId' => $id,
        ], $idsToDelete);
    }
}