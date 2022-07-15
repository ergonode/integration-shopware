<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Manager;

use Ergonode\IntegrationShopware\Entity\ErgonodeMappingExtension\ErgonodeMappingExtensionEntity;
use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class ExtensionManager
{
    public function getEntityExtensionId(Entity $entity): ?string
    {
        $extension = $entity->getExtension(AbstractErgonodeMappingExtension::EXTENSION_NAME);
        if ($extension instanceof ErgonodeMappingExtensionEntity) {
            return $extension->getId();
        }

        return null;
    }
}