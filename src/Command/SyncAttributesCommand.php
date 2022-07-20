<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Command;

use Ergonode\IntegrationShopware\Processor\AttributeSyncProcessor;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SyncAttributesCommand extends Command
{
    protected static $defaultName = 'ergonode:attributes:sync';

    private Context $context;

    private AttributeSyncProcessor $processor;

    public function __construct(
        AttributeSyncProcessor $processor
    ) {
        parent::__construct();

        $this->context = new Context(new SystemSource());
        $this->processor = $processor;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $result = $this->processor->process($this->context);

        if (0 === $result->getProcessedEntityCount()) {
            $io->info('No entities processed.');

            return self::SUCCESS;
        }

        $io->success('Attributes synced (Ergonode->Shopware).');

        return self::SUCCESS;
    }
}
