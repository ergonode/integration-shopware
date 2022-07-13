<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\Struct\ErgonodeCategory;
use Ergonode\IntegrationShopware\Struct\ErgonodeCategoryCollection;

class CategoryResponseTransformer implements ResponseTransformerInterface
{
    public function transformResponse(array $response): ErgonodeCategoryCollection
    {
        $categories = new ErgonodeCategoryCollection();

        $categoriesRaw = $response['categoryTreeLeafList']['edges'] ?? [];
        if (empty($categoriesRaw)) {
            return $categories;
        }

        $entrypoint = new ErgonodeCategory($response['code']);
        foreach ($response['name'] as $name) {
            $entrypoint->setNameTranslation($name['language'], $name['value']);
        }

        $categories->set($entrypoint->getCode(), $entrypoint);

        foreach ($categoriesRaw as $categoryRaw) {
            $parentCategoryRaw = $categoryRaw['node']['parentCategory'];
            $categoryRaw = $categoryRaw['node']['category'];

            $category = new ErgonodeCategory($categoryRaw['code']);

            foreach ($categoryRaw['name'] as $name) {
                $category->setNameTranslation($name['language'], $name['value']);
            }

            if (isset($parentCategoryRaw['code'])) {
                $category->setParentCategory(
                    $categories->get($parentCategoryRaw['code']) ?? null
                );
            } else {
                $category->setParentCategory($entrypoint);
            }

            $categories->set($category->getCode(), $category);
        }

        return $categories;
    }
}