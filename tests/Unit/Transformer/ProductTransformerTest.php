<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests\Unit\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingCollection;
use Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingEntity;
use Ergonode\IntegrationShopware\Provider\Mapping\AttributeMappingProvider;
use Ergonode\IntegrationShopware\Provider\LanguageProvider;
use Ergonode\IntegrationShopware\Transformer\ProductTransformer;
use Ergonode\IntegrationShopware\Util\AttributeTypeValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;

class ProductTransformerTest extends TestCase
{
    private const MOCK_MAPPING = [
        'name' => 'name',
        'stock' => 'stock',
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

    /**
     * @var AttributeTypeValidator|MockObject
     */
    private $attributeTypeValidator;

    protected function setUp(): void
    {
        $this->attributeMappingProvider = $this->createMock(AttributeMappingProvider::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->attributeTypeValidator = $this->createMock(AttributeTypeValidator::class);
        $this->mockAttributeMappingProvider();
        $this->mockAttributeTypeValidator();

        $languageProvider = $this->createMock(LanguageProvider::class);
        $languageProvider->method('getDefaultLanguageLocale')->willReturn('en-GB');

        $this->productTransformer = new ProductTransformer(
            $this->attributeMappingProvider,
            $languageProvider,
            $this->attributeTypeValidator
        );
    }

    /**
     * @dataProvider getProductData
     */
    public function testTransformingData(array $data): void
    {
        $result = $this->productTransformer->transform(
            new ProductTransformationDTO($data),
            $this->contextMock
        );

        $this->assertEquals([
            'name' => 'Test product EN',
            'translations' => [
                'pl-PL' => [
                    'name' => 'Test product PL',
                ],
                'en-GB' => [
                    'name' => 'Test product EN',
                ],
            ],
            'stock' => 999,
        ], $result->getShopwareData());
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
                                                    'language' => 'en_GB',
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
                                                    'language' => 'en_GB',
                                                    '__typename' => 'NumericAttributeValue',
                                                    'value_numeric' => 999,
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

    private function mockAttributeMappingProvider(): void
    {
        $returnValueMap = [];
        foreach (self::MOCK_MAPPING as $swKey => $ergoKey) {
            $mappingEntity = $this->createMock(ErgonodeAttributeMappingEntity::class);
            $mappingEntity->method('getShopwareKey')->willReturn($swKey);
            $mappingEntity->method('getErgonodeKey')->willReturn($ergoKey);

            $mappingCollection = new ErgonodeAttributeMappingCollection([
                $mappingEntity,
            ]);

            $returnValueMap[] = [
                $ergoKey,
                $this->contextMock,
                $mappingCollection,
            ];
        }

        $this->attributeMappingProvider
            ->method('provideByErgonodeKey')
            ->willReturnMap($returnValueMap);
    }

    private function mockAttributeTypeValidator(): void
    {
        $this->attributeTypeValidator
            ->expects($this->exactly(count(self::MOCK_MAPPING)))
            ->method('filterWrongAttributes');
    }
}
