<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Product\Transformer;

use Strix\Ergonode\Modules\Product\Struct\ErgonodeProduct;
use Strix\Ergonode\Transformer\NodeTransformerInterface;

class ProductNodeTransformer implements NodeTransformerInterface
{
    public function supports(string $className): bool
    {
        return $className === ErgonodeProduct::class;
    }

    public function transformNode(array $node): ErgonodeProduct
    {
        $entity = new ErgonodeProduct($node['sku']);

        $entity->setCreatedAt($node['createdAt']);
        $entity->setEditedAt($node['editedAt']);
        $entity->setTypename($node['__typename']);
        $entity->setTemplateName($node['template']['name']);

        foreach ($node['categoryList']['edges'] as $category) {
            $entity->addCategoryCode($category['node']['code']);
        }

        foreach ($node['attributeList']['edges'] as $attribute) {
            $entity->addAttributeCode($attribute['node']['attribute']['code']);
        }

        return $entity;
    }
}