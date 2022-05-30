<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Fixtures;

class GqlCategoryResponse
{
    public static function emptyCategoryTreeResponse(): array
    {
        return [
            'categoryTree' => [
                'code' => 'empty_tree',
                'name' => [
                    0 => [
                        'value' => NULL,
                        'language' => 'pl_PL',
                    ],
                    1 => [
                        'value' => NULL,
                        'language' => 'cs_CZ',
                    ],
                    2 => [
                        'value' => NULL,
                        'language' => 'da_DK',
                    ],
                    3 => [
                        'value' => NULL,
                        'language' => 'en_US',
                    ],
                    4 => [
                        'value' => NULL,
                        'language' => 'fi_FI',
                    ],
                    5 => [
                        'value' => NULL,
                        'language' => 'hr_HR',
                    ],
                    6 => [
                        'value' => NULL,
                        'language' => 'nl_NL',
                    ],
                ],
                'categoryTreeLeafList' => [
                    'pageInfo' => [
                        'endCursor' => NULL,
                        'hasNextPage' => false,
                    ],
                    'edges' => [],
                ],
            ],
        ];
    }

    public static function fullCategoryTreeResponse(): array
    {
        return [
            'data' => [
                'categoryTree' => [
                    'code' => 'default_tree',
                    'name' => [
                        0 => [
                            'value' => NULL,
                            'language' => 'pl_PL',
                        ],
                        1 => [
                            'value' => NULL,
                            'language' => 'cs_CZ',
                        ],
                        2 => [
                            'value' => NULL,
                            'language' => 'da_DK',
                        ],
                        3 => [
                            'value' => NULL,
                            'language' => 'en_US',
                        ],
                        4 => [
                            'value' => NULL,
                            'language' => 'fi_FI',
                        ],
                        5 => [
                            'value' => NULL,
                            'language' => 'hr_HR',
                        ],
                        6 => [
                            'value' => NULL,
                            'language' => 'nl_NL',
                        ],
                    ],
                    'categoryTreeLeafList' => [
                        'pageInfo' => [
                            'endCursor' => 'YXJyYXljb25uZWN0aW9uOjQ=',
                            'hasNextPage' => false,
                        ],
                        'edges' => [
                            0 => [
                                'node' => [
                                    'category' => [
                                        'code' => 'test',
                                        'name' => [
                                            0 => [
                                                'value' => NULL,
                                                'language' => 'pl_PL',
                                            ],
                                            1 => [
                                                'value' => NULL,
                                                'language' => 'cs_CZ',
                                            ],
                                            2 => [
                                                'value' => NULL,
                                                'language' => 'da_DK',
                                            ],
                                            3 => [
                                                'value' => NULL,
                                                'language' => 'en_US',
                                            ],
                                            4 => [
                                                'value' => NULL,
                                                'language' => 'fi_FI',
                                            ],
                                            5 => [
                                                'value' => NULL,
                                                'language' => 'hr_HR',
                                            ],
                                            6 => [
                                                'value' => NULL,
                                                'language' => 'nl_NL',
                                            ],
                                        ],
                                    ],
                                    'parentCategory' => NULL,
                                ],
                            ],
                            1 => [
                                'node' => [
                                    'category' => [
                                        'code' => 'level1',
                                        'name' => [
                                            0 => [
                                                'value' => NULL,
                                                'language' => 'pl_PL',
                                            ],
                                            1 => [
                                                'value' => NULL,
                                                'language' => 'cs_CZ',
                                            ],
                                            2 => [
                                                'value' => NULL,
                                                'language' => 'da_DK',
                                            ],
                                            3 => [
                                                'value' => NULL,
                                                'language' => 'en_US',
                                            ],
                                            4 => [
                                                'value' => NULL,
                                                'language' => 'fi_FI',
                                            ],
                                            5 => [
                                                'value' => NULL,
                                                'language' => 'hr_HR',
                                            ],
                                            6 => [
                                                'value' => NULL,
                                                'language' => 'nl_NL',
                                            ],
                                        ],
                                    ],
                                    'parentCategory' => NULL,
                                ],
                            ],
                            2 => [
                                'node' => [
                                    'category' => [
                                        'code' => 'level1_1',
                                        'name' => [
                                            0 => [
                                                'value' => NULL,
                                                'language' => 'pl_PL',
                                            ],
                                            1 => [
                                                'value' => NULL,
                                                'language' => 'cs_CZ',
                                            ],
                                            2 => [
                                                'value' => NULL,
                                                'language' => 'da_DK',
                                            ],
                                            3 => [
                                                'value' => NULL,
                                                'language' => 'en_US',
                                            ],
                                            4 => [
                                                'value' => NULL,
                                                'language' => 'fi_FI',
                                            ],
                                            5 => [
                                                'value' => NULL,
                                                'language' => 'hr_HR',
                                            ],
                                            6 => [
                                                'value' => NULL,
                                                'language' => 'nl_NL',
                                            ],
                                        ],
                                    ],
                                    'parentCategory' => [
                                        'code' => 'level1',
                                    ],
                                ],
                            ],
                            3 => [
                                'node' => [
                                    'category' => [
                                        'code' => 'level1_1_1',
                                        'name' => [
                                            0 => [
                                                'value' => NULL,
                                                'language' => 'pl_PL',
                                            ],
                                            1 => [
                                                'value' => NULL,
                                                'language' => 'cs_CZ',
                                            ],
                                            2 => [
                                                'value' => NULL,
                                                'language' => 'da_DK',
                                            ],
                                            3 => [
                                                'value' => NULL,
                                                'language' => 'en_US',
                                            ],
                                            4 => [
                                                'value' => NULL,
                                                'language' => 'fi_FI',
                                            ],
                                            5 => [
                                                'value' => NULL,
                                                'language' => 'hr_HR',
                                            ],
                                            6 => [
                                                'value' => NULL,
                                                'language' => 'nl_NL',
                                            ],
                                        ],
                                    ],
                                    'parentCategory' => [
                                        'code' => 'level1_1',
                                    ],
                                ],
                            ],
                            4 => [
                                'node' => [
                                    'category' => [
                                        'code' => 'level1_2',
                                        'name' => [
                                            0 => [
                                                'value' => NULL,
                                                'language' => 'pl_PL',
                                            ],
                                            1 => [
                                                'value' => NULL,
                                                'language' => 'cs_CZ',
                                            ],
                                            2 => [
                                                'value' => NULL,
                                                'language' => 'da_DK',
                                            ],
                                            3 => [
                                                'value' => NULL,
                                                'language' => 'en_US',
                                            ],
                                            4 => [
                                                'value' => NULL,
                                                'language' => 'fi_FI',
                                            ],
                                            5 => [
                                                'value' => NULL,
                                                'language' => 'hr_HR',
                                            ],
                                            6 => [
                                                'value' => NULL,
                                                'language' => 'nl_NL',
                                            ],
                                        ],
                                    ],
                                    'parentCategory' => [
                                        'code' => 'level1',
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