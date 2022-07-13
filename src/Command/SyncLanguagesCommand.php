<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Command;

use Ergonode\IntegrationShopware\Processor\LanguageSyncProcessor;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SyncLanguagesCommand extends Command
{
    protected static $defaultName = 'strix:ergonode:languages:sync';

    private Context $context;

    private LanguageSyncProcessor $processor;

    public function __construct(
        LanguageSyncProcessor $processor
    ) {
        parent::__construct();

        $this->context = new Context(new SystemSource());
        $this->processor = $processor;
    }

    protected function configure()
    {
        $this->setDescription(
            'Fetches all languages from Ergonode and saves them as Language entities in Shopware.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $result = $this->processor->process($this->context);

        if (0 === $result->getProcessedEntityCount()) {
            $io->info('No entities processed.');

            return self::SUCCESS;
        }

        $io->success('Languages synchronized (Ergonode->Shopware).');

        return self::SUCCESS;
    }
}
