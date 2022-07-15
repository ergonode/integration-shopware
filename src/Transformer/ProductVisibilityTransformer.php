<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductVisibilityDTO;
use Ergonode\IntegrationShopware\Provider\ProductProvider;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityEntity;
use Shopware\Core\Framework\Context;

use function array_values;
use function in_array;

class ProductVisibilityTransformer
{
    private ProductProvider $productProvider;

    public function __construct(
        ProductProvider $productProvider
    ) {
        $this->productProvider = $productProvider;
    }

    public function transform(ProductVisibilityDTO $dto, Context $context): ProductVisibilityDTO
    {
        $sku = $dto->getSku();
        $productSalesChannelIds = $dto->getSalesChannelIds();

        $product = $this->productProvider->getProductBySku($sku, $context, ['visibilities']);

        if (null === $product) {
            return $dto; // product missing
        }

        $visibilities = $product->getVisibilities();

        if (null === $visibilities) {
            return $dto; // association missing
        }

        if (0 === $visibilities->count()) {
            $payload = array_map(fn(string $salesChannelId) => [
                'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                'productId' => $product->getId(),
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
            'productId' => $product->getId(),
            'salesChannelId' => $salesChannelId,
        ], array_values($newSalesChannelIds));

        $dto->setCreatePayload($payload);

        return $dto;
    }
}