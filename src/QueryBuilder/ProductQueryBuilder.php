<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\QueryBuilder;

use Ergonode\IntegrationShopware\QueryBuilder\Common\AttributesQuery;
use GraphQL\InlineFragment;
use GraphQL\Query;

class ProductQueryBuilder
{
    private const ATTRIBUTE_LIST_COUNT = 1000;
    private const VARIANT_LIST_COUNT = 25;
    private const CATEGORY_LIST_COUNT = 50;

    public function build(int $count, ?string $cursor = null): Query
    {
        $arguments = [
            'first' => $count,
        ];

        if ($cursor !== null) {
            $arguments['after'] = $cursor;
        }

        return (new Query('productStream'))
            ->setArguments($arguments)
            ->setSelectionSet([
                'totalCount',
                (new Query('pageInfo'))
                    ->setSelectionSet([
                        'endCursor',
                        'hasNextPage',
                    ]),
                (new Query('edges'))
                    ->setSelectionSet([
                        'cursor',
                        (new Query('node'))
                            ->setSelectionSet([
                                'sku',
                                'createdAt',
                                'editedAt',
                                '__typename',
                                (new InlineFragment('VariableProduct'))
                                    ->setSelectionSet([
                                        (new Query('bindings'))
                                            ->setSelectionSet([
                                                'code',
                                            ]),
                                        (new Query('variantList'))
                                            ->setArguments(['first' => self::VARIANT_LIST_COUNT])
                                            ->setSelectionSet([
                                                (new Query('pageInfo'))
                                                    ->setSelectionSet([
                                                        'endCursor',
                                                        'hasNextPage',
                                                    ]),
                                                (new Query('edges'))
                                                    ->setSelectionSet([
                                                        (new Query('node'))
                                                            ->setSelectionSet([
                                                                'sku',
                                                                '__typename',
                                                                (new Query('attributeList'))
                                                                    ->setArguments(['first' => self::ATTRIBUTE_LIST_COUNT])
                                                                    ->setSelectionSet([
                                                                        (new Query('edges'))
                                                                            ->setSelectionSet([
                                                                                (new Query('node'))
                                                                                    ->setSelectionSet([
                                                                                        AttributesQuery::getAttributeFragment(),
                                                                                        AttributesQuery::attributesTranslations(),
                                                                                    ]),
                                                                            ]),
                                                                    ]),
                                                            ]),
                                                    ]),
                                                'totalCount',
                                            ]),
                                    ]),
                                (new Query('categoryList'))
                                    ->setArguments(['first' => self::CATEGORY_LIST_COUNT])
                                    ->setSelectionSet([
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
                                (new Query('attributeList'))
                                    ->setArguments(['first' => self::ATTRIBUTE_LIST_COUNT])
                                    ->setSelectionSet([
                                        (new Query('edges'))
                                            ->setSelectionSet([
                                                (new Query('node'))
                                                    ->setSelectionSet([
                                                        AttributesQuery::getAttributeFragment(),
                                                        AttributesQuery::attributesTranslations(),
                                                    ]),
                                            ]),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public function buildDeleted(?int $count = null, ?string $cursor = null): Query
    {
        $arguments = [];

        if ($count !== null) {
            $arguments['first'] = $count;
        }

        if ($cursor !== null) {
            $arguments['after'] = $cursor;
        }

        return (new Query('productDeletedStream'))
            ->setArguments($arguments)
            ->setSelectionSet([
                'totalCount',
                (new Query('pageInfo'))
                    ->setSelectionSet([
                        'endCursor',
                        'hasNextPage',
                    ]),
                (new Query('edges'))
                    ->setSelectionSet([
                        'cursor',
                        'node',
                    ]),
            ]);
    }

    public function buildProductWithVariants(
        string $sku,
        ?string $variantsCursor = null,
        ?string $categoryCursor = null,
    ): Query {
        $variantListArguments = ['first' => self::VARIANT_LIST_COUNT];
        if (null !== $variantsCursor) {
            $variantListArguments['after'] = $variantsCursor;
        }

        $categoryListArguments = ['first' => self::CATEGORY_LIST_COUNT];
        if (null !== $categoryCursor) {
            $categoryListArguments['after'] = $categoryCursor;
        }

        return (new Query('product'))
            ->setArguments(['sku' => $sku])
            ->setSelectionSet([
                'sku',
                'createdAt',
                'editedAt',
                '__typename',
                (new InlineFragment('VariableProduct'))
                    ->setSelectionSet([
                        (new Query('bindings'))
                            ->setSelectionSet([
                                'code',
                            ]),
                        (new Query('variantList'))
                            ->setArguments($variantListArguments)
                            ->setSelectionSet([
                                (new Query('pageInfo'))
                                    ->setSelectionSet([
                                        'endCursor',
                                        'hasNextPage',
                                    ]),
                                (new Query('edges'))
                                    ->setSelectionSet([
                                        (new Query('node'))
                                            ->setSelectionSet([
                                                'sku',
                                                '__typename',
                                                (new Query('attributeList'))
                                                    ->setArguments(['first' => self::ATTRIBUTE_LIST_COUNT])
                                                    ->setSelectionSet([
                                                        (new Query('edges'))
                                                            ->setSelectionSet([
                                                                (new Query('node'))
                                                                    ->setSelectionSet([
                                                                        AttributesQuery::getAttributeFragment(),
                                                                        AttributesQuery::attributesTranslations(),
                                                                    ]),
                                                            ]),
                                                        'totalCount',
                                                    ]),
                                            ]),
                                    ]),
                            ]),
                    ]),
                (new Query('categoryList'))
                    ->setArguments($categoryListArguments)
                    ->setSelectionSet([
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
                (new Query('attributeList'))
                    ->setArguments(['first' => self::ATTRIBUTE_LIST_COUNT])
                    ->setSelectionSet([
                        (new Query('edges'))
                            ->setSelectionSet([
                                (new Query('node'))
                                    ->setSelectionSet([
                                        AttributesQuery::getAttributeFragment(),
                                        AttributesQuery::attributesTranslations(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public function buildOnlySkus(int $count, ?string $cursor): Query
    {
        $arguments = [
            'first' => $count,
        ];

        if ($cursor !== null) {
            $arguments['after'] = $cursor;
        }

        return (new Query('productStream'))
            ->setArguments($arguments)
            ->setSelectionSet([
                'totalCount',
                (new Query('pageInfo'))
                    ->setSelectionSet([
                        'endCursor',
                        'hasNextPage',
                    ]),
                (new Query('edges'))
                    ->setSelectionSet([
                        'cursor',
                        (new Query('node'))
                            ->setSelectionSet([
                                'sku',
                                '__typename',
                            ]),
                    ]),
            ]);
    }

    public function buildVariantSkusForProduct(string $sku): Query
    {
        return (new Query('product'))
            ->setArguments(['sku' => $sku])
            ->setSelectionSet([
                (new InlineFragment('VariableProduct'))
                    ->setSelectionSet([
                        (new Query('variantList'))
                            ->setArguments(['first' => 10000]) // allow unlimited
                            ->setSelectionSet([
                                (new Query('edges'))
                                    ->setSelectionSet([
                                        (new Query('node'))
                                            ->setSelectionSet([
                                                'sku',
                                            ]),
                                    ]),
                                'totalCount',
                            ]),
                    ]),
            ]);
    }
}
