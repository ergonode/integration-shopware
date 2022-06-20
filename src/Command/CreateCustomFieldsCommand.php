<?php

declare(strict_types=1);

namespace Strix\Ergonode\Command;

use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Strix\Ergonode\Manager\ProductCustomFieldManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateCustomFieldsCommand extends Command
{
    protected static $defaultName = 'strix:ergonode:custom-fields:create';

    private Context $context;

    private ProductCustomFieldManager $manager;

    public function __construct(
        ProductCustomFieldManager $manager
    ) {
        parent::__construct();

        $this->context = new Context(new SystemSource());
        $this->manager = $manager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $entities = $this->manager->prepareCustomFields($this->context);

        if (empty($entities)) {
            $io->info('No custom fields created.');

            return self::SUCCESS;
        }

        $io->success('Custom fields created (Ergonode->Shopware).');
        foreach ($entities as $entity => $ids) {
            $io->success(["Created/updated $entity:", ...$ids]);
        }

        return self::SUCCESS;
    }
}
