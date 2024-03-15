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
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

/**
 * Temporary debug command
 */
#[AsCommand(name: 'ergonode:debug:category-persist')]
class DebugPersistCategories extends Command
{
    private MessageBusInterface $messageBus;

    private ErgonodeCursorManager $cursorManager;

    public function __construct(
        MessageBusInterface $messageBus,
        ErgonodeCursorManager $cursorManager
    ) {
        parent::__construct();

        $this->messageBus = $messageBus;
        $this->cursorManager = $cursorManager;
    }

    protected function configure(): void
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

        $this->messageBus->dispatch(new CategorySync(),[new TransportNamesStamp('sync')]);

        $io->success('Processed entities');

        return self::SUCCESS;
    }
}
