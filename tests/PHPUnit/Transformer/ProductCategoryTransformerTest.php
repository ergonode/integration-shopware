<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests\PHPUnit\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Provider\CategoryProvider;
use Ergonode\IntegrationShopware\Transformer\ProductCategoryTransformer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\Context;

class ProductCategoryTransformerTest extends TestCase
{
    private const CATEGORY_CODE = 'test';
    private const CATEGORY_ID = '194b7b7a4c984089b474839d9cc019d3';

    private ProductCategoryTransformer $productCategoryTransformer;

    /**
     * @var MockObject|CategoryProvider
     */
    private $categoryProvider;

    /**
     * @var MockObject|Context
     */
    private $contextMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->categoryProvider = $this->createMock(CategoryProvider::class);

        $categoryEntity = $this->createMock(CategoryEntity::class);
        $categoryEntity->method('getId')->willReturn(self::CATEGORY_ID);
        $this->categoryProvider
            ->expects($this->once())
            ->method('getCategoriesByCode')
            ->with(self::CATEGORY_CODE)
            ->willReturn(
                new CategoryCollection([$categoryEntity])
            );

        $this->productCategoryTransformer = new ProductCategoryTransformer(
            $this->categoryProvider
        );
    }

    /**
     * @dataProvider getProductData
     */
    public function testTransformingData(array $data): void
    {
        $dto = new ProductTransformationDTO($data);
        $dto->setSwProduct(null);

        $result = $this->productCategoryTransformer->transform(
            $dto,
            $this->contextMock
        );

        $this->assertEquals([
            'categories' => [
                [
                    'id' => self::CATEGORY_ID
                ]
            ]
        ], $result->getShopwareData());
    }

    public function getProductData(): array
    {
        return
            [
                [
                    [
                        'categoryList' => [
                            'edges' => [
                                [
                                    'node' => [
                                        'code' => self::CATEGORY_CODE
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
    }
}
