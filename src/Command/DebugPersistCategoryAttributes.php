<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Command;

use Ergonode\IntegrationShopware\MessageQueue\Message\CategoryAttributesSync;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

/**
 * Temporary debug command
 */
#[AsCommand(name: 'ergonode:debug:category-attribute-persist')]
class DebugPersistCategoryAttributes extends Command
{
    private MessageBusInterface $messageBus;


    public function __construct(
        MessageBusInterface $messageBus,
    ) {
        parent::__construct();

        $this->messageBus = $messageBus;
    }

    protected function configure()
    {
        parent::configure();

        $this->setHelp(
            'This debug command fetches Ergonode category attributes and apply them to ergonode categories in Shopware.'
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

        $this->messageBus->dispatch(new CategoryAttributesSync(), [new TransportNamesStamp('sync')]);

        $io->success('Processed entities');

        return self::SUCCESS;
    }
}
