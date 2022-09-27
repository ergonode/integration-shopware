<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Command;

use Ergonode\IntegrationShopware\Api\AttributeDeletedStreamResultsProxy;
use Ergonode\IntegrationShopware\Api\ProductDeletedStreamResultsProxy;
use Ergonode\IntegrationShopware\Manager\ErgonodeCursorManager;
use Ergonode\IntegrationShopware\Manager\OrphanEntitiesManager;
use Ergonode\IntegrationShopware\Service\ScheduledTask\FullSyncTask;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class DebugRunFullSync extends Command
{
    protected static $defaultName = 'ergonode:debug:sync';

    private Context $context;

    private OrphanEntitiesManager $orphanEntitiesManager;

    private ErgonodeCursorManager $cursorManager;

    private MessageBusInterface $messageBus;

    public function __construct(
        OrphanEntitiesManager $orphanEntitiesManager,
        ErgonodeCursorManager $cursorManager,
        MessageBusInterface $messageBus
    ) {
        parent::__construct();

        $this->context = new Context(new SystemSource());
        $this->orphanEntitiesManager = $orphanEntitiesManager;
        $this->cursorManager = $cursorManager;
        $this->messageBus = $messageBus;
    }

    protected function configure()
    {
        $this->setDescription(
            'Iterates through latest Ergonode deleted attributes and deletes matching Shopware Property Groups.'
        );


    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->messageBus->dispatch(new FullSyncTask());

        return 1;
    }
}
