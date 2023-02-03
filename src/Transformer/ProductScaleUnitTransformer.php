<?php

declare(strict_types=1);


namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingCollection;
use Ergonode\IntegrationShopware\Provider\AttributeMappingProvider;
use Ergonode\IntegrationShopware\Provider\UnitProvider;
use Ergonode\IntegrationShopware\Util\AttributeTypeValidator;
use Shopware\Core\Framework\Context;

class ProductScaleUnitTransformer implements ProductDataTransformerInterface
{
    private const SHOPWARE_SCALE_UNIT_CODE = 'scaleUnit';

    private AttributeMappingProvider $attributeMappingProvider;
    private AttributeTypeValidator $attributeTypeValidator;
    private UnitProvider $unitProvider;

    public function __construct(
        AttributeMappingProvider $attributeMappingProvider,
        AttributeTypeValidator $attributeTypeValidator,
        UnitProvider $unitProvider
    ) {
        $this->attributeMappingProvider = $attributeMappingProvider;
        $this->attributeTypeValidator = $attributeTypeValidator;
        $this->unitProvider = $unitProvider;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
//        $unitId = $this->unitProvider->getIdByName('sztuka', $context);
//        dd($unitId);
//        return $productData;

//        $swData = $productData->getShopwareData();
//        dd($swData);
        $ergonodeData = $productData->getErgonodeData();
        $mappingKeys = $this->attributeMappingProvider->provideByShopwareKey(
            self::SHOPWARE_SCALE_UNIT_CODE,
            $context
        );
        if ($mappingKeys === null) {
            return $productData;
        }

        $ergonodeKey = $mappingKeys->getErgonodeKey();
        $ergonodeAttributeMappingCollection = new ErgonodeAttributeMappingCollection([$mappingKeys]);
        foreach ($ergonodeData['attributeList']['edges'] as $edge) {
            $code = $edge['node']['attribute']['code'];
            if ($code !== $ergonodeKey) {
                continue;
            }
            $this->attributeTypeValidator->filterWrongAttributes(
                $edge['node']['attribute'] ?? [],
                $ergonodeAttributeMappingCollection,
                $context,
                ['sku' => $ergonodeData['sku']]
            );
//            $unitId = $this->unitProvider->getIdByName();
//            dump($edge);
//            dd($unitId);
//            dump($edge);
        }

//            dd('a');
//        $productScaleUnit = $swData['scale_unit'];

        return $productData;
    }
}