<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Unit\Transformer;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\System\Tax\TaxEntity;
use Strix\Ergonode\DTO\ProductTransformationDTO;
use Strix\Ergonode\Provider\TaxProvider;
use Strix\Ergonode\Transformer\ProductTaxTransformer;

class ProductTaxTransformerTest extends TestCase
{
    private const TEST_TAX_RATE = 19;
    private const TEST_TAX_ID = 'cc75d746500b47f4b51711e9ad91d674';

    private ProductTaxTransformer $productTaxTransformer;

    /**
     * @var MockObject|TaxProvider
     */
    private $taxProvider;

    /**
     * @var MockObject|EntityRepositoryInterface
     */
    private $taxRepository;

    /**
     * @var MockObject|Context
     */
    private $contextMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);

        $this->taxProvider = $this->createMock(TaxProvider::class);
        $this->taxRepository = $this->createMock(EntityRepositoryInterface::class);

        $this->productTaxTransformer = new ProductTaxTransformer($this->taxProvider, $this->taxRepository);
    }

    public function testTransformingExistingTax(): void
    {
        $data = [
            'tax' => [
                'rate' => self::TEST_TAX_RATE
            ]
        ];

        $taxEntity = $this->createMock(TaxEntity::class);
        $taxEntity->method('getId')->willReturn(self::TEST_TAX_ID);

        $this->taxProvider
            ->expects($this->once())
            ->method('getByTaxRate')
            ->with(self::TEST_TAX_RATE, $this->contextMock)
            ->willReturn($taxEntity);

        $result = $this->productTaxTransformer->transform(new ProductTransformationDTO([], $data), $this->contextMock);

        $this->assertEquals([
            'taxId' => self::TEST_TAX_ID
        ], $result->getShopwareData());

        $this->assertArrayNotHasKey('tax', $result->getShopwareData());
    }

    public function testCreatingTax(): void
    {
        $data = [
            'tax' => [
                'rate' => self::TEST_TAX_RATE
            ]
        ];

        $this->taxProvider
            ->expects($this->once())
            ->method('getByTaxRate')
            ->with(self::TEST_TAX_RATE, $this->contextMock)
            ->willReturn(null);

        $entityWrittenEvent = $this->createMock(EntityWrittenContainerEvent::class);
        $entityWrittenEvent->method('getPrimaryKeys')->willReturn([
            self::TEST_TAX_ID
        ]);

        $this->taxRepository
            ->expects($this->once())
            ->method('create')
            ->with([
                [
                    'taxRate' => self::TEST_TAX_RATE,
                    'name' => 'Ergonode Autogenerated (' . self::TEST_TAX_RATE . '%)'
                ]
            ], $this->contextMock)
            ->willReturn($entityWrittenEvent);


        $result = $this->productTaxTransformer->transform(new ProductTransformationDTO([], $data), $this->contextMock);

        $this->assertEquals([
            'taxId' => self::TEST_TAX_ID
        ], $result->getShopwareData());

        $this->assertArrayNotHasKey('tax', $result->getShopwareData());
    }
}
