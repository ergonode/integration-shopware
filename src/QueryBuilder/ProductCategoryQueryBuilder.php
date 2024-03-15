<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\QueryBuilder;

use GraphQL\Query;

class ProductCategoryQueryBuilder
{
    private const CATEGORY_LIST_COUNT = 50;

    public function build(string $sku, ?string $cursor = null): Query
    {
        $arguments = [
            'sku' => $sku,
        ];

        $categoryListArguments = ['first' => self::CATEGORY_LIST_COUNT];
        if (null !== $cursor) {
            $categoryListArguments['after'] = $cursor;
        }

        return (new Query('product'))
            ->setArguments($arguments)
            ->setSelectionSet([
                'sku',
                (new Query('categoryList'))
                    ->setArguments($categoryListArguments)
                    ->setSelectionSet([
                        'totalCount',
                        (new Query('pageInfo'))
                            ->setSelectionSet([
                                'endCursor',
                                'hasNextPage',
                            ]),
                        (new Query('edges'))
                            ->setSelectionSet([
                                (new Query('node'))
                                    ->setSelectionSet([
                                        'code',
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
