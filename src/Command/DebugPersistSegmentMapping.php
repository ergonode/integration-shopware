<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Command;

use Ergonode\IntegrationShopware\Processor\ProductVisibilitySyncProcessor;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Temporary debug command
 */
class DebugPersistSegmentMapping extends Command
{
    protected static $defaultName = 'ergonode:debug:segment-mapping';

    private Context $context;

    private ProductVisibilitySyncProcessor $productVisibilitySyncProcessor;

    public function __construct(
        ProductVisibilitySyncProcessor $productVisibilitySyncProcessor
    ) {
        parent::__construct();

        $this->context = new Context(new SystemSource());
        $this->productVisibilitySyncProcessor = $productVisibilitySyncProcessor;
    }

    protected function configure()
    {
        parent::configure();

        $this->setHelp(
            'This debug command fetches SKUs using sales channel specific API key and connects those products to the sales channel.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $count = $this->productVisibilitySyncProcessor->processStream($this->context);

        if (0 === $count->getProcessedEntityCount()) {
            $io->info('No actions performed.');

            return self::SUCCESS;
        }

        $io->success('Product visibility (segments) synced (Ergonode->Shopware).');

        return self::SUCCESS;
    }
}