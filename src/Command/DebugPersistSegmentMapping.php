<?php

declare(strict_types=1);

namespace Strix\Ergonode\Command;

use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Strix\Ergonode\Processor\ProductVisibilityProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Temporary debug command
 */
class DebugPersistSegmentMapping extends Command
{
    protected static $defaultName = 'strix:debug:segment-mapping';

    private Context $context;

    private ProductVisibilityProcessor $productVisibilityProcessor;

    public function __construct(
        ProductVisibilityProcessor $productVisibilityProcessor
    ) {
        parent::__construct();

        $this->context = new Context(new SystemSource());
        $this->productVisibilityProcessor = $productVisibilityProcessor;
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

        $events = $this->productVisibilityProcessor->processStream($this->context);

        if (empty($events)) {
            $io->info('No actions performed.');

            return self::SUCCESS;
        }

        $io->success('Product visibility (segments) synced (Ergonode->Shopware).');
        foreach ($events as $event => $ids) {
            if (!empty($ids)) {
                $io->success(["Performed $event for IDs:", ...$ids]);
            }
        }

        return self::SUCCESS;
    }
}