<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Shopware\Core\Framework\Context;

interface MappableFieldsProviderInterface
{
    public function getShopwareAttributes(): array;

    public function getShopwareAttributesWithTypes(): array;

    public function getShopwareCustomFields(Context $context): array;

    public function getShopwareCustomFieldsWithTypes(Context $context): array;

    public function getErgonodeAttributes(array $types = []): array;

    public function getErgonodeAttributesWithTypes(array $types = []): array;

    public function getErgonodeCategoryTreeCodes(): array;

    public function getShopwareCategories(Context $context): array;

    public function getErgonodeCategories(): array;

    public function getShopwareCategoriesAttributesWithTypes(): array;

    public function getErgonodeCategoryAttributesWithTypes(array $types = []): array;

    public function getTemplates(): array;
}
