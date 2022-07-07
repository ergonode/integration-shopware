<?php

declare(strict_types=1);

namespace Strix\Ergonode\Command;

use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Strix\Ergonode\Processor\AttributeSyncProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SyncAttributesCommand extends Command
{
    protected static $defaultName = 'strix:ergonode:attributes:sync';

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

        $entities = $this->processor->process($this->context);

        if (empty($entities)) {
            $io->info('No entities created.');

            return self::SUCCESS;
        }

        $io->success('Attributes synced (Ergonode->Shopware).');
        foreach ($entities as $entity => $ids) {
            if (!empty($ids)) {
                $io->success(["$entity:", ...$ids]);
            }
        }

        return self::SUCCESS;
    }
}
