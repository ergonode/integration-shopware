<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Persistor\UnitPersistor;
use Ergonode\IntegrationShopware\Provider\AttributeMappingProvider;
use Ergonode\IntegrationShopware\Provider\UnitProvider;
use Ergonode\IntegrationShopware\Util\AttributeTypeValidator;
use Ergonode\IntegrationShopware\Util\IsoCodeConverter;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;

class ProductScaleUnitTransformer implements ProductDataTransformerInterface
{
    private const SHOPWARE_SCALE_UNIT_CODE = 'scaleUnit';

    private AttributeMappingProvider $attributeMappingProvider;
    private UnitProvider $unitProvider;
    private UnitPersistor $unitPersistor;

    public function __construct(
        AttributeMappingProvider $attributeMappingProvider,
        UnitPersistor $unitPersistor,
        UnitProvider $unitProvider
    ) {
        $this->attributeMappingProvider = $attributeMappingProvider;
        $this->unitProvider = $unitProvider;
        $this->unitPersistor = $unitPersistor;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $swData = $productData->getShopwareData();
        $ergonodeData = $productData->getErgonodeData();

        $mappingKeys = $this->attributeMappingProvider->provideByShopwareKey(
            self::SHOPWARE_SCALE_UNIT_CODE,
            $context
        );
        if ($mappingKeys === null) {
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
                $payload = $this->createPayload($translationValues);
                $this->unitPersistor->persist($payload, $context);
                $swData['unitId'] = $payload['id'];
            } else {
                $swData['unitId'] = $unit->getId();
            }

            $productData->setShopwareData($swData);
        }
        return $productData;
    }

    private function getTranslationValuesWithConvertedIso(array $translations): array
    {
        $translationValues = [];
        foreach ($translations as $translation) {
            $code = $translation['value_array']['code'];
            $translationValues[IsoCodeConverter::ergonodeToShopwareIso($translation['language'])] = $code;
        }
        return $translationValues;
    }

    private function createPayload(array $translations): array
    {
        $id = Uuid::randomHex();
        $payload = ['id' => $id];
        foreach ($translations as $key => $translation) {
            $payload['translations'][$key] = [
                'name' => $translation,
                'shortCode' => $translation
            ];
        }
        return $payload;
    }
}
