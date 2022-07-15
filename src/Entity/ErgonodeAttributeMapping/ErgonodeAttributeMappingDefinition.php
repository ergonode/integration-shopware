<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ErgonodeAttributeMappingDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'strix_ergonode_attribute_mapping';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ErgonodeAttributeMappingCollection::class;
    }

    public function getEntityClass(): string
    {
        return ErgonodeAttributeMappingEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new StringField('shopware_key', 'shopwareKey'))->addFlags(new Required()),
            (new StringField('ergonode_key', 'ergonodeKey'))->addFlags(new Required()),
        ]);
    }
}
