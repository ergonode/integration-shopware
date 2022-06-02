<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Unit\Modules\Attribute\Provider;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Strix\Ergonode\Modules\Attribute\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingCollection;
use Strix\Ergonode\Modules\Attribute\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingEntity;
use Strix\Ergonode\Modules\Attribute\Provider\AttributeMappingProvider;
use Strix\Ergonode\Tests\Fixture\ErgonodeAttributeMappingFixture;

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

        $output = $this->provider->provideByErgonodeKey('some_key', $this->contextMock);

        $rawOutput = $output->map(fn(ErgonodeAttributeMappingEntity $entity) => $entity->getShopwareKey());

        $this->assertEquals(['swKey1', 'swKey2'], array_values($rawOutput));
    }

    public function testProviderWhenRepositoryReturnsWrongType()
    {
        $this->mockRepositoryResult(new ProductCollection());

        $output = $this->provider->provideByErgonodeKey('some_key', $this->contextMock);

        $this->assertEquals(null, $output);
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
