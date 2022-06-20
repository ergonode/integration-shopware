<?php

declare(strict_types=1);

namespace Strix\Ergonode\Manager;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetDefinition;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetEntity;
use Shopware\Core\System\CustomField\CustomFieldDefinition;
use Strix\Ergonode\Provider\ConfigProvider;

class ProductCustomFieldManager
{
    public const SET_NAME = 'strix_ergonode_custom_fields';

    private EntityRepositoryInterface $customFieldRepository;

    private EntityRepositoryInterface $customFieldSetRepository;

    private ?string $customFieldSetId = null;

    private ConfigProvider $configProvider;

    public function __construct(
        EntityRepositoryInterface $customFieldRepository,
        EntityRepositoryInterface $customFieldSetRepository,
        ConfigProvider $configProvider
    ) {
        $this->customFieldRepository = $customFieldRepository;
        $this->customFieldSetRepository = $customFieldSetRepository;
        $this->configProvider = $configProvider;
    }

    public function prepareCustomFields(Context $context): array
    {
        $entities = [];

        foreach ($this->configProvider->getErgonodeCustomFields() as $name) {
            $this->createCustomField($name, $name, $context);
        }

        return $entities;
    }

    public function createCustomField(string $name, string $label, Context $context): array
    {
        if ($this->customFieldExists($name, $context)) {
            return [];
        }

        $this->initCustomFieldSet($context);

        $written = $this->customFieldRepository->create([
            'name' => $name,
            'config' => [
                'label' => [
                    'en-GB' => $label,
                ],
            ],
            'customFieldSetId' => $this->getErgonodeCustomFieldSetId($context),
        ], $context);

        return [
            CustomFieldDefinition::ENTITY_NAME => $written->getPrimaryKeys(CustomFieldDefinition::ENTITY_NAME),
        ];
    }

    public function initCustomFieldSet(Context $context): void
    {
        if ($this->customFieldSetExists($context)) {
            return;
        }

        $written = $this->customFieldSetRepository->create([
            'name' => self::SET_NAME,
            'config' => [
                'label' => [
                    'en-GB' => 'Ergonode Custom Fields',
                ],
            ],
            'relations' => [
                'entity_name' => ProductDefinition::ENTITY_NAME,
            ],
        ], $context);

        $setIds = $written->getPrimaryKeys(CustomFieldSetDefinition::ENTITY_NAME);
        $this->customFieldSetId = reset($setIds);
    }

    private function customFieldExists(string $name, Context $context): bool
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $name));
        $criteria->addFilter(new EqualsFilter('customFieldSet.name', self::SET_NAME));
        $criteria->addAssociation('customFieldSet');

        return 0 < $this->customFieldRepository->search($criteria, $context)->count();
    }

    private function getCustomFieldSet(Context $context): ?CustomFieldSetEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', self::SET_NAME));

        return $this->customFieldSetRepository->search($criteria, $context)->first();
    }

    private function customFieldSetExists(Context $context): bool
    {
        return $this->getCustomFieldSet($context) instanceof CustomFieldSetEntity;
    }

    private function getErgonodeCustomFieldSetId(Context $context): ?string
    {
        if (null === $this->customFieldSetId) {
            $set = $this->getCustomFieldSet($context);

            if ($set instanceof CustomFieldSetEntity) {
                $this->customFieldSetId = $set->getId();
            }
        }

        return $this->customFieldSetId;
    }
}