<?php

declare(strict_types=1);

namespace Strix\Ergonode\Command;

use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Strix\Ergonode\Modules\Category\Provider\ErgonodeCategoryProvider;
use Strix\Ergonode\Persistor\CategoryPersistor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Temporary debug command
 */
class DebugPersistCategories extends Command
{
    protected static $defaultName = 'strix:debug:category-persist';

    private ErgonodeCategoryProvider $categoryProvider;

    private CategoryPersistor $categoryPersistor;

    public function __construct(
        ErgonodeCategoryProvider $categoryProvider,
        CategoryPersistor $categoryPersistor
    ) {
        parent::__construct();

        $this->categoryProvider = $categoryProvider;
        $this->categoryPersistor = $categoryPersistor;
    }

    protected function configure()
    {
        parent::configure();

        $this->setHelp(
            'This debug command fetches Ergonode category tree by code and saves it.'
        );

        $this->addArgument('code', InputArgument::REQUIRED, 'Root category code');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $categoryCollection = $this->categoryProvider->provideCategoryTree(
            $input->getArgument('code')
        );


        if (empty($categoryCollection)) {
            $io->error('Request failed');

            return self::FAILURE;
        }

        $this->categoryPersistor->persist($categoryCollection, new Context(new SystemSource()));

        return self::SUCCESS;
    }
}