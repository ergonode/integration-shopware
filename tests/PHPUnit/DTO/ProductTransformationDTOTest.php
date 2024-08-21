<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests\PHPUnit\DTO;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use PHPUnit\Framework\TestCase;

class ProductTransformationDTOTest extends TestCase
{
    /**
     * @dataProvider deleteEntitiesDataProvider
     */
    public function testAddEntitiesToDeleteMethod(array $config, array $expectedOutput): void
    {
        $dto = new ProductTransformationDTO([]);

        foreach ($config as $entityName => $payloads) {
            foreach ($payloads as $payload) {
                $dto->addEntitiesToDelete($entityName, $payload);
            }
        }

        $output = $dto->getEntitiesToDelete();

        $this->assertThat($output, $this->identicalTo($expectedOutput));
    }

    public function deleteEntitiesDataProvider(): iterable
    {
        yield [
            'config' => [
                'product_option' => [
                    [
                        'productId' => 'product_1',
                        'optionId' => 'option_1',
                    ],
                    [
                        [
                            'productId' => 'product_2_1',
                            'optionId' => 'option_1',
                        ],
                        [
                            'productId' => 'product_2_2',
                            'optionId' => 'option_1',
                        ],
                    ],
                    [
                        'productId' => 'product_3',
                        'optionId' => 'option_1',
                    ],
                ],
            ],
            'expectedOutput' => [
                'product_option' => [
                    [
                        'productId' => 'product_1',
                        'optionId' => 'option_1',
                    ],
                    [
                        'productId' => 'product_2_1',
                        'optionId' => 'option_1',
                    ],
                    [
                        'productId' => 'product_2_2',
                        'optionId' => 'option_1',
                    ],
                    [
                        'productId' => 'product_3',
                        'optionId' => 'option_1',
                    ],
                ],
            ],
        ];

        yield [
            'config' => [
                'product' => [
                    [
                        'id' => 'product_1',
                    ],
                    [
                        'id' => 'product_2',
                    ],
                    [
                        'id' => 'product_3',
                    ],
                ],
            ],
            'expectedOutput' => [
                'product' => [
                    [
                        'id' => 'product_1',
                    ],
                    [
                        'id' => 'product_2',
                    ],
                    [
                        'id' => 'product_3',
                    ],
                ],
            ],
        ];

        yield [
            'config' => [
                'product' => [
                    [
                        [
                            'id' => 'product_1',
                        ],
                        [
                            'id' => 'product_2',
                        ],
                        [
                            'id' => 'product_3',
                        ],
                    ],
                ],
            ],
            'expectedOutput' => [
                'product' => [
                    [
                        'id' => 'product_1',
                    ],
                    [
                        'id' => 'product_2',
                    ],
                    [
                        'id' => 'product_3',
                    ],
                ],
            ],
        ];

        yield [
            'config' => [
                'entity_one' => [
                    [
                        [
                            [
                                'id' => 'entity_one_1_1',
                            ],
                            [
                                'id' => 'entity_one_1_2',
                            ],
                        ],
                        [
                            'id' => 'entity_one_2',
                        ],
                        [
                            'id' => 'entity_one_3',
                        ],
                    ],
                ],
                'entity_two' => [
                    [
                        [
                            'id' => 'entity_two_1',
                        ],
                        [
                            [
                                'id' => 'entity_two_2_1',
                            ],
                            [
                                'id' => 'entity_two_2_2',
                            ],
                        ],
                        [
                            'id' => 'entity_two_3',
                        ],
                    ],
                ],
            ],
            'expectedOutput' => [
                'entity_one' => [
                    [
                        'id' => 'entity_one_1_1',
                    ],
                    [
                        'id' => 'entity_one_1_2',
                    ],
                    [
                        'id' => 'entity_one_2',
                    ],
                    [
                        'id' => 'entity_one_3',
                    ],
                ],
                'entity_two' => [
                    [
                        'id' => 'entity_two_1',
                    ],
                    [
                        'id' => 'entity_two_2_1',
                    ],
                    [
                        'id' => 'entity_two_2_2',
                    ],
                    [
                        'id' => 'entity_two_3',
                    ],
                ],
            ],
        ];
    }
}
