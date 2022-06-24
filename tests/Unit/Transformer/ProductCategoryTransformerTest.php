<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Unit\Provider;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\Context;
use Strix\Ergonode\DTO\ProductTransformationDTO;
use Strix\Ergonode\Provider\CategoryProvider;
use Strix\Ergonode\Transformer\ProductCategoryTransformer;

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
        $result = $this->productCategoryTransformer->transform(
            new ProductTransformationDTO(ProductTransformationDTO::OPERATION_CREATE, $data),
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
