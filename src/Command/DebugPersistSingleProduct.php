<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Command;

use Ergonode\IntegrationShopware\MessageQueue\Handler\SingleProductSyncHandler;
use Ergonode\IntegrationShopware\MessageQueue\Message\SingleProductSync;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Temporary debug command
 */
#[AsCommand(name: 'ergonode:debug:product-persist')]
class DebugPersistSingleProduct extends Command
{
    private SingleProductSyncHandler $singleProductSyncHandler;

    public function __construct(
        SingleProductSyncHandler $singleProductSyncHandler
    ) {
        parent::__construct();
        $this->singleProductSyncHandler = $singleProductSyncHandler;
    }

    protected function configure()
    {
        parent::configure();

        $this->setHelp(
            'This debug command fetches Ergonode product by SKU and saves it with its variants.'
        );

        $this->addArgument('sku', InputArgument::REQUIRED, 'Ergonode product SKU');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sku = $input->getArgument('sku');

        $this->singleProductSyncHandler->handle(new SingleProductSync($sku));

        return self::SUCCESS;
    }
}
