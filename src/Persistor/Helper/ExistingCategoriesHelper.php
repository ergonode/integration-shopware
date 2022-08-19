<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Persistor\Helper;

use Ergonode\IntegrationShopware\Entity\ErgonodeCategoryMappingExtension\ErgonodeCategoryMappingExtensionEntity;
use Ergonode\IntegrationShopware\Extension\ErgonodeCategoryMappingExtension;
use Ergonode\IntegrationShopware\Provider\CategoryProvider;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class ExistingCategoriesHelper
{
    private array $existingCategories = [];

    private CategoryProvider $categoryProvider;

    public function __construct(CategoryProvider $categoryProvider)
    {
        $this->categoryProvider = $categoryProvider;
    }

    private function getEntityExtensionCode(Entity $entity): ?string
    {
        $extension = $entity->getExtension(ErgonodeCategoryMappingExtension::EXTENSION_NAME);
        if ($extension instanceof ErgonodeCategoryMappingExtensionEntity) {
            return $extension->getCode();
        }

        return null;
    }

    public function load(array $codes, Context $context): void
    {
        $this->reset();
        $entities = $this->categoryProvider->getCategoriesWithAnyCode($codes, $context, [
            ErgonodeCategoryMappingExtension::EXTENSION_NAME
        ]);

        foreach ($entities as $entity) {
            $this->existingCategories[$this->getEntityExtensionCode($entity)] = $entity->getId();
        }
    }

    public function get(string $code): ?string
    {
        return $this->existingCategories[$code] ?? null;
    }

    public function set(string $code, string $id): void
    {
        $this->existingCategories[$code] = $id;
    }

    public function has(string $code): bool
    {
        return \array_key_exists($code, $this->existingCategories);
    }

    public function reset(): void
    {
        $this->existingCategories = [];
    }
}