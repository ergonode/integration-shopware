<?php

declare(strict_types=1);

namespace Strix\Ergonode\Service\ScheduledTask;

use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Strix\Ergonode\Processor\ProductSyncProcessor;

class ProductSyncTaskHandler extends ScheduledTaskHandler
{
    private const MAX_PAGES_PER_RUN = 25;

    private ProductSyncProcessor $productSyncProcessor;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        ProductSyncProcessor $productSyncProcessor
    ) {
        parent::__construct($scheduledTaskRepository);
        $this->productSyncProcessor = $productSyncProcessor;
    }

    public static function getHandledMessages(): iterable
    {
        return [ScheduledTask::class];
    }

    public function run(): void
    {
        $context = new Context(new SystemSource());
        $currentPage = 0;

        while ($this->productSyncProcessor->processStream($context)) {
            if ($currentPage++ >= self::MAX_PAGES_PER_RUN) {
                break;
            }
        }
    }
}