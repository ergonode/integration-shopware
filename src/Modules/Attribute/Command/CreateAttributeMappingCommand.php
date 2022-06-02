<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Attribute\Command;

use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Strix\Ergonode\Modules\Attribute\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingDefinition;
use Strix\Ergonode\Modules\Attribute\Service\AttributeMappingService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateAttributeMappingCommand extends Command
{
    protected static $defaultName = 'strix:ergonode:mapping:create';

    private Context $context;

    private EntityRepositoryInterface $repository;

    private AttributeMappingService $service;

    public function __construct(
        EntityRepositoryInterface $repository,
        AttributeMappingService $service
    ) {
        $this->context = new Context(new SystemSource());
        $this->repository = $repository;
        $this->service = $service;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument(
            'shopwareKey',
            InputArgument::REQUIRED,
            'Available keys: ' . implode(', ', $this->service->getMappableShopwareAttributes())
        );
        $this->addArgument(
            'ergonodeKey',
            InputArgument::REQUIRED,
            'Available keys: ' . implode(', ', $this->service->getAllErgonodeAttributes())
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $written = $this->repository->create([
            [
                'shopwareKey' => $input->getArgument('shopwareKey'),
                'ergonodeKey' => $input->getArgument('ergonodeKey'),
            ],
        ], $this->context);

        if (!empty($written->getErrors())) {
            $io->error($written->getErrors());

            return self::FAILURE;
        }

        $io->success('Product attribute mapping created');
        $io->success($written->getPrimaryKeys(ErgonodeAttributeMappingDefinition::ENTITY_NAME));

        return self::SUCCESS;
    }
}
