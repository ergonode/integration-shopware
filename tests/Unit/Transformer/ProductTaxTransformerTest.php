<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Unit\Transformer;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\Tax\TaxEntity;
use Strix\Ergonode\DTO\ProductTransformationDTO;
use Strix\Ergonode\Provider\TaxProvider;
use Strix\Ergonode\Transformer\ProductTaxTransformer;

class ProductTaxTransformerTest extends TestCase
{
    private const TEST_TAX_ID = 'cc75d746500b47f4b51711e9ad91d674';

    private ProductTaxTransformer $productTaxTransformer;

    /**
     * @var MockObject|TaxProvider
     */
    private $taxProvider;

    /**
     * @var MockObject|Context
     */
    private $contextMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->taxProvider = $this->createMock(TaxProvider::class);

        $this->productTaxTransformer = new ProductTaxTransformer($this->taxProvider);
    }

    public function testTransformingNewProduct(): void
    {
        $taxEntity = $this->createMock(TaxEntity::class);
        $taxEntity->method('getId')->willReturn(self::TEST_TAX_ID);

        $this->taxProvider
            ->expects($this->once())
            ->method('getDefaultTax')
            ->with($this->contextMock)
            ->willReturn($taxEntity);

        $dto = new ProductTransformationDTO([]);
        $dto->setSwProduct(null);

        $result = $this->productTaxTransformer->transform(
            $dto,
            $this->contextMock
        );

        $this->assertEquals([
            'taxId' => self::TEST_TAX_ID
        ], $result->getShopwareData());
    }

    public function testTransformingUpdatedProduct(): void
    {
        $dto = new ProductTransformationDTO([]);
        $dto->setSwProduct($this->createMock(ProductEntity::class));

        $result = $this->productTaxTransformer->transform(
            $dto,
            $this->contextMock
        );

        $this->assertArrayNotHasKey('taxId', $result->getShopwareData());
    }
}
