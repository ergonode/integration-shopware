<?php

declare(strict_types=1);

namespace Strix\Ergonode\Service\ScheduledTask;

use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Strix\Ergonode\Processor\CategorySyncProcessor;
use Strix\Ergonode\Provider\ConfigProvider;

class CategorySyncTaskHandler extends ScheduledTaskHandler
{
    private const MAX_PAGES_PER_RUN = 25;

    private CategorySyncProcessor $categorySyncProcessor;
    private ConfigProvider $configProvider;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        CategorySyncProcessor $categorySyncProcessor,
        ConfigProvider $configProvider
    ) {
        parent::__construct($scheduledTaskRepository);
        $this->categorySyncProcessor = $categorySyncProcessor;
        $this->configProvider = $configProvider;
    }

    public static function getHandledMessages(): iterable
    {
        return [CategorySyncTask::class];
    }

    public function run(): void
    {
        $context = new Context(new SystemSource());
        $currentPage = 0;

        $categoryTreeCode = $this->configProvider->getCategoryTreeCode();
        if (empty($categoryTreeCode)) {
            throw new \RuntimeException('Could not find category tree code in plugin config.');
        }

        while ($this->categorySyncProcessor->processStream($categoryTreeCode, $context)) {
            if ($currentPage++ >= self::MAX_PAGES_PER_RUN) {
                break;
            }
        }
    }
}