<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Command;

use Ergonode\IntegrationShopware\MessageQueue\Message\ProductVisibilitySync;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

/**
 * Temporary debug command
 */
#[AsCommand(name: 'ergonode:debug:product-visibility')]
class DebugPersistVisibilityProduct extends Command
{
    private MessageBusInterface $messageBus;

    public function __construct(
        MessageBusInterface $messageBus
    ) {
        parent::__construct();
        $this->messageBus = $messageBus;
    }

    protected function configure()
    {
        parent::configure();

        $this->setHelp(
            'This debug command synchronize prouct visibility from Ergonode.'
        );
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->messageBus->dispatch(new ProductVisibilitySync(), [new TransportNamesStamp('sync')]);

        return self::SUCCESS;
    }
}
