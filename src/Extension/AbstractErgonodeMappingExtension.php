<?php

declare(strict_types=1);

namespace Strix\Ergonode\Extension;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Strix\Ergonode\Entity\ErgonodeMappingExtension\ErgonodeMappingExtensionEntity;
use Strix\Ergonode\Entity\ErgonodeMappingExtension\ErgonodeMappingExtensionDefinition;

abstract class AbstractErgonodeMappingExtension extends EntityExtension
{
    public const EXTENSION_NAME = 'ergonodeMappingExtension';

    public const STORAGE_NAME = 'ergonode_mapping_extension_id';

    public const PROPERTY_NAME = 'ergonodeMappingExtensionId';

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new FkField(
                self::STORAGE_NAME,
                self::PROPERTY_NAME,
                ErgonodeMappingExtensionDefinition::class
            )
        );

        $collection->add(
            (new OneToOneAssociationField(
                self::EXTENSION_NAME,
                self::STORAGE_NAME,
                'id',
                ErgonodeMappingExtensionDefinition::class,
                false
            ))->addFlags(new CascadeDelete())
        );
    }
}