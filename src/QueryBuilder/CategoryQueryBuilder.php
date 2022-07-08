<?php

declare(strict_types=1);

namespace Strix\Ergonode\QueryBuilder;

use GraphQL\Query;

class CategoryQueryBuilder
{
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

    public function build(string $treeCode, int $count, ?string $cursor): Query
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
                            ])
                    ])
            ]);
    }

    public function buildTreeStream(int $count, ?string $cursor): Query
    {
        $listArguments = ['first' => $count];
        if ($cursor !== null) {
            $listArguments['after'] = $cursor;
        }
        return (new Query('categoryTreeStream'))
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
                                                            ]),
                                                        (new Query('parentCategory'))
                                                            ->setSelectionSet([
                                                                'code',
                                                            ]),
                                                    ]),
                                            ]),
                                    ])
                            ])
                    ])
            ]);
    }
}
