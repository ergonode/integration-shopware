<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductVisibilityDTO;
use Ergonode\IntegrationShopware\Provider\ProductProvider;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityEntity;
use Shopware\Core\Framework\Context;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use function array_values;
use function in_array;

class ProductVisibilityTransformer
{
    private ProductProvider $productProvider;

    private EntityRepository $productVisibilityRepository;

    public function __construct(
        ProductProvider $productProvider,
        EntityRepository $productVisibilityRepository
    ) {
        $this->productProvider = $productProvider;
        $this->productVisibilityRepository = $productVisibilityRepository;
    }

    public function transform(ProductVisibilityDTO $dto, Context $context): ProductVisibilityDTO
    {
        $sku = $dto->getSku();
        $productSalesChannelIds = $dto->getSalesChannelIds();

        $productId = $this->productProvider->getProductId($sku, $context);

        if (!$productId) {
            return $dto; // product missing
        }

        $visibilities = $this->getVisibility($productId, $context);

        if (0 === $visibilities->count()) {
            $payload = array_map(fn(string $salesChannelId) => [
                'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                'productId' => $productId,
                'salesChannelId' => $salesChannelId,
            ], $productSalesChannelIds);

            $dto->setCreatePayload($payload);

            return $dto;
        }

        $visibilityIdsToDelete = [];
        $newSalesChannelIds = $productSalesChannelIds;

        /** @var ProductVisibilityEntity $visibility */
        foreach ($visibilities as $visibility) {
            $salesChannelId = $visibility->getSalesChannelId();

            if (in_array($salesChannelId, $productSalesChannelIds)) {
                $newSalesChannelIds = array_diff($newSalesChannelIds, [$salesChannelId]);

                continue;
            }

            $visibilityIdsToDelete[] = $visibility->getId();
        }

        $dto->setDeletePayloadIds($visibilityIdsToDelete);

        $payload = array_map(fn(string $salesChannelId) => [
            'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
            'productId' => $productId,
            'salesChannelId' => $salesChannelId,
        ], array_values($newSalesChannelIds));

        $dto->setCreatePayload($payload);

        return $dto;
    }

    private function getVisibility(string $productId, Context $context): EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productId', $productId));
        return $this->productVisibilityRepository->search($criteria, $context);
    }
}
