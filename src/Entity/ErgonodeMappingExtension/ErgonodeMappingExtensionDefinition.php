<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\ErgonodeMappingExtension;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ErgonodeMappingExtensionDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'ergonode_mapping_extension';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ErgonodeMappingExtensionEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new BoolField('active', 'active')),
            (new StringField('code', 'code', 128))->addFlags(new Required()),
            (new StringField('type', 'type', 128))->addFlags(new Required()),
        ]);
    }
}