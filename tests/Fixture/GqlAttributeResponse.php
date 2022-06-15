<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Fixture;

class GqlAttributeResponse
{
    public static function attributeStreamResponse(): array
    {
        return [
            'data' => [
                'attributeStream' => [
                    'totalCount' => 6,
                    'pageInfo' => [
                        'endCursor' => 'YXJyYXljb25uZWN0aW9uOjI5NDE0MA==',
                        'hasNextPage' => false,
                    ],
                    'edges' => [
                        0 => [
                            'cursor' => 'YXJyYXljb25uZWN0aW9uOjI5NDEwNQ==',
                            'node' => [
                                'code' => 'stock',
                                'scope' => 'GLOBAL',
                                'label' => [
                                    0 => [
                                        'language' => 'pl_PL',
                                        'value' => NULL,
                                    ],
                                    1 => [
                                        'language' => 'cs_CZ',
                                        'value' => NULL,
                                    ],
                                    2 => [
                                        'language' => 'da_DK',
                                        'value' => NULL,
                                    ],
                                    3 => [
                                        'language' => 'en_US',
                                        'value' => NULL,
                                    ],
                                    4 => [
                                        'language' => 'fi_FI',
                                        'value' => NULL,
                                    ],
                                    5 => [
                                        'language' => 'hr_HR',
                                        'value' => NULL,
                                    ],
                                    6 => [
                                        'language' => 'nl_NL',
                                        'value' => NULL,
                                    ],
                                ],
                                'hint' => [
                                    0 => [
                                        'language' => 'pl_PL',
                                        'value' => NULL,
                                    ],
                                    1 => [
                                        'language' => 'cs_CZ',
                                        'value' => NULL,
                                    ],
                                    2 => [
                                        'language' => 'da_DK',
                                        'value' => NULL,
                                    ],
                                    3 => [
                                        'language' => 'en_US',
                                        'value' => NULL,
                                    ],
                                    4 => [
                                        'language' => 'fi_FI',
                                        'value' => NULL,
                                    ],
                                    5 => [
                                        'language' => 'hr_HR',
                                        'value' => NULL,
                                    ],
                                    6 => [
                                        'language' => 'nl_NL',
                                        'value' => NULL,
                                    ],
                                ],
                                'placeholder' => [
                                    0 => [
                                        'language' => 'pl_PL',
                                        'value' => NULL,
                                    ],
                                    1 => [
                                        'language' => 'cs_CZ',
                                        'value' => NULL,
                                    ],
                                    2 => [
                                        'language' => 'da_DK',
                                        'value' => NULL,
                                    ],
                                    3 => [
                                        'language' => 'en_US',
                                        'value' => NULL,
                                    ],
                                    4 => [
                                        'language' => 'fi_FI',
                                        'value' => NULL,
                                    ],
                                    5 => [
                                        'language' => 'hr_HR',
                                        'value' => NULL,
                                    ],
                                    6 => [
                                        'language' => 'nl_NL',
                                        'value' => NULL,
                                    ],
                                ],
                                'type_numeric' => 'stock',
                            ],
                        ],
                        1 => [
                            'cursor' => 'YXJyYXljb25uZWN0aW9uOjI5NDEwNg==',
                            'node' => [
                                'code' => 'name',
                                'scope' => 'LOCAL',
                                'label' => [
                                    0 => [
                                        'language' => 'pl_PL',
                                        'value' => NULL,
                                    ],
                                    1 => [
                                        'language' => 'cs_CZ',
                                        'value' => NULL,
                                    ],
                                    2 => [
                                        'language' => 'da_DK',
                                        'value' => NULL,
                                    ],
                                    3 => [
                                        'language' => 'en_US',
                                        'value' => NULL,
                                    ],
                                    4 => [
                                        'language' => 'fi_FI',
                                        'value' => NULL,
                                    ],
                                    5 => [
                                        'language' => 'hr_HR',
                                        'value' => NULL,
                                    ],
                                    6 => [
                                        'language' => 'nl_NL',
                                        'value' => NULL,
                                    ],
                                ],
                                'hint' => [
                                    0 => [
                                        'language' => 'pl_PL',
                                        'value' => NULL,
                                    ],
                                    1 => [
                                        'language' => 'cs_CZ',
                                        'value' => NULL,
                                    ],
                                    2 => [
                                        'language' => 'da_DK',
                                        'value' => NULL,
                                    ],
                                    3 => [
                                        'language' => 'en_US',
                                        'value' => NULL,
                                    ],
                                    4 => [
                                        'language' => 'fi_FI',
                                        'value' => NULL,
                                    ],
                                    5 => [
                                        'language' => 'hr_HR',
                                        'value' => NULL,
                                    ],
                                    6 => [
                                        'language' => 'nl_NL',
                                        'value' => NULL,
                                    ],
                                ],
                                'placeholder' => [
                                    0 => [
                                        'language' => 'pl_PL',
                                        'value' => NULL,
                                    ],
                                    1 => [
                                        'language' => 'cs_CZ',
                                        'value' => NULL,
                                    ],
                                    2 => [
                                        'language' => 'da_DK',
                                        'value' => NULL,
                                    ],
                                    3 => [
                                        'language' => 'en_US',
                                        'value' => NULL,
                                    ],
                                    4 => [
                                        'language' => 'fi_FI',
                                        'value' => NULL,
                                    ],
                                    5 => [
                                        'language' => 'hr_HR',
                                        'value' => NULL,
                                    ],
                                    6 => [
                                        'language' => 'nl_NL',
                                        'value' => NULL,
                                    ],
                                ],
                                'type_text' => 'name',
                            ],
                        ],
                        2 => [
                            'cursor' => 'YXJyYXljb25uZWN0aW9uOjI5NDEwNw==',
                            'node' => [
                                'code' => 'description',
                                'scope' => 'LOCAL',
                                'label' => [
                                    0 => [
                                        'language' => 'pl_PL',
                                        'value' => NULL,
                                    ],
                                    1 => [
                                        'language' => 'cs_CZ',
                                        'value' => NULL,
                                    ],
                                    2 => [
                                        'language' => 'da_DK',
                                        'value' => NULL,
                                    ],
                                    3 => [
                                        'language' => 'en_US',
                                        'value' => NULL,
                                    ],
                                    4 => [
                                        'language' => 'fi_FI',
                                        'value' => NULL,
                                    ],
                                    5 => [
                                        'language' => 'hr_HR',
                                        'value' => NULL,
                                    ],
                                    6 => [
                                        'language' => 'nl_NL',
                                        'value' => NULL,
                                    ],
                                ],
                                'hint' => [
                                    0 => [
                                        'language' => 'pl_PL',
                                        'value' => NULL,
                                    ],
                                    1 => [
                                        'language' => 'cs_CZ',
                                        'value' => NULL,
                                    ],
                                    2 => [
                                        'language' => 'da_DK',
                                        'value' => NULL,
                                    ],
                                    3 => [
                                        'language' => 'en_US',
                                        'value' => NULL,
                                    ],
                                    4 => [
                                        'language' => 'fi_FI',
                                        'value' => NULL,
                                    ],
                                    5 => [
                                        'language' => 'hr_HR',
                                        'value' => NULL,
                                    ],
                                    6 => [
                                        'language' => 'nl_NL',
                                        'value' => NULL,
                                    ],
                                ],
                                'placeholder' => [
                                    0 => [
                                        'language' => 'pl_PL',
                                        'value' => NULL,
                                    ],
                                    1 => [
                                        'language' => 'cs_CZ',
                                        'value' => NULL,
                                    ],
                                    2 => [
                                        'language' => 'da_DK',
                                        'value' => NULL,
                                    ],
                                    3 => [
                                        'language' => 'en_US',
                                        'value' => NULL,
                                    ],
                                    4 => [
                                        'language' => 'fi_FI',
                                        'value' => NULL,
                                    ],
                                    5 => [
                                        'language' => 'hr_HR',
                                        'value' => NULL,
                                    ],
                                    6 => [
                                        'language' => 'nl_NL',
                                        'value' => NULL,
                                    ],
                                ],
                                'type_text' => 'description',
                            ],
                        ],
                        3 => [
                            'cursor' => 'YXJyYXljb25uZWN0aW9uOjI5NDExOQ==',
                            'node' => [
                                'code' => 'price',
                                'scope' => 'GLOBAL',
                                'label' => [
                                    0 => [
                                        'language' => 'pl_PL',
                                        'value' => NULL,
                                    ],
                                    1 => [
                                        'language' => 'cs_CZ',
                                        'value' => NULL,
                                    ],
                                    2 => [
                                        'language' => 'da_DK',
                                        'value' => NULL,
                                    ],
                                    3 => [
                                        'language' => 'en_US',
                                        'value' => NULL,
                                    ],
                                    4 => [
                                        'language' => 'fi_FI',
                                        'value' => NULL,
                                    ],
                                    5 => [
                                        'language' => 'hr_HR',
                                        'value' => NULL,
                                    ],
                                    6 => [
                                        'language' => 'nl_NL',
                                        'value' => NULL,
                                    ],
                                ],
                                'hint' => [
                                    0 => [
                                        'language' => 'pl_PL',
                                        'value' => NULL,
                                    ],
                                    1 => [
                                        'language' => 'cs_CZ',
                                        'value' => NULL,
                                    ],
                                    2 => [
                                        'language' => 'da_DK',
                                        'value' => NULL,
                                    ],
                                    3 => [
                                        'language' => 'en_US',
                                        'value' => NULL,
                                    ],
                                    4 => [
                                        'language' => 'fi_FI',
                                        'value' => NULL,
                                    ],
                                    5 => [
                                        'language' => 'hr_HR',
                                        'value' => NULL,
                                    ],
                                    6 => [
                                        'language' => 'nl_NL',
                                        'value' => NULL,
                                    ],
                                ],
                                'placeholder' => [
                                    0 => [
                                        'language' => 'pl_PL',
                                        'value' => NULL,
                                    ],
                                    1 => [
                                        'language' => 'cs_CZ',
                                        'value' => NULL,
                                    ],
                                    2 => [
                                        'language' => 'da_DK',
                                        'value' => NULL,
                                    ],
                                    3 => [
                                        'language' => 'en_US',
                                        'value' => NULL,
                                    ],
                                    4 => [
                                        'language' => 'fi_FI',
                                        'value' => NULL,
                                    ],
                                    5 => [
                                        'language' => 'hr_HR',
                                        'value' => NULL,
                                    ],
                                    6 => [
                                        'language' => 'nl_NL',
                                        'value' => NULL,
                                    ],
                                ],
                                'type_price' => 'price',
                                'additional_currency' => 'PLN',
                            ],
                        ],
                        4 => [
                            'cursor' => 'YXJyYXljb25uZWN0aW9uOjI5NDIwNw==',
                            'node' => [
                                'code' => 'color',
                                'scope' => 'GLOBAL',
                                'label' => [
                                    0 => [
                                        'language' => 'pl_PL',
                                        'value' => 'kolor',
                                    ],
                                    1 => [
                                        'language' => 'cs_CZ',
                                        'value' => NULL,
                                    ],
                                    2 => [
                                        'language' => 'da_DK',
                                        'value' => NULL,
                                    ],
                                    3 => [
                                        'language' => 'en_US',
                                        'value' => 'color',
                                    ],
                                    4 => [
                                        'language' => 'fi_FI',
                                        'value' => NULL,
                                    ],
                                    5 => [
                                        'language' => 'hr_HR',
                                        'value' => NULL,
                                    ],
                                    6 => [
                                        'language' => 'nl_NL',
                                        'value' => NULL,
                                    ],
                                ],
                                'hint' => [
                                    0 => [
                                        'language' => 'pl_PL',
                                        'value' => 'kolor produktu',
                                    ],
                                    1 => [
                                        'language' => 'cs_CZ',
                                        'value' => NULL,
                                    ],
                                    2 => [
                                        'language' => 'da_DK',
                                        'value' => NULL,
                                    ],
                                    3 => [
                                        'language' => 'en_US',
                                        'value' => 'color of product',
                                    ],
                                    4 => [
                                        'language' => 'fi_FI',
                                        'value' => NULL,
                                    ],
                                    5 => [
                                        'language' => 'hr_HR',
                                        'value' => NULL,
                                    ],
                                    6 => [
                                        'language' => 'nl_NL',
                                        'value' => NULL,
                                    ],
                                ],
                                'placeholder' => [
                                    0 => [
                                        'language' => 'pl_PL',
                                        'value' => NULL,
                                    ],
                                    1 => [
                                        'language' => 'cs_CZ',
                                        'value' => NULL,
                                    ],
                                    2 => [
                                        'language' => 'da_DK',
                                        'value' => NULL,
                                    ],
                                    3 => [
                                        'language' => 'en_US',
                                        'value' => NULL,
                                    ],
                                    4 => [
                                        'language' => 'fi_FI',
                                        'value' => NULL,
                                    ],
                                    5 => [
                                        'language' => 'hr_HR',
                                        'value' => NULL,
                                    ],
                                    6 => [
                                        'language' => 'nl_NL',
                                        'value' => NULL,
                                    ],
                                ],
                                'type_select' => 'color',
                                'options' => [
                                    0 => [
                                        'code' => 'black',
                                        'label' => [
                                            0 => [
                                                'language' => 'pl_PL',
                                                'value' => 'czarny',
                                            ],
                                            1 => [
                                                'language' => 'cs_CZ',
                                                'value' => NULL,
                                            ],
                                            2 => [
                                                'language' => 'da_DK',
                                                'value' => NULL,
                                            ],
                                            3 => [
                                                'language' => 'en_US',
                                                'value' => 'black',
                                            ],
                                            4 => [
                                                'language' => 'fi_FI',
                                                'value' => NULL,
                                            ],
                                            5 => [
                                                'language' => 'hr_HR',
                                                'value' => NULL,
                                            ],
                                            6 => [
                                                'language' => 'nl_NL',
                                                'value' => NULL,
                                            ],
                                        ],
                                    ],
                                    1 => [
                                        'code' => 'white',
                                        'label' => [
                                            0 => [
                                                'language' => 'pl_PL',
                                                'value' => 'biały',
                                            ],
                                            1 => [
                                                'language' => 'cs_CZ',
                                                'value' => NULL,
                                            ],
                                            2 => [
                                                'language' => 'da_DK',
                                                'value' => NULL,
                                            ],
                                            3 => [
                                                'language' => 'en_US',
                                                'value' => 'white',
                                            ],
                                            4 => [
                                                'language' => 'fi_FI',
                                                'value' => NULL,
                                            ],
                                            5 => [
                                                'language' => 'hr_HR',
                                                'value' => NULL,
                                            ],
                                            6 => [
                                                'language' => 'nl_NL',
                                                'value' => NULL,
                                            ],
                                        ],
                                    ],
                                    2 => [
                                        'code' => 'blue',
                                        'label' => [
                                            0 => [
                                                'language' => 'pl_PL',
                                                'value' => 'niebieski',
                                            ],
                                            1 => [
                                                'language' => 'cs_CZ',
                                                'value' => NULL,
                                            ],
                                            2 => [
                                                'language' => 'da_DK',
                                                'value' => NULL,
                                            ],
                                            3 => [
                                                'language' => 'en_US',
                                                'value' => 'blue',
                                            ],
                                            4 => [
                                                'language' => 'fi_FI',
                                                'value' => NULL,
                                            ],
                                            5 => [
                                                'language' => 'hr_HR',
                                                'value' => NULL,
                                            ],
                                            6 => [
                                                'language' => 'nl_NL',
                                                'value' => NULL,
                                            ],
                                        ],
                                    ],
                                    3 => [
                                        'code' => 'violet',
                                        'label' => [
                                            0 => [
                                                'language' => 'pl_PL',
                                                'value' => 'fioletowy',
                                            ],
                                            1 => [
                                                'language' => 'cs_CZ',
                                                'value' => NULL,
                                            ],
                                            2 => [
                                                'language' => 'da_DK',
                                                'value' => NULL,
                                            ],
                                            3 => [
                                                'language' => 'en_US',
                                                'value' => 'violet',
                                            ],
                                            4 => [
                                                'language' => 'fi_FI',
                                                'value' => NULL,
                                            ],
                                            5 => [
                                                'language' => 'hr_HR',
                                                'value' => NULL,
                                            ],
                                            6 => [
                                                'language' => 'nl_NL',
                                                'value' => NULL,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        5 => [
                            'cursor' => 'YXJyYXljb25uZWN0aW9uOjI5NDI1NA==',
                            'node' => [
                                'code' => 'size',
                                'scope' => 'GLOBAL',
                                'label' => [
                                    0 => [
                                        'language' => 'pl_PL',
                                        'value' => 'rozmiar',
                                    ],
                                    1 => [
                                        'language' => 'cs_CZ',
                                        'value' => NULL,
                                    ],
                                    2 => [
                                        'language' => 'da_DK',
                                        'value' => NULL,
                                    ],
                                    3 => [
                                        'language' => 'en_US',
                                        'value' => 'size',
                                    ],
                                    4 => [
                                        'language' => 'fi_FI',
                                        'value' => NULL,
                                    ],
                                    5 => [
                                        'language' => 'hr_HR',
                                        'value' => NULL,
                                    ],
                                    6 => [
                                        'language' => 'nl_NL',
                                        'value' => NULL,
                                    ],
                                ],
                                'hint' => [
                                    0 => [
                                        'language' => 'pl_PL',
                                        'value' => 'rozmiar produktu',
                                    ],
                                    1 => [
                                        'language' => 'cs_CZ',
                                        'value' => NULL,
                                    ],
                                    2 => [
                                        'language' => 'da_DK',
                                        'value' => NULL,
                                    ],
                                    3 => [
                                        'language' => 'en_US',
                                        'value' => 'product size',
                                    ],
                                    4 => [
                                        'language' => 'fi_FI',
                                        'value' => NULL,
                                    ],
                                    5 => [
                                        'language' => 'hr_HR',
                                        'value' => NULL,
                                    ],
                                    6 => [
                                        'language' => 'nl_NL',
                                        'value' => NULL,
                                    ],
                                ],
                                'placeholder' => [
                                    0 => [
                                        'language' => 'pl_PL',
                                        'value' => NULL,
                                    ],
                                    1 => [
                                        'language' => 'cs_CZ',
                                        'value' => NULL,
                                    ],
                                    2 => [
                                        'language' => 'da_DK',
                                        'value' => NULL,
                                    ],
                                    3 => [
                                        'language' => 'en_US',
                                        'value' => NULL,
                                    ],
                                    4 => [
                                        'language' => 'fi_FI',
                                        'value' => NULL,
                                    ],
                                    5 => [
                                        'language' => 'hr_HR',
                                        'value' => NULL,
                                    ],
                                    6 => [
                                        'language' => 'nl_NL',
                                        'value' => NULL,
                                    ],
                                ],
                                'type_select' => 'size',
                                'options' => [
                                    0 => [
                                        'code' => 's',
                                        'label' => [
                                            0 => [
                                                'language' => 'pl_PL',
                                                'value' => 'S',
                                            ],
                                            1 => [
                                                'language' => 'cs_CZ',
                                                'value' => NULL,
                                            ],
                                            2 => [
                                                'language' => 'da_DK',
                                                'value' => NULL,
                                            ],
                                            3 => [
                                                'language' => 'en_US',
                                                'value' => 'S',
                                            ],
                                            4 => [
                                                'language' => 'fi_FI',
                                                'value' => NULL,
                                            ],
                                            5 => [
                                                'language' => 'hr_HR',
                                                'value' => NULL,
                                            ],
                                            6 => [
                                                'language' => 'nl_NL',
                                                'value' => NULL,
                                            ],
                                        ],
                                    ],
                                    1 => [
                                        'code' => 'm',
                                        'label' => [
                                            0 => [
                                                'language' => 'pl_PL',
                                                'value' => 'M',
                                            ],
                                            1 => [
                                                'language' => 'cs_CZ',
                                                'value' => NULL,
                                            ],
                                            2 => [
                                                'language' => 'da_DK',
                                                'value' => NULL,
                                            ],
                                            3 => [
                                                'language' => 'en_US',
                                                'value' => 'M',
                                            ],
                                            4 => [
                                                'language' => 'fi_FI',
                                                'value' => NULL,
                                            ],
                                            5 => [
                                                'language' => 'hr_HR',
                                                'value' => NULL,
                                            ],
                                            6 => [
                                                'language' => 'nl_NL',
                                                'value' => NULL,
                                            ],
                                        ],
                                    ],
                                    2 => [
                                        'code' => 'l',
                                        'label' => [
                                            0 => [
                                                'language' => 'pl_PL',
                                                'value' => 'L',
                                            ],
                                            1 => [
                                                'language' => 'cs_CZ',
                                                'value' => NULL,
                                            ],
                                            2 => [
                                                'language' => 'da_DK',
                                                'value' => NULL,
                                            ],
                                            3 => [
                                                'language' => 'en_US',
                                                'value' => 'L',
                                            ],
                                            4 => [
                                                'language' => 'fi_FI',
                                                'value' => NULL,
                                            ],
                                            5 => [
                                                'language' => 'hr_HR',
                                                'value' => NULL,
                                            ],
                                            6 => [
                                                'language' => 'nl_NL',
                                                'value' => NULL,
                                            ],
                                        ],
                                    ],
                                    3 => [
                                        'code' => 'xl',
                                        'label' => [
                                            0 => [
                                                'language' => 'pl_PL',
                                                'value' => 'XL',
                                            ],
                                            1 => [
                                                'language' => 'cs_CZ',
                                                'value' => NULL,
                                            ],
                                            2 => [
                                                'language' => 'da_DK',
                                                'value' => NULL,
                                            ],
                                            3 => [
                                                'language' => 'en_US',
                                                'value' => 'XL',
                                            ],
                                            4 => [
                                                'language' => 'fi_FI',
                                                'value' => NULL,
                                            ],
                                            5 => [
                                                'language' => 'hr_HR',
                                                'value' => NULL,
                                            ],
                                            6 => [
                                                'language' => 'nl_NL',
                                                'value' => NULL,
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