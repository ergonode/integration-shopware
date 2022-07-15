<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Extension;

use Ergonode\IntegrationShopware\Entity\ErgonodeCategoryMappingExtension\ErgonodeCategoryMappingExtensionDefinition;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ErgonodeCategoryMappingExtension extends EntityExtension
{
    public const EXTENSION_NAME = 'ergonodeCategoryMappingExtension';

    public const STORAGE_NAME = 'ergonode_category_mapping_extension_id';

    public const PROPERTY_NAME = 'ergonodeCategoryMappingExtensionId';

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new FkField(
                self::STORAGE_NAME,
                self::PROPERTY_NAME,
                ErgonodeCategoryMappingExtensionDefinition::class
            )
        );

        $collection->add(
            (new OneToOneAssociationField(
                self::EXTENSION_NAME,
                self::STORAGE_NAME,
                'id',
                ErgonodeCategoryMappingExtensionDefinition::class,
                false
            ))->addFlags(new CascadeDelete())
        );
    }

    public function getDefinitionClass(): string
    {
        return CategoryDefinition::class;
    }
}