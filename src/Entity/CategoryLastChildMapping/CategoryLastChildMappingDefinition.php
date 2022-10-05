<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\CategoryLastChildMapping;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class CategoryLastChildMappingDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'category_last_child_mapping';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return CategoryLastChildMappingEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new IdField('category_id', 'categoryId')),
            (new IdField('last_child_id', 'lastChildId'))->addFlags(new Required()),
        ]);
    }
}