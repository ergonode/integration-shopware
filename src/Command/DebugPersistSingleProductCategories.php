<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Command;

use Ergonode\IntegrationShopware\MessageQueue\Handler\ProductCategorySyncHandler;
use Ergonode\IntegrationShopware\MessageQueue\Message\SingleProductCategorySync;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Temporary debug command
 */
#[AsCommand(name: 'ergonode:debug:product-category-persist')]
class DebugPersistSingleProductCategories extends Command
{
    private ProductCategorySyncHandler $handler;

    public function __construct(
        ProductCategorySyncHandler $handler
    ) {
        parent::__construct();
        $this->handler = $handler;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setHelp(
            'This debug command fetches Ergonode product by SKU and saves category of products.'
        );

        $this->addArgument('sku', InputArgument::REQUIRED, 'Ergonode product SKU');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sku = $input->getArgument('sku');

        $this->handler->handleMessage(new SingleProductCategorySync($sku));

        return self::SUCCESS;
    }
}
