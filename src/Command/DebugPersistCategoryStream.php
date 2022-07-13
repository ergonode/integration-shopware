<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Command;

use Ergonode\IntegrationShopware\Processor\CategorySyncProcessor;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Temporary debug command
 */
class DebugPersistCategoryStream extends Command
{
    protected static $defaultName = 'strix:debug:category-persist-stream';

    private CategorySyncProcessor $categorySyncProcessor;

    public function __construct(
        CategorySyncProcessor $categorySyncProcessor
    ) {
        parent::__construct();

        $this->categorySyncProcessor = $categorySyncProcessor;
    }

    protected function configure()
    {
        parent::configure();

        $this->setHelp(
            'This debug command fetches Ergonode category stream.'
        );

        $this->addArgument(
            'treeCode',
            InputArgument::REQUIRED,
            'Category tree code'
        );

        $this->addOption(
            'limit',
            'l',
            InputOption::VALUE_OPTIONAL,
            'Limits how many pages of categories are fetched from the stream'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = new Context(new SystemSource());
        $limit = $input->getOption('limit');
        $treeCode = $input->getArgument('treeCode');

        $io = new SymfonyStyle($input, $output);
        $io->progressStart();

        $processedPages = 0;
        try {
            while ($this->categorySyncProcessor->processStream($treeCode, $context)) {
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