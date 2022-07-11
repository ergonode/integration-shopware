<?php

declare(strict_types=1);

namespace Strix\Ergonode\Manager;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Strix\Ergonode\Entity\ErgonodeMappingExtension\ErgonodeMappingExtensionEntity;
use Strix\Ergonode\Extension\AbstractErgonodeMappingExtension;

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