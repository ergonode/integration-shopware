<?php
declare(strict_types=1);
namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Entity\ErgonodeMappingExtension\ErgonodeMappingExtensionEntity;
use Ergonode\IntegrationShopware\Processor\Attribute\ManufacturerAttributeProcessor;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ProductManufacturerTransformer implements ProductDataTransformerInterface
{
    private EntityRepositoryInterface $manufacturerRepository;

    private EntityRepositoryInterface $mappingExtensionRepository;

    public function __construct(EntityRepositoryInterface $manufacturerRepository, EntityRepositoryInterface $mappingExtensionRepository)
    {
        $this->manufacturerRepository = $manufacturerRepository;
        $this->mappingExtensionRepository = $mappingExtensionRepository;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $shopwareData = $productData->getShopwareData();
        $manufacturerName = $shopwareData['manufacturer'] ?? null;
        if (empty($manufacturerName)) {
            return $productData;
        }

        $manufacturerId = $this->findManufacturer($manufacturerName, $context);

        $shopwareData['manufacturer'] = $manufacturerId ? ['id' => $manufacturerId] : null;
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
