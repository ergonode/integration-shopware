<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Product\Command;

use Strix\Ergonode\Modules\Product\Provider\ErgonodeProductProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class QueryProductsCommand extends Command
{
    protected static $defaultName = 'strix:ergonode:query:products';

    private ErgonodeProductProvider $productProvider;

    public function __construct(
        ErgonodeProductProvider $productProvider
    ) {
        parent::__construct();

        $this->productProvider = $productProvider;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $result = $this->productProvider->provide(4);
//        $result = $this->productProvider->provideDeleted(5);

        $products = $result->getEdges();

        if (empty($products)) {
            $io->error('Empty response');

            return self::FAILURE;
        }

        $io->success('Got following response:');

        dump($products);

        return self::SUCCESS;
    }
}