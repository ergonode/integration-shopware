<?php
declare(strict_types=1);
namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ProductManufacturerTransformer implements ProductDataTransformerInterface
{
    private EntityRepositoryInterface $manufacturerRepository;

    public function __construct(EntityRepositoryInterface $manufacturerRepository)
    {
        $this->manufacturerRepository = $manufacturerRepository;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $shopwareData = $productData->getShopwareData();
        $manufacturerName = $shopwareData['manufacturer'] ?? null;
        if (empty($manufacturerName)) {
            return $productData;
        }

        $manufacturerId = $this->findOrCreateManufacturer($manufacturerName, $context);

        $shopwareData['manufacturer'] = ['id' => $manufacturerId];
        $productData->setShopwareData($shopwareData);

        return $productData;
    }

    private function findOrCreateManufacturer(string $name, Context $context): string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $name));
        $result = $this->manufacturerRepository->search($criteria, $context);

        if ($result->count() > 0) {
            $manufacturerEntity = $result->getEntities()->first();
            if (!$manufacturerEntity instanceof ProductManufacturerEntity) {
                throw new \RuntimeException('Something went wrong when fetching manufacturer entity');
            }

            return $manufacturerEntity->getId();
        }

        $writeResult = $this->manufacturerRepository->upsert([['name' => $name]], $context);
        $keys = $writeResult->getPrimaryKeys(ProductManufacturerDefinition::ENTITY_NAME);

        if (empty($keys)) {
            throw new \Exception(sprintf('Failed creating manufacturer %s', $name));
        }

        return $keys[0];
    }
}
