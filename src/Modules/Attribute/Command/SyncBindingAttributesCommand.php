<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Attribute\Command;

use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Strix\Ergonode\Modules\Attribute\Provider\ErgonodeAttributeProvider;
use Strix\Ergonode\Persistor\PropertyGroupPersistor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SyncBindingAttributesCommand extends Command
{
    protected static $defaultName = 'strix:ergonode:attributes:sync';

    private Context $context;

    private ErgonodeAttributeProvider $ergonodeAttributeProvider;

    private PropertyGroupPersistor $propertyGroupPersistor;

    public function __construct(
        ErgonodeAttributeProvider $ergonodeAttributeProvider,
        PropertyGroupPersistor $propertyGroupPersistor
    ) {
        parent::__construct();

        $this->context = new Context(new SystemSource());
        $this->ergonodeAttributeProvider = $ergonodeAttributeProvider;
        $this->propertyGroupPersistor = $propertyGroupPersistor;
    }

    protected function configure()
    {
        $this->setDescription(
            'Fetches select and multiselect attributes from Ergonode and saves them as PropertyGroup and PropertyGroupOption entities in Shopware.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $attributes = $this->ergonodeAttributeProvider->provideBindingAttributes();
        $entities = $this->propertyGroupPersistor->persistStream($attributes, $this->context);

        if (empty($attributes)) {
            $io->error('Empty response');

            return self::FAILURE;
        }

        $io->success('Property groups synchronized (Ergonode->Shopware).');
        foreach ($entities as $entity => $ids) {
            $io->success(["Created/updated $entity:", ...$ids]);
        }

        return self::SUCCESS;
    }
}
