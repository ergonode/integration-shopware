<?php

declare(strict_types=1);

namespace Strix\Ergonode\Command;

use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Strix\Ergonode\Exception\MissingRequiredProductMappingException;
use Strix\Ergonode\Processor\ProductSyncProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SyncProductsCommand extends Command
{
    protected static $defaultName = 'strix:ergonode:products:sync';

    private Context $context;

    private ProductSyncProcessor $processor;

    public function __construct(
        ProductSyncProcessor $processor
    ) {
        parent::__construct();

        $this->context = new Context(new SystemSource());
        $this->processor = $processor;
    }

    protected function configure()
    {
        $this->setDescription(
            'Fetches all products from Ergonode and saves them as Product entities in Shopware.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $entities = $this->processor->process($this->context);
        } catch (MissingRequiredProductMappingException $e) {
            $io->error($e->getMessage());

            return self::FAILURE;
        }

        if (empty($entities)) {
            $io->info('No new products created.');

            return self::SUCCESS;
        }

        $io->success('Products synchronized (Ergonode->Shopware).');
        foreach ($entities as $entity => $ids) {
            $io->success(["Created $entity:", ...$ids]);
        }

        return self::SUCCESS;
    }
}
