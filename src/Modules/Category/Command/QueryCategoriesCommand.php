<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Category\Command;

use Strix\Ergonode\Modules\Category\Provider\ErgonodeCategoryProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class QueryCategoriesCommand extends Command
{
    protected static $defaultName = 'strix:ergonode:query:categories';

    private ErgonodeCategoryProvider $categoryProvider;

    public function __construct(
        ErgonodeCategoryProvider $categoryProvider
    ) {
        parent::__construct();

        $this->categoryProvider = $categoryProvider;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

//        $result = $this->categoryProvider->provideCategoryTree('default_tree');
        $result = $this->categoryProvider->provideCategoryTree('empty_tree');

        if (empty($result)) {
            $io->error('Empty response');

            return self::FAILURE;
        }

        $io->success('Got following response:');

        dump($result);

        return self::SUCCESS;
    }
}