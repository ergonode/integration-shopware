<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Command;

use Ergonode\IntegrationShopware\Entity\ErgonodeCategoryAttributeMapping\ErgonodeCategoryAttributeMappingDefinition;
use Ergonode\IntegrationShopware\Provider\MappableFieldsProvider;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'ergonode:category-attributes:create-mapping')]
class CreateCategoryAttributeMappingCommand extends Command
{
    private Context $context;

    public function __construct(
        private EntityRepository $repository,
        private MappableFieldsProvider $mappableFieldsProvider
    ) {
        $this->context = new Context(new SystemSource());

        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument(
            'shopwareKey'
        );
        $this->addArgument(
            'ergonodeKey'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $shopwareKey = $input->getArgument('shopwareKey');
        $ergonodeKey = $input->getArgument('ergonodeKey');

        if (empty($shopwareKey)) {
            $io->error(['No Shopware key provided.', 'Available keys: '
                . implode(', ', $this->mappableFieldsProvider->getShopwareCategoriesAttributesWithTypes())]);

            return self::FAILURE;
        }
        if (empty($ergonodeKey)) {
            $io->error(['No Ergonode key provided.', 'Available keys: '
                . implode(', ', $this->mappableFieldsProvider->getErgonodeCategoryAttributesWithTypes())]);

            return self::FAILURE;
        }

        $written = $this->repository->create([
            [
                'shopwareKey' => $shopwareKey,
                'ergonodeKey' => $ergonodeKey,
            ],
        ], $this->context);

        if (!empty($written->getErrors())) {
            $io->error($written->getErrors());

            return self::FAILURE;
        }

        $io->success('Product category attribute mapping created.');
        $io->success($written->getPrimaryKeys(ErgonodeCategoryAttributeMappingDefinition::ENTITY_NAME));

        return self::SUCCESS;
    }
}
