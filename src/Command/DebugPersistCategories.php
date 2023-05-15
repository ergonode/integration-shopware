<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Command;

use Ergonode\IntegrationShopware\Api\CategoryStreamResultsProxy;
use Ergonode\IntegrationShopware\Api\CategoryTreeStreamResultsProxy;
use Ergonode\IntegrationShopware\Manager\ErgonodeCursorManager;
use Ergonode\IntegrationShopware\MessageQueue\Handler\CategorySyncHandler;
use Ergonode\IntegrationShopware\MessageQueue\Message\CategorySync;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Temporary debug command
 */
class DebugPersistCategories extends Command
{
    protected static $defaultName = 'ergonode:debug:category-persist';

    private CategorySyncHandler $handler;

    private ErgonodeCursorManager $cursorManager;

    public function __construct(
        CategorySyncHandler $handler,
        ErgonodeCursorManager $cursorManager
    ) {
        parent::__construct();

        $this->handler = $handler;
        $this->cursorManager = $cursorManager;
    }

    protected function configure()
    {
        parent::configure();

        $this->setHelp(
            'This debug command fetches Ergonode category tree.'
        );

        $this->addOption(
            'force',
            null,
            InputOption::VALUE_NONE,
            'Use this flag to clear saved cursors before running the handler'
        );
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('force')) {
            $context = new Context(new SystemSource());

            $this->cursorManager->deleteCursors(
                [
                    CategoryStreamResultsProxy::MAIN_FIELD,
                    CategoryTreeStreamResultsProxy::MAIN_FIELD,
                    CategoryTreeStreamResultsProxy::TREE_LEAF_LIST_CURSOR,
                ],
                $context
            );

            $io->info('Cursors deleted');
        }

        $this->handler->handle(new CategorySync());

        $io->success('Processed entities');

        return self::SUCCESS;
    }
}
