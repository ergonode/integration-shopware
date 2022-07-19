<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\ErgonodeSyncHistory;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ErgonodeSyncHistoryDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'ergonode_sync_history';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ErgonodeSyncHistoryEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('name', 'name', 128))->addFlags(new Required()),
            (new StringField('status', 'status', 128))->addFlags(new Required()),
            (new IntField('total_success', 'totalSuccess'))->addFlags(new Required()),
            (new IntField('total_error', 'totalError'))->addFlags(new Required()),
            (new DateTimeField('start_date', 'startDate'))->addFlags(new Required()),
            new DateTimeField('end_date', 'endDate'),
        ]);
    }
}