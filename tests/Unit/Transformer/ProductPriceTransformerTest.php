<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Unit\Provider;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Strix\Ergonode\DTO\ProductTransformationDTO;
use Strix\Ergonode\Transformer\ProductPriceTransformer;

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
        $result = $this->productPriceTransformer->transform(
            new ProductTransformationDTO(ProductTransformationDTO::OPERATION_CREATE, []),
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
        $result = $this->productPriceTransformer->transform(
            new ProductTransformationDTO(ProductTransformationDTO::OPERATION_UPDATE, []),
            $this->contextMock
        );

        $this->assertArrayNotHasKey('price', $result->getShopwareData());
    }
}
