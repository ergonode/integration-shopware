<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Command;

use Ergonode\IntegrationShopware\Processor\DeletedProductSyncProcessor;
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
class DebugProductDeletedStream extends Command
{
    protected static $defaultName = 'ergonode:debug:product-deleted-stream';

    private DeletedProductSyncProcessor $deletedProductSyncProcessor;

    public function __construct(
        DeletedProductSyncProcessor $deletedProductSyncProcessor
    ) {
        parent::__construct();
        $this->deletedProductSyncProcessor = $deletedProductSyncProcessor;
    }

    protected function configure()
    {
        parent::configure();

        $this->setHelp(
            'This debug command removed products from Shopware found in Ergonode productDeletedStream.'
        );

        $this->addOption(
            'limit',
            'l',
            InputOption::VALUE_OPTIONAL,
            'Limits how many pages of deleted products are fetched from the stream'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = new Context(new SystemSource());
        $limit = $input->getOption('limit');

        $io = new SymfonyStyle($input, $output);
        $io->progressStart();

        $processedPages = 0;
        try {
            while ($this->deletedProductSyncProcessor->processStream($context)->hasNextPage()) {
                $io->progressAdvance();
                if ($processedPages++ >= $limit && $limit !== null) {
                    break;
                }
            }

            $io->progressFinish();
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success(\sprintf('Processed %d page(s)', $processedPages));

        return Command::SUCCESS;
    }
}