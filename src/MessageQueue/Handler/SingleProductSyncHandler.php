<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\MessageQueue\Handler;

use Ergonode\IntegrationShopware\MessageQueue\Message\SingleProductSync;
use Ergonode\IntegrationShopware\Processor\ProductSyncProcessor;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Ergonode\IntegrationShopware\Util\Constants;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\DataAbstractionLayer\ProductIndexingMessage;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexerRegistry;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

use function array_merge;

#[AsMessageHandler]
class SingleProductSyncHandler extends AbstractSyncHandler
{
    private const MAX_PAGES_PER_RUN = 4;

    private ProductSyncProcessor $productSyncProcessor;
    private MessageBusInterface $messageBus;

    public function __construct(
        SyncHistoryLogger $syncHistoryService,
        LockFactory $lockFactory,
        LoggerInterface $ergonodeSyncLogger,
        ProductSyncProcessor $productSyncProcessor,
        MessageBusInterface $messageBus
    ) {
        parent::__construct($syncHistoryService, $lockFactory, $ergonodeSyncLogger);

        $this->productSyncProcessor = $productSyncProcessor;
        $this->messageBus = $messageBus;
    }

    public function __invoke(SingleProductSync $message)
    {
        $this->handleMessage($message);
    }

    public static function getHandledMessages(): iterable
    {
        return [SingleProductSync::class];
    }

    /**
     * @param SingleProductSync $message
     */
    protected function createContext($message): Context
    {
        if (!$message instanceof SingleProductSync) {
            throw new InvalidArgumentException(
                sprintf(
                    'Wrong message type provided. Expected %s. Got %s.',
                    SingleProductSync::class,
                    get_class($message)
                )
            );
        }

        $context = parent::createContext($message);
        $context->addState(EntityIndexerRegistry::DISABLE_INDEXING);

        if ($message->shouldAppendCategories()) {
            $context->addState(Constants::STATE_PRODUCT_APPEND_CATEGORIES);
        }

        return $context;
    }

    /**
     * @param SingleProductSync $message
     */
    public function runSync($message): int
    {
        if (!$message instanceof SingleProductSync) {
            throw new InvalidArgumentException(
                sprintf(
                    'Wrong message type provided. Expected %s. Got %s.',
                    SingleProductSync::class,
                    get_class($message)
                )
            );
        }

        $currentPage = 0;
        $count = 0;
        $result = null;

        try {
            $primaryKeys = [];
            do {
                $result = $this->productSyncProcessor->processSingle(
                    $message->getSku(),
                    $this->context
                );

                $count += $result->getProcessedEntityCount();
                $primaryKeys = array_merge($primaryKeys, $result->getPrimaryKeys());

                if (self::MAX_PAGES_PER_RUN !== null && ++$currentPage >= self::MAX_PAGES_PER_RUN) {
                    break;
                }
            } while ($result->hasNextPage());
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }

        if (false === empty($primaryKeys)) {
            $this->logger->info('Dispatching product indexing message');
            $indexingMessage = new ProductIndexingMessage($primaryKeys);
            $indexingMessage->setIndexer('product.indexer');
            $this->messageBus->dispatch($indexingMessage);
        }

        if (null !== $result) {
            if ($result->hasNextPage()) {
                $this->messageBus->dispatch(
                    new SingleProductSync($message->getSku(), $message->shouldAppendCategories())
                );
            } else {
                $this->productSyncProcessor->deleteOrphanedVariants($message->getSku(), $this->context);
            }
        }

        return $count;
    }
}
