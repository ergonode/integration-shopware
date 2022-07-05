<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Attribute\Command;

use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Strix\Ergonode\Modules\Attribute\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingDefinition;
use Strix\Ergonode\Service\AttributeMapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateAttributeMappingCommand extends Command
{
    protected static $defaultName = 'strix:ergonode:attributes:create-mapping';

    private Context $context;

    private EntityRepositoryInterface $repository;

    private AttributeMapper $attributeMapper;

    public function __construct(
        EntityRepositoryInterface $repository,
        AttributeMapper $attributeMapper
    ) {
        $this->context = new Context(new SystemSource());
        $this->repository = $repository;
        $this->attributeMapper = $attributeMapper;

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
            $io->error(['No Shopware key provided.', 'Available keys: ' . implode(', ', $this->attributeMapper->getMappableShopwareAttributes())]);

            return self::FAILURE;
        }
        if (empty($ergonodeKey)) {
            $io->error(['No Ergonode key provided.', 'Available keys: ' . implode(', ', $this->attributeMapper->getAllErgonodeAttributes())]);

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

        $io->success('Product attribute mapping created.');
        $io->success($written->getPrimaryKeys(ErgonodeAttributeMappingDefinition::ENTITY_NAME));

        return self::SUCCESS;
    }
}
