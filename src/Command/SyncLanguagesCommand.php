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
    protected static $defaultName = 'ergonode:languages:sync';

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

        $entities = $this->processor->process($this->context);

        if (empty($entities)) {
            $io->info('No new languages created.');

            return self::SUCCESS;
        }

        $io->success('Languages synchronized (Ergonode->Shopware).');
        foreach ($entities as $entity => $ids) {
            if (!empty($ids)) {
                $io->success(["Created $entity:", ...$ids]);
            }
        }

        return self::SUCCESS;
    }
}
