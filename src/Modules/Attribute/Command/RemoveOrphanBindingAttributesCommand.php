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

class RemoveOrphanBindingAttributesCommand extends Command
{
    protected static $defaultName = 'strix:ergonode:attributes:remove-orphan';

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
        $entities = [];

        $iterator = $this->ergonodeAttributeProvider->provideDeletedBindingAttributes();
        foreach ($iterator as $deletedAttributes) {
            $entities = array_merge($entities, $this->propertyGroupPersistor->remove($deletedAttributes, $this->context));
        }

        if (empty($entities)) {
            $io->info('Could not find any orphan property groups');

            return self::SUCCESS;
        }

        $io->success('Deleted property groups removed (Ergonode->Shopware).');
        foreach ($entities as $entity => $ids) {
            $io->success(["Deleted $entity:", ...$ids]);
        }

        return self::SUCCESS;
    }
}
