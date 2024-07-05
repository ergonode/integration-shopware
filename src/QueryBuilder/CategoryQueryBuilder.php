<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\QueryBuilder;

use Ergonode\IntegrationShopware\QueryBuilder\Common\AttributesQuery;
use GraphQL\Query;

class CategoryQueryBuilder
{
    public const DEFAULT_TREE_COUNT = 50;
    public const DEFAULT_CATEGORY_ATTRIBUTE_COUNT = 200;

    public function buildTree(string $treeCode, int $count, ?string $cursor): Query
    {
        $listArguments = ['first' => $count];
        if ($cursor !== null) {
            $listArguments['after'] = $cursor;
        }

        return (new Query('categoryTree'))
            ->setSelectionSet([
                'code',
                (new Query('name'))
                    ->setSelectionSet([
                        'value',
                        'language',
                    ]),
                (new Query('categoryTreeLeafList'))
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
                                        (new Query('category'))
                                            ->setSelectionSet([
                                                'code',
                                                (new Query('name'))
                                                    ->setSelectionSet([
                                                        'value',
                                                        'language',
                                                    ]),
                                            ]),
                                        (new Query('parentCategory'))
                                            ->setSelectionSet([
                                                'code',
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->setArguments($listArguments),
            ])
            ->setArguments([
                'code' => $treeCode,
            ]);
    }

    public function build(int $count, ?string $cursor): Query
    {
        $listArguments = ['first' => $count];
        if ($cursor !== null) {
            $listArguments['after'] = $cursor;
        }

        return (new Query('categoryStream'))
            ->setArguments($listArguments)
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
                                'code',
                                (new Query('name'))
                                    ->setSelectionSet([
                                        'value',
                                        'language',
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public function buildWithCategoryAttributes(int $count, ?string $cursor): Query
    {
        $listArguments = ['first' => $count];
        if ($cursor !== null) {
            $listArguments['after'] = $cursor;
        }

        $attributesArguments = ['first' => self::DEFAULT_CATEGORY_ATTRIBUTE_COUNT];

        return (new Query('categoryStream'))
            ->setArguments($listArguments)
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
                                'code',
                                (new Query('name'))
                                    ->setSelectionSet([
                                        'value',
                                        'language',
                                    ]),
                                $this->queryAttributeList($attributesArguments),
                            ]),
                    ]),
            ]);
    }

    public function buildTreeStream(
        int $categoryLeafCount,
        ?string $categoryLeafCursor = null
    ): Query {
        $treeArguments = ['first' => self::DEFAULT_TREE_COUNT];

        $categoryLeafArguments = ['first' => $categoryLeafCount];
        if ($categoryLeafCursor !== null) {
            $categoryLeafArguments['after'] = $categoryLeafCursor;
        }

        return (new Query('categoryTreeStream'))
            ->setArguments($treeArguments)
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
                                'code',
                                (new Query('categoryTreeLeafList'))
                                    ->setArguments($categoryLeafArguments)
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
                                                        (new Query('category'))
                                                            ->setSelectionSet([
                                                                'code',
                                                                (new Query('name'))
                                                                    ->setSelectionSet([
                                                                        'value',
                                                                        'language',
                                                                    ]),
                                                            ]),
                                                        (new Query('parentCategory'))
                                                            ->setSelectionSet([
                                                                'code',
                                                            ]),
                                                    ]),
                                            ]),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    private function queryAttributeList(array $attributesArguments)
    {
        return (new Query('attributeList'))
            ->setArguments($attributesArguments)
            ->setSelectionSet([
                (new Query('edges'))
                    ->setSelectionSet([
                        (new Query('node'))
                            ->setSelectionSet([
                                AttributesQuery::getAttributeFragment(),
                                AttributesQuery::attributesTranslations(),
                            ]),
                    ]),
            ]);
    }

    public function buildTreeStreamWithOnlyCodes(
        int $treeCount,
        ?string $treeCursor = null
    ): Query {
        $treeArguments = ['first' => $treeCount];
        if ($treeCursor !== null) {
            $treeArguments['after'] = $treeCursor;
        }

        return (new Query('categoryTreeStream'))
            ->setArguments($treeArguments)
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
                                'code',
                            ]),
                    ]),
            ]);
    }
}
