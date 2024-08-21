<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests\PHPUnit\Fixture;

class GqlProductResponse
{
    public static function deletedProductsResponse(): array
    {
        return [
            'data' => [
                'productDeletedStream' => [
                    'totalCount' => 666,
                    'pageInfo' => [
                        'endCursor' => 'YXJyYXljb25uZWN0aW9uOjEzNQ==',
                        'hasNextPage' => true,
                    ],
                    'edges' => [
                        0 => [
                            'cursor' => 'YXJyYXljb25uZWN0aW9uOjEzMQ==',
                            'node' => 'skirt_001',
                        ],
                        1 => [
                            'cursor' => 'YXJyYXljb25uZWN0aW9uOjEzMg==',
                            'node' => 'test_group',
                        ],
                        2 => [
                            'cursor' => 'YXJyYXljb25uZWN0aW9uOjEzMw==',
                            'node' => 'jacket_001',
                        ],
                        3 => [
                            'cursor' => 'YXJyYXljb25uZWN0aW9uOjEzNA==',
                            'node' => 'jacket_001_xl',
                        ],
                        4 => [
                            'cursor' => 'YXJyYXljb25uZWN0aW9uOjEzNQ==',
                            'node' => 'jacket_001_l',
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function productStreamResponse(): array
    {
        return [
            'data' => [
                'productStream' => [
                    'totalCount' => 7,
                    'pageInfo' => [
                        'endCursor' => 'YXJyYXljb25uZWN0aW9uOjI5NDEyNw==',
                        'hasNextPage' => true,
                    ],
                    'edges' => [
                        0 => [
                            'cursor' => 'YXJyYXljb25uZWN0aW9uOjI5NDExNA==',
                            'node' => [
                                'sku' => 'some_random_sku_1',
                                'createdAt' => '2022-05-19T09:15:51+00:00',
                                'editedAt' => '2022-05-20T08:18:58+00:00',
                                '__typename' => 'SimpleProduct',
                                'template' => [
                                    'name' => 'TEST',
                                ],
                                'categoryList' => [
                                    'edges' => [],
                                ],
                                'attributeList' => [
                                    'edges' => [],
                                ],
                            ],
                        ],
                        1 => [
                            'cursor' => 'YXJyYXljb25uZWN0aW9uOjI5NDExOA==',
                            'node' => [
                                'sku' => 'sku001',
                                'createdAt' => '2022-05-20T08:20:08+00:00',
                                'editedAt' => '2022-05-20T08:20:30+00:00',
                                '__typename' => 'SimpleProduct',
                                'template' => [
                                    'name' => 'templatka1',
                                ],
                                'categoryList' => [
                                    'edges' => [],
                                ],
                                'attributeList' => [
                                    'edges' => [
                                        0 => [
                                            'node' => [
                                                'attribute' => [
                                                    'code' => 'name',
                                                ],
                                            ],
                                        ],
                                        1 => [
                                            'node' => [
                                                'attribute' => [
                                                    'code' => 'description',
                                                ],
                                            ],
                                        ],
                                        2 => [
                                            'node' => [
                                                'attribute' => [
                                                    'code' => 'price',
                                                ],
                                            ],
                                        ],
                                        3 => [
                                            'node' => [
                                                'attribute' => [
                                                    'code' => 'stock',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        2 => [
                            'cursor' => 'YXJyYXljb25uZWN0aW9uOjI5NDEyMA==',
                            'node' => [
                                'sku' => '3',
                                'createdAt' => '2022-05-20T10:47:47+00:00',
                                'editedAt' => '2022-05-20T10:47:47+00:00',
                                '__typename' => 'SimpleProduct',
                                'template' => [
                                    'name' => 'templatka1',
                                ],
                                'categoryList' => [
                                    'edges' => [],
                                ],
                                'attributeList' => [
                                    'edges' => [],
                                ],
                            ],
                        ],
                        3 => [
                            'cursor' => 'YXJyYXljb25uZWN0aW9uOjI5NDEyNw==',
                            'node' => [
                                'sku' => 'some_random_sku_4',
                                'createdAt' => '2022-05-23T09:46:02+00:00',
                                'editedAt' => '2022-05-23T09:47:06+00:00',
                                '__typename' => 'SimpleProduct',
                                'template' => [
                                    'name' => 'Test Warsztat',
                                ],
                                'categoryList' => [
                                    'edges' => [
                                        0 => [
                                            'node' => [
                                                'code' => 'cat1',
                                            ],
                                        ],
                                        1 => [
                                            'node' => [
                                                'code' => 'cat2',
                                            ],
                                        ],
                                        2 => [
                                            'node' => [
                                                'code' => 'cat3',
                                            ],
                                        ],
                                    ],
                                ],
                                'attributeList' => [
                                    'edges' => [
                                        0 => [
                                            'node' => [
                                                'attribute' => [
                                                    'code' => 'name',
                                                ],
                                            ],
                                        ],
                                        1 => [
                                            'node' => [
                                                'attribute' => [
                                                    'code' => 'description',
                                                ],
                                            ],
                                        ],
                                        2 => [
                                            'node' => [
                                                'attribute' => [
                                                    'code' => 'js_test',
                                                ],
                                            ],
                                        ],
                                        3 => [
                                            'node' => [
                                                'attribute' => [
                                                    'code' => 'obrazek',
                                                ],
                                            ],
                                        ],
                                        4 => [
                                            'node' => [
                                                'attribute' => [
                                                    'code' => 'stock',
                                                ],
                                            ],
                                        ],
                                        5 => [
                                            'node' => [
                                                'attribute' => [
                                                    'code' => 'price',
                                                ],
                                            ],
                                        ],
                                        6 => [
                                            'node' => [
                                                'attribute' => [
                                                    'code' => 'test',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}