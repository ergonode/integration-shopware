<?php

declare(strict_types=1);

namespace Strix\Ergonode\Command;

use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Strix\Ergonode\Api\Client\CachedErgonodeGqlClient;
use Strix\Ergonode\Modules\Product\QueryBuilder\ProductQueryBuilder;
use Strix\Ergonode\Persistor\ProductPersistor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Temporary debug command
 */
class DebugPersistProduct extends Command
{
    protected static $defaultName = 'strix:debug:product-persist';

    private CachedErgonodeGqlClient $gqlClient;
    private ProductQueryBuilder $productQueryBuilder;
    private ProductPersistor $productPersistor;

    public function __construct(
        CachedErgonodeGqlClient $gqlClient,
        ProductQueryBuilder $productQueryBuilder,
        ProductPersistor $productPersistor
    ) {
        parent::__construct();

        $this->gqlClient = $gqlClient;
        $this->productQueryBuilder = $productQueryBuilder;
        $this->productPersistor = $productPersistor;
    }

    protected function configure()
    {
        parent::configure();

        $this->setHelp(
            'This debug command fetches Ergonode product by SKU and saves it with its variants. Uses request cache'
        );

        $this->addArgument('sku', InputArgument::REQUIRED, 'Ergonode product SKU');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $query = $this->productQueryBuilder->buildSingleProduct($input->getArgument('sku'));
        $result = $this->gqlClient->query($query);

        if (null === $result) {
            $io->error('Request failed');

            return self::FAILURE;
        }

        $this->productPersistor->persist($result, new Context(new SystemSource()));

        return self::SUCCESS;
    }
}