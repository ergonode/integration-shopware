<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ProductScaleUnitTransformer implements ProductDataTransformerInterface
{
    private EntityRepository $mappingExtensionRepository;

    public function __construct(
        EntityRepository $mappingExtensionRepository
    ) {
        $this->mappingExtensionRepository = $mappingExtensionRepository;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $shopwareData = $productData->getShopwareData();
        $ergonodeData = $productData->getErgonodeData();

        $scaleUnit = $ergonodeData->getScaleUnit();
        if ($scaleUnit) {
            $shopwareData->setUnitId(null);
            if ($scaleUnit->getFirstOption()) {
                $unitCode = $scaleUnit->getFirstOption()->getCode();
                $criteria = new Criteria();
                $criteria->addFilter(new EqualsFilter('code', $unitCode));
                $scaleUnitId = $this->mappingExtensionRepository->searchIds($criteria, $context);
                if ($scaleUnitId->firstId()) {
                    $shopwareData->setUnitId($scaleUnitId->firstId());
                }
            }
        } elseif ($scaleUnit === false && $productData->getSwProduct()) {
            $shopwareData->setUnitId($productData->getSwProduct()->getUnitId());
        } else {
            $shopwareData->setUnitId(null);
        }

        $productData->setShopwareData($shopwareData);

        return $productData;
    }
}
