<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor;

use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientFactory;
use Ergonode\IntegrationShopware\DTO\SyncCounterDTO;
use Ergonode\IntegrationShopware\Persistor\ProductVisibilityPersistor;
use Ergonode\IntegrationShopware\Provider\ErgonodeProductProvider;
use Shopware\Core\Framework\Context;

class ProductVisibilitySyncProcessor
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

    public function processStream(Context $context, int $productCount = self::DEFAULT_PRODUCT_COUNT): SyncCounterDTO
    {
        $counter = new SyncCounterDTO();

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

        foreach ($skuSalesChannelsMap as $sku => $salesChannelIds) {
            $counter->incrProcessedEntityCount(
                count($this->persistor->persist(strval($sku), $salesChannelIds, $context))
            );
        }

        return $counter;
    }
}