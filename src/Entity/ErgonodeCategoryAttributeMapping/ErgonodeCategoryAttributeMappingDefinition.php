<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\ErgonodeCategoryAttributeMapping;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ErgonodeCategoryAttributeMappingDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'ergonode_category_attribute_mapping';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ErgonodeCategoryAttributeMappingCollection::class;
    }

    public function getEntityClass(): string
    {
        return ErgonodeCategoryAttributeMappingEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new BoolField('active', 'active')),
            (new StringField('shopware_key', 'shopwareKey'))->addFlags(new Required()),
            (new StringField('ergonode_key', 'ergonodeKey'))->addFlags(new Required()),
        ]);
    }
}
