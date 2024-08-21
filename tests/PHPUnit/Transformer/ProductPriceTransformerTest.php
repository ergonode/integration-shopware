<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests\PHPUnit\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Transformer\ProductPriceTransformer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
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

    /**
     * @dataProvider getProductData
     */
    public function testTransformingData(array $data): void
    {
        $result = $this->productPriceTransformer->transform(new ProductTransformationDTO([], $data), $this->contextMock);

        $this->assertEquals([
            'price' => [
                [
                    'net' => 100,
                    'gross' => 123,
                    'linked' => false,
                    'currencyId' => Defaults::CURRENCY
                ]
            ]
        ], $result->getShopwareData());
    }

    public function getProductData(): array
    {
        return [
            [
                [
                    'price' => [
                        'net' => 100,
                        'gross' => 123,
                    ]
                ]
            ]
        ];
    }
}
