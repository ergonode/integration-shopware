<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Command;

use Ergonode\IntegrationShopware\Processor\CategoryTreeSyncProcessor;
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
class DebugPersistCategoryTreeStream extends Command
{
    protected static $defaultName = 'ergonode:debug:category-tree-persist-stream';

    private CategoryTreeSyncProcessor $categoryTreeSyncProcessor;

    public function __construct(
        CategoryTreeSyncProcessor $categoryTreeSyncProcessor
    ) {
        parent::__construct();

        $this->categoryTreeSyncProcessor = $categoryTreeSyncProcessor;
    }

    protected function configure()
    {
        parent::configure();

        $this->setHelp(
            'This debug command fetches Ergonode category tree stream.'
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

        $io = new SymfonyStyle($input, $output);
        $io->progressStart();

        $processedPages = 0;
        try {
            while ($this->categoryTreeSyncProcessor->processStream($context)) {
                $io->progressAdvance();
                if ($processedPages++ >= $limit && $limit !== null) {
                    break;
                }
            }
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        } finally {
            $io->progressFinish();
        }
        $io->success(\sprintf('Processed %d page(s)', $processedPages));

        return Command::SUCCESS;
    }
}