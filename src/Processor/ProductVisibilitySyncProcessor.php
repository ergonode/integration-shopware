<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor;

use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientFactory;
use Ergonode\IntegrationShopware\DTO\SyncCounterDTO;
use Ergonode\IntegrationShopware\MessageQueue\Message\SingleProductVisibilitySync;
use Ergonode\IntegrationShopware\Persistor\ProductVisibilityPersistor;
use Ergonode\IntegrationShopware\Provider\ErgonodeProductProvider;
use Shopware\Core\Framework\Context;
use Symfony\Component\Messenger\MessageBusInterface;

class ProductVisibilitySyncProcessor
{
    public const DEFAULT_PRODUCT_COUNT = 200;

    private ProductVisibilityPersistor $persistor;

    private ErgonodeGqlClientFactory $gqlClientFactory;

    private ErgonodeProductProvider $ergonodeProductProvider;

    private MessageBusInterface $messageBus;

    public function __construct(
        ProductVisibilityPersistor $persistor,
        ErgonodeGqlClientFactory $gqlClientFactory,
        ErgonodeProductProvider $ergonodeProductProvider,
        MessageBusInterface $messageBus
    ) {
        $this->persistor = $persistor;
        $this->gqlClientFactory = $gqlClientFactory;
        $this->ergonodeProductProvider = $ergonodeProductProvider;
        $this->messageBus = $messageBus;
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

        $chunk = array_chunk($skuSalesChannelsMap, 5000, true);

        foreach($chunk as $chunkRecord) {
            $this->messageBus->dispatch(new SingleProductVisibilitySync($chunkRecord));
        }

        return $counter;
    }


    public function processSingle(array $data, Context $context): SyncCounterDTO
    {
        $counter = new SyncCounterDTO();

        foreach ($data as $sku => $salesChannelIds) {
            $counter->incrProcessedEntityCount(
                count($this->persistor->persist(strval($sku), $salesChannelIds, $context))
            );
        }

        return $counter;
    }
}
