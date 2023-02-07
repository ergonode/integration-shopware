<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Provider\AttributeMappingProvider;
use Ergonode\IntegrationShopware\Provider\UnitProvider;
use Ergonode\IntegrationShopware\Util\IsoCodeConverter;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;

class ProductScaleUnitTransformer implements ProductDataTransformerInterface
{
    private const SHOPWARE_SCALE_UNIT_CODE = 'scaleUnit';

    private AttributeMappingProvider $attributeMappingProvider;
    private UnitProvider $unitProvider;

    public function __construct(
        AttributeMappingProvider $attributeMappingProvider,
        UnitProvider $unitProvider
    ) {
        $this->attributeMappingProvider = $attributeMappingProvider;
        $this->unitProvider = $unitProvider;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $swData = $productData->getShopwareData();
        $ergonodeData = $productData->getErgonodeData();

        $mappingKeys = $this->attributeMappingProvider->provideByShopwareKey(
            self::SHOPWARE_SCALE_UNIT_CODE,
            $context
        );
        if (null === $mappingKeys) {
            unset($swData['unit']);
            return $productData;
        }
        $ergonodeKey = $mappingKeys->getErgonodeKey();

        foreach ($ergonodeData['attributeList']['edges'] as $edge) {
            $code = $edge['node']['attribute']['code'];
            if ($code !== $ergonodeKey) {
                continue;
            }
            $translationValues = $this->getTranslationValuesWithConvertedIso($edge['node']['translations']);
            $uniqueTranslationValues = array_unique($translationValues);
            $unit = $this->unitProvider->getUnitByNames($uniqueTranslationValues, $context);
            if ($unit === null) {
                $payload = $this->createPayload($translationValues, $code);
                $swData['unit'] = $payload;
            }
            $swData['unitId'] = $unit ? $unit->getId() : null;
        }
        $productData->setShopwareData($swData);

        return $productData;
    }

    private function getTranslationValuesWithConvertedIso(array $translations): array
    {
        $translationValues = [];
        foreach ($translations[0]['value_array']['name'] as $name) {
            $translationValues[IsoCodeConverter::ergonodeToShopwareIso($name['language'])] =
                $name['value'] ?? $translations[0]['value_array']['code'];
        }

        return $translationValues;
    }

    private function createPayload(array $translations): array
    {
        $payload = [];
        foreach ($translations as $key => $translation) {
            $payload['translations'][$key] = [
                'name' => $translation,
                'shortCode' => $translation,
            ];
        }
        return $payload;
    }
}
