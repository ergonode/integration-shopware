<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Provider\UnitProvider;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ProductScaleUnitTransformer implements ProductDataTransformerInterface
{
    private EntityRepositoryInterface $mappingExtensionRepository;

    public function __construct(
        EntityRepositoryInterface $mappingExtensionRepository
    ) {
        $this->mappingExtensionRepository = $mappingExtensionRepository;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $swData = $productData->getShopwareData();
        $scaleUnit = $swData['scaleUnit'] ?? null;
        if (is_null($scaleUnit)) {
            return $productData;
        }
        unset($swData['scaleUnit']);

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('code', $scaleUnit));
        $scaleUnitId = $this->mappingExtensionRepository->searchIds($criteria, $context);
        if (!$scaleUnitId->firstId()) {
            return $productData;
        }

        $swData['unitId'] = $scaleUnitId->firstId();
        $productData->setShopwareData($swData);

        return $productData;
    }
}
