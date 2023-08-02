<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\QueryBuilder;

use GraphQL\Query;

class CategoryQueryBuilder
{
    public const DEFAULT_TREE_COUNT = 50;

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
