<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests\Unit\Provider;

use Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingEntity;
use Ergonode\IntegrationShopware\Provider\AttributeMappingProvider;
use Ergonode\IntegrationShopware\Tests\Fixture\ErgonodeAttributeMappingFixture;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;

class AttributeMappingProviderTest extends TestCase
{
    /**
     * @var MockObject|EntityRepositoryInterface
     */
    private EntityRepositoryInterface $mappingRepositoryMock;

    private AttributeMappingProvider $provider;

    /**
     * @var MockObject|Context
     */
    private $contextMock;

    protected function setUp(): void
    {
        $this->mappingRepositoryMock = $this->createMock(EntityRepositoryInterface::class);
        
        $this->contextMock = $this->createMock(Context::class);

        $this->provider = new AttributeMappingProvider(
            $this->mappingRepositoryMock
        );
    }

    public function testProvideByShopwareKeyMethod()
    {
        $this->mockRepositoryResult(ErgonodeAttributeMappingFixture::collection([['swKey', 'ergo_key']]));

        $output = $this->provider->provideByShopwareKey('swKey', $this->contextMock);

        $this->assertEquals('ergo_key', $output->getErgonodeKey());
    }

    public function testProvideByErgonodeKeyMethod()
    {
        $this->mockRepositoryResult(ErgonodeAttributeMappingFixture::collection([['swKey1', 'some_ergo_key'], ['swKey2', 'some_ergo_key']]));

        $output = $this->provider->provideByErgonodeKey('some_ergo_key', $this->contextMock);

        $rawOutput = $output->map(fn(ErgonodeAttributeMappingEntity $entity) => $entity->getShopwareKey());

        $this->assertEquals(['swKey1', 'swKey2'], array_values($rawOutput));
    }

    public function testProviderWhenRepositoryReturnsWrongType()
    {
        $this->mockRepositoryResult(new ProductCollection());

        $output = $this->provider->provideByErgonodeKey('some_key', $this->contextMock);

        $this->assertEquals(0, $output->count());
    }

    private function mockRepositoryResult($result): void
    {
        $this->mappingRepositoryMock->expects($this->once())
            ->method('search')
            ->willReturn(
                $this->createConfiguredMock(EntitySearchResult::class, [
                    'getEntities' => $result,
                ])
            );
    }
}