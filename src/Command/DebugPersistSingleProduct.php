<?php

declare(strict_types=1);

namespace Strix\Ergonode\Command;

use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Strix\Ergonode\Api\Client\ErgonodeGqlClientInterface;
use Strix\Ergonode\Modules\Product\Api\ProductResultsProxy;
use Strix\Ergonode\Modules\Product\QueryBuilder\ProductQueryBuilder;
use Strix\Ergonode\Persistor\ProductPersistor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Temporary debug command
 */
class DebugPersistSingleProduct extends Command
{
    protected static $defaultName = 'strix:debug:product-persist';

    private ErgonodeGqlClientInterface $gqlClient;
    private ProductQueryBuilder $productQueryBuilder;
    private ProductPersistor $productPersistor;
    private CacheInterface $gqlRequestCache;

    public function __construct(
        ErgonodeGqlClientInterface $gqlClient,
        ProductQueryBuilder $productQueryBuilder,
        ProductPersistor $productPersistor,
        CacheInterface $gqlRequestCache
    ) {
        parent::__construct();

        $this->gqlClient = $gqlClient;
        $this->productQueryBuilder = $productQueryBuilder;
        $this->productPersistor = $productPersistor;
        $this->gqlRequestCache = $gqlRequestCache;
    }

    protected function configure()
    {
        parent::configure();

        $this->setHelp(
            'This debug command fetches Ergonode product by SKU and saves it with its variants.'
        );

        $this->addArgument('sku', InputArgument::REQUIRED, 'Ergonode product SKU');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $sku = $input->getArgument('sku');

        /** @var ProductResultsProxy|null $result */
        $result = $this->gqlRequestCache->get(
            $sku,
            function () use ($sku) {
                $query = $this->productQueryBuilder->buildProductWithVariants($sku);
                return $this->gqlClient->query($query, ProductResultsProxy::class);
            }
        );

        if (null === $result) {
            $io->error('Request failed');

            return self::FAILURE;
        }

        $this->productPersistor->persist(
            $result->getProductData(),
            new Context(new SystemSource())
        );

        return self::SUCCESS;
    }
}