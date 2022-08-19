<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Command;

use Ergonode\IntegrationShopware\Api\AttributeDeletedStreamResultsProxy;
use Ergonode\IntegrationShopware\Api\ProductDeletedStreamResultsProxy;
use Ergonode\IntegrationShopware\Manager\ErgonodeCursorManager;
use Ergonode\IntegrationShopware\Manager\OrphanEntitiesManager;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DebugAttributeDeletedStream extends Command
{
    protected static $defaultName = 'ergonode:debug:attribute-deleted-stream';

    private Context $context;

    private OrphanEntitiesManager $orphanEntitiesManager;

    private ErgonodeCursorManager $cursorManager;

    public function __construct(
        OrphanEntitiesManager $orphanEntitiesManager,
        ErgonodeCursorManager $cursorManager
    ) {
        parent::__construct();

        $this->context = new Context(new SystemSource());
        $this->orphanEntitiesManager = $orphanEntitiesManager;
        $this->cursorManager = $cursorManager;
    }

    protected function configure()
    {
        $this->setDescription(
            'Iterates through latest Ergonode deleted attributes and deletes matching Shopware Property Groups.'
        );

        $this->addOption(
            'force',
            null,
            InputOption::VALUE_NONE,
            'Use this flag to clear saved cursors before running the handler'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('force')) {
            $context = new Context(new SystemSource());

            $this->cursorManager->deleteCursor(AttributeDeletedStreamResultsProxy::MAIN_FIELD, $context);

            $io->info('Cursors deleted');
        }

        $entities = $this->orphanEntitiesManager->cleanAttributes($this->context);

        if (empty($entities)) {
            $io->info('Could not find any orphan property groups');

            return self::SUCCESS;
        }

        $io->success('Orphan property groups deleted (Ergonode->Shopware).');
        foreach ($entities as $entity => $ids) {
            $io->success(["Deleted $entity:", ...$ids]);
        }

        return self::SUCCESS;
    }
}
