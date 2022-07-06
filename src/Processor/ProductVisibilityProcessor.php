<?php

declare(strict_types=1);

namespace Strix\Ergonode\Processor;

use Shopware\Core\Framework\Context;
use Strix\Ergonode\Api\Client\ErgonodeGqlClientFactory;
use Strix\Ergonode\Modules\Product\Provider\ErgonodeProductProvider;
use Strix\Ergonode\Persistor\ProductVisibilityPersistor;

class ProductVisibilityProcessor
{
    public const DEFAULT_PRODUCT_COUNT = 200;

    private ProductVisibilityPersistor $persistor;

    private ErgonodeGqlClientFactory $gqlClientFactory;

    private ErgonodeProductProvider $ergonodeProductProvider;

    public function __construct(
        ProductVisibilityPersistor $persistor,
        ErgonodeGqlClientFactory $gqlClientFactory,
        ErgonodeProductProvider $ergonodeProductProvider
    ) {
        $this->persistor = $persistor;
        $this->gqlClientFactory = $gqlClientFactory;
        $this->ergonodeProductProvider = $ergonodeProductProvider;
    }

    public function processStream(Context $context, int $productCount = self::DEFAULT_PRODUCT_COUNT): array
    {
        $gqlClientGenerator = $this->gqlClientFactory->createForEverySalesChannel($context);

        $skuSalesChannelsMap = [];

        foreach ($gqlClientGenerator as $salesChannelClient) {
            $productsGenerator = $this->ergonodeProductProvider->provideOnlySkus(
                $productCount,
                null,
                $salesChannelClient
            );

            foreach ($productsGenerator as $results) {
                foreach ($results->getEdges() as $product) {
                    $skuSalesChannelsMap[$product['node']['sku']][] = $salesChannelClient->getSalesChannelId();
                }
            }
        }

        $newEvents = [];
        foreach ($skuSalesChannelsMap as $sku => $salesChannelIds) {
            $newEvents[] = $this->persistor->persist(strval($sku), $salesChannelIds, $context);
        }

        return array_merge_recursive([], ...$newEvents);
    }
}