<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Entity\ErgonodeMappingExtension\ErgonodeMappingExtensionEntity;
use Ergonode\IntegrationShopware\Processor\Attribute\ManufacturerAttributeProcessor;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ProductManufacturerTransformer implements ProductDataTransformerInterface
{
    private EntityRepository $manufacturerRepository;

    private EntityRepository $mappingExtensionRepository;

    public function __construct(EntityRepository $manufacturerRepository, EntityRepository $mappingExtensionRepository)
    {
        $this->manufacturerRepository = $manufacturerRepository;
        $this->mappingExtensionRepository = $mappingExtensionRepository;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $shopwareData = $productData->getShopwareData();
        $ergonodeData = $productData->getErgonodeData();

        $shopwareData->resetManufacturer();;
        $manufacturerName = $ergonodeData->getManufacturer();
        if (!empty($manufacturerName)) {
            $manufacturerId = $this->findManufacturer($manufacturerName, $context);

            $shopwareData->setManufacturerId($manufacturerId);
        }

        $productData->setShopwareData($shopwareData);

        return $productData;
    }

    private function findManufacturer(string $name, Context $context): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('code', $name));
        $criteria->addFilter(new EqualsFilter('type', ManufacturerAttributeProcessor::MAPPING_TYPE));
        $result = $this->mappingExtensionRepository->search($criteria, $context);

        $mapping = $result->first();
        if ($mapping instanceof ErgonodeMappingExtensionEntity) {
            $manufacturerCriteria = new Criteria();
            $manufacturerCriteria->addFilter(new EqualsFilter('id', $mapping->getId()));
            $result = $this->manufacturerRepository->searchIds($manufacturerCriteria, $context);

            return $result->firstId();
        }

        return null;
    }
}
