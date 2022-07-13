<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests\Unit\Service;

use Ergonode\IntegrationShopware\Provider\AttributeMappingProvider;
use Ergonode\IntegrationShopware\Provider\ErgonodeAttributeProvider;
use Ergonode\IntegrationShopware\Service\AttributeMapper;
use Ergonode\IntegrationShopware\Tests\Fixture\ErgonodeAttributeMappingFixture;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;

class AttributeMapperTest extends TestCase
{
    private AttributeMapper $attributeMapper;

    /**
     * @var MockObject|AttributeMappingProvider
     */
    private AttributeMappingProvider $attributeMappingProviderMock;

    /**
     * @var MockObject|Context
     */
    private Context $contextMock;

    protected function setUp(): void
    {
        $ergonodeAttributeProviderMock = $this->createMock(ErgonodeAttributeProvider::class);
        $this->attributeMappingProviderMock = $this->createMock(AttributeMappingProvider::class);

        $this->contextMock = $this->createMock(Context::class);

        $this->attributeMapper = new AttributeMapper(
            $ergonodeAttributeProviderMock,
            $this->attributeMappingProviderMock
        );
    }

    /**
     * @dataProvider shopwareToErgonodeMappingDataProvider
     */
    public function testMapShopwareKeyMethodWhenEntityProvided(string $shopwareKey, string $ergonodeKey)
    {
        $this->mockProviderMethod('provideByShopwareKey', ErgonodeAttributeMappingFixture::entity($shopwareKey, $ergonodeKey));

        $output = $this->attributeMapper->mapShopwareKey($shopwareKey, $this->contextMock);

        $this->assertEquals($ergonodeKey, $output);
    }

    /**
     * @dataProvider ergonodeToShopwareMappingDataProvider
     */
    public function testMapErgonodeKeyMethodWhenEntitiesProvided(string $ergonodeKey, array $shopwareKeys)
    {
        $collection = [];
        foreach ($shopwareKeys as $shopwareKey) {
            $collection[] = [$shopwareKey, $ergonodeKey];
        }

        $this->mockProviderMethod('provideByErgonodeKey', ErgonodeAttributeMappingFixture::collection($collection));

        $output = $this->attributeMapper->mapErgonodeKey($ergonodeKey, $this->contextMock);

        $this->assertEquals($shopwareKeys, $output);
    }

    public function testMapShopwareKeyMethodWhenNullProvided()
    {
        $this->mockProviderMethod('provideByShopwareKey', null);

        $output = $this->attributeMapper->mapShopwareKey('example_string', $this->contextMock);

        $this->assertEquals(null, $output);
    }

    public function testMapErgonodeKeyMethodWhenEmptyCollectionProvided()
    {
        $this->mockProviderMethod('provideByErgonodeKey', ErgonodeAttributeMappingFixture::collection([]));

        $output = $this->attributeMapper->mapErgonodeKey('example_string', $this->contextMock);

        $this->assertEquals([], $output);
    }

    public function shopwareToErgonodeMappingDataProvider(): array
    {
        return [
            ['name', 'product_name'],
            ['description', 'some_ergonode_key'],
            ['someShopwareKey', 'some_ergonode_key'],
            ['sadfasdfdas', '2342343242'],
        ];
    }

    public function ergonodeToShopwareMappingDataProvider(): array
    {
        return [
            ['product_name', ['name']],
            ['some_ergonode_key', ['description', 'someShopwareKey']],
            ['2342343242', ['sadfasdfdas']],
        ];
    }

    private function mockProviderMethod(string $method, $result): void
    {
        $this->attributeMappingProviderMock->expects($this->once())
            ->method($method)
            ->willReturn($result);
    }
}
