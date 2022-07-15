<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests\Unit\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Transformer\ProductPriceTransformer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;

class ProductPriceTransformerTest extends TestCase
{
    private ProductPriceTransformer $productPriceTransformer;

    /**
     * @var MockObject|Context
     */
    private $contextMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);

        $this->productPriceTransformer = new ProductPriceTransformer();
    }

    public function testTransformingNewProduct(): void
    {
        $dto = new ProductTransformationDTO([]);
        $dto->setSwProduct(null);

        $result = $this->productPriceTransformer->transform(
            $dto,
            $this->contextMock
        );

        $this->assertEquals([
            'price' => [
                [
                    'net' => 0,
                    'gross' => 0,
                    'linked' => false,
                    'currencyId' => Defaults::CURRENCY
                ]
            ]
        ], $result->getShopwareData());
    }

    public function testTransformingUpdatedProduct(): void
    {
        $dto = new ProductTransformationDTO([]);
        $dto->setSwProduct($this->createMock(ProductEntity::class));

        $result = $this->productPriceTransformer->transform(
            $dto,
            $this->contextMock
        );

        $this->assertArrayNotHasKey('price', $result->getShopwareData());
    }
}
