<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Attribute\Command;

use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Strix\Ergonode\Manager\OrphanEntitiesManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RemoveOrphanBindingAttributesCommand extends Command
{
    protected static $defaultName = 'strix:ergonode:attributes:remove-orphan';

    private Context $context;

    private OrphanEntitiesManager $orphanEntitiesManager;

    public function __construct(
        OrphanEntitiesManager $orphanEntitiesManager
    ) {
        parent::__construct();

        $this->context = new Context(new SystemSource());
        $this->orphanEntitiesManager = $orphanEntitiesManager;
    }

    protected function configure()
    {
        $this->setDescription(
            'Iterates through latest Ergonode deleted attributes and deletes matching Shopware Property Groups.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $entities = $this->orphanEntitiesManager->cleanPropertyGroups($this->context);

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
