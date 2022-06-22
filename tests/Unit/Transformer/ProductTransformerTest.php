<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Unit\Provider;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Strix\Ergonode\Modules\Attribute\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingCollection;
use Strix\Ergonode\Modules\Attribute\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingEntity;
use Strix\Ergonode\Modules\Attribute\Provider\AttributeMappingProvider;
use Strix\Ergonode\Transformer\ProductTransformer;

class ProductTransformerTest extends TestCase
{
    private const MOCK_MAPPING = [
        'name' => 'name',
        'stock' => 'stock',
        'tax.rate' => 'tax',
        'price.net' => 'priceNet',
        'price.gross' => 'priceGross'
    ];

    private ProductTransformer $productTransformer;

    /**
     * @var MockObject|AttributeMappingProvider
     */
    private $attributeMappingProvider;

    /**
     * @var MockObject|Context
     */
    private $contextMock;

    protected function setUp(): void
    {
        $this->attributeMappingProvider = $this->createMock(AttributeMappingProvider::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->mockAttributeMappingProvider();

        $this->productTransformer = new ProductTransformer(
            $this->attributeMappingProvider
        );
    }

    private function mockAttributeMappingProvider(): void
    {
        $returnValueMap = [];
        foreach (self::MOCK_MAPPING as $swKey => $ergoKey) {
            $mappingEntity = $this->createMock(ErgonodeAttributeMappingEntity::class);
            $mappingEntity->method('getShopwareKey')->willReturn($swKey);
            $mappingEntity->method('getErgonodeKey')->willReturn($ergoKey);

            $mappingCollection = new ErgonodeAttributeMappingCollection([
                $mappingEntity
            ]);

            $returnValueMap[] = [
                $ergoKey,
                $this->contextMock,
                $mappingCollection
            ];
        }

        $this->attributeMappingProvider
            ->method('provideByErgonodeKey')
            ->willReturnMap($returnValueMap);
    }

    /**
     * @dataProvider getProductData
     */
    public function testTransformingData(array $data): void
    {
        $result = $this->productTransformer->transform($data, $this->contextMock);

        $this->assertEquals([
            'name' => 'Test product EN',
            'translations' => [
                'pl-PL' => [
                    'name' => 'Test product PL'
                ]
            ],
            'stock' => 999,
            'tax' => [
                'rate' => 23
            ],
            'price' => [
                'net' => 100,
                'gross' => 123,
            ]
        ], $result);
    }

    public function getProductData(): array
    {
        return [
            [
                [
                    'sku' => 'MP_0001',
                    'createdAt' => '2022-06-01T11:10:47+00:00',
                    'editedAt' => '2022-06-20T12:13:44+00:00',
                    '__typename' => 'SimpleProduct',
                    'template' => [
                        'name' => '',
                    ],
                    'attributeList' => [
                        'edges' => [
                            [
                                'node' =>
                                    [
                                        'attribute' =>
                                            [
                                                'code' => 'name',
                                            ],
                                        'valueTranslations' =>
                                            [
                                                [
                                                    'inherited' => false,
                                                    'language' => 'pl_PL',
                                                    '__typename' => 'StringAttributeValue',
                                                    'value_string' => 'Test product PL',
                                                ],
                                                [
                                                    'inherited' => false,
                                                    'language' => 'en_US',
                                                    '__typename' => 'StringAttributeValue',
                                                    'value_string' => 'Test product EN',
                                                ],
                                            ],
                                    ],
                            ],
                            [
                                'node' =>
                                    [
                                        'attribute' =>
                                            [
                                                'code' => 'stock',
                                            ],
                                        'valueTranslations' =>
                                            [
                                                [
                                                    'inherited' => false,
                                                    'language' => 'pl_PL',
                                                    '__typename' => 'NumericAttributeValue',
                                                    'value_numeric' => 999,
                                                ],
                                                [
                                                    'inherited' => false,
                                                    'language' => 'en_US',
                                                    '__typename' => 'NumericAttributeValue',
                                                    'value_numeric' => 999,
                                                ],
                                            ],
                                    ],
                            ],
                            [
                                'node' =>
                                    [
                                        'attribute' =>
                                            [
                                                'code' => 'tax',
                                            ],
                                        'valueTranslations' =>
                                            [
                                                [
                                                    'inherited' => false,
                                                    'language' => 'pl_PL',
                                                    '__typename' => 'NumericAttributeValue',
                                                    'value_numeric' => 23,
                                                ],
                                                [
                                                    'inherited' => false,
                                                    'language' => 'en_US',
                                                    '__typename' => 'NumericAttributeValue',
                                                    'value_numeric' => 23,
                                                ],
                                            ],
                                    ],
                            ],
                            [
                                'node' =>
                                    [
                                        'attribute' =>
                                            [
                                                'code' => 'priceNet',
                                            ],
                                        'valueTranslations' =>
                                            [
                                                [
                                                    'inherited' => false,
                                                    'language' => 'pl_PL',
                                                    '__typename' => 'NumericAttributeValue',
                                                    'value_numeric' => 100,
                                                ],
                                                [
                                                    'inherited' => false,
                                                    'language' => 'en_US',
                                                    '__typename' => 'NumericAttributeValue',
                                                    'value_numeric' => 100,
                                                ],
                                            ],
                                    ],
                            ],
                            [
                                'node' =>
                                    [
                                        'attribute' =>
                                            [
                                                'code' => 'priceGross',
                                            ],
                                        'valueTranslations' =>
                                            [
                                                [
                                                    'inherited' => false,
                                                    'language' => 'pl_PL',
                                                    '__typename' => 'NumericAttributeValue',
                                                    'value_numeric' => 123,
                                                ],
                                                [
                                                    'inherited' => false,
                                                    'language' => 'en_US',
                                                    '__typename' => 'NumericAttributeValue',
                                                    'value_numeric' => 123,
                                                ],
                                            ],
                                    ],
                            ],
                        ],
                    ],
                ]
            ]
        ];
    }
}
