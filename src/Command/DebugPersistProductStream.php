<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Command;

use Ergonode\IntegrationShopware\Processor\ProductSyncProcessor;
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
class DebugPersistProductStream extends Command
{
    protected static $defaultName = 'ergonode:debug:product-persist-stream';

    private ProductSyncProcessor $productSyncProcessor;

    public function __construct(
        ProductSyncProcessor $productSyncProcessor
    ) {
        parent::__construct();

        $this->productSyncProcessor = $productSyncProcessor;
    }

    protected function configure()
    {
        parent::configure();

        $this->setHelp(
            'This debug command fetches Ergonode product stream.'
        );

        $this->addOption(
            'limit',
            'l',
            InputOption::VALUE_OPTIONAL,
            'Limits how many pages of products are fetched from the stream'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = new Context(new SystemSource());
        $limit = $input->getOption('limit');

        $io = new SymfonyStyle($input, $output);
        $io->progressStart();

        $processedPages = 1;
        $entityCount = 0;
        $processTime = 0;
        $peakProcessMemory = 0;
        try {
            do {
                $io->progressAdvance();
                $result = $this->productSyncProcessor->processStream($context);

                $entityCount += $result->getProcessedEntityCount();
                if ($result->hasStopwatch()) {
                    $processEvent = $result->getStopwatch()->getSections()['__root__']->getEvent('process');
                    if (null !== $processEvent) {
                        $processTime += $processEvent->getDuration();
                        $peakProcessMemory = \max($peakProcessMemory, $processEvent->getMemory());
                    }
                }

                if ($limit !== null && $processedPages++ >= $limit) {
                    break;
                }
            } while ($result->hasNextPage());

            $io->progressFinish();
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success(\sprintf('Processed %d page(s) and %d entities', $processedPages - 1, $entityCount));
        $io->info(
            \sprintf(
                "Process time:\t%.02fms\nPeak memory:\t\t%.02fMB",
                $processTime,
                $peakProcessMemory / 1024 / 1024
            )
        );

        return Command::SUCCESS;
    }
}