<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests\Unit\Provider;

use Ergonode\IntegrationShopware\Api\AttributeDeletedStreamResultsProxy;
use Ergonode\IntegrationShopware\Api\AttributeStreamResultsProxy;
use Ergonode\IntegrationShopware\Api\Client\CachedErgonodeGqlClient;
use Ergonode\IntegrationShopware\Api\ProductStreamResultsProxy;
use Ergonode\IntegrationShopware\Provider\ErgonodeAttributeProvider;
use Ergonode\IntegrationShopware\QueryBuilder\AttributeQueryBuilder;
use Ergonode\IntegrationShopware\Tests\Fixture\GqlAttributeResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ErgonodeAttributeProviderTest extends TestCase
{
    private ErgonodeAttributeProvider $provider;

    /**
     * @var MockObject|AttributeQueryBuilder
     */
    private $attributeQueryBuilderMock;

    /**
     * @var MockObject|CachedErgonodeGqlClient
     */
    private $ergonodeGqlClientMock;

    protected function setUp(): void
    {
        $this->attributeQueryBuilderMock = $this->createMock(AttributeQueryBuilder::class);
        $this->ergonodeGqlClientMock = $this->createMock(CachedErgonodeGqlClient::class);

        $this->provider = new ErgonodeAttributeProvider(
            $this->attributeQueryBuilderMock,
            $this->ergonodeGqlClientMock
        );
    }

    /**
     * @dataProvider attributesOutputDataProvider
     */
    public function testProvideProductAttributesMethod(int $responsePages, array $response, string $proxyClass, array $expectedOutput)
    {
        $this->mockGqlResults($responsePages, $response, $proxyClass);

        $generator = $this->provider->provideProductAttributes();

        foreach ($generator as $result) {
            $this->assertEquals($expectedOutput, array_values($result->getEdges()));
        }
    }

    public function testIfProvideProductAttributesMethodWillFailWhenApiRespondsWithWrongResults()
    {
        $this->mockGqlResults(
            1,
            GqlAttributeResponse::attributeStreamResponse(),
            ProductStreamResultsProxy::class,
            'attributeStream'
        );

        $generator = $this->provider->provideProductAttributes();

        foreach ($generator as $result) {
            $this->assertEquals(null, $result);
        }
    }

    /**
     * @dataProvider deletedAttributesOutputDataProvider
     */
    public function testProvideDeletedAttributesMethod(int $responsePages, array $response, string $proxyClass, array $expectedOutput)
    {
        $this->mockGqlResults($responsePages, $response, $proxyClass);

        $generator = $this->provider->provideDeletedAttributes();

        foreach ($generator as $result) {
            $this->assertSame($expectedOutput, array_values($result->getEdges()));
        }
    }

    public function testIfProvideDeletedAttributesMethodWillFailWhenApiRespondsWithWrongResults()
    {
        $this->mockGqlResults(
            1,
            GqlAttributeResponse::attributeDeletedStreamResponse(),
            ProductStreamResultsProxy::class,
            'attributeDeletedStream'
        );

        $generator = $this->provider->provideDeletedAttributes();

        foreach ($generator as $result) {
            $this->assertEquals(null, $result);
        }
    }

    public function attributesOutputDataProvider(): array
    {
        return [
            [
                1,
                GqlAttributeResponse::attributeStreamResponse(),
                AttributeStreamResultsProxy::class,
                GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'],
            ],
            [
                3,
                GqlAttributeResponse::attributeStreamResponse(),
                AttributeStreamResultsProxy::class,
                GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'],
            ],
        ];
    }

    public function deletedAttributesOutputDataProvider(): array
    {
        return [
            [
                1,
                GqlAttributeResponse::attributeDeletedStreamResponse(),
                AttributeDeletedStreamResultsProxy::class,
                GqlAttributeResponse::attributeDeletedStreamResponse()['data']['attributeDeletedStream']['edges'],
            ],
            [
                3,
                GqlAttributeResponse::attributeDeletedStreamResponse(),
                AttributeDeletedStreamResultsProxy::class,
                GqlAttributeResponse::attributeDeletedStreamResponse()['data']['attributeDeletedStream']['edges'],
            ],
        ];
    }

    private function mockGqlResults(int $responsePages, array $response, string $proxyClass = AttributeStreamResultsProxy::class, string $mainFieldKey = ''): void
    {
        $returns = [];

        $mainFieldKey = empty($mainFieldKey) ? $proxyClass::MAIN_FIELD : $mainFieldKey;

        $response['data'][$mainFieldKey]['totalCount'] = $responsePages * count($response['data'][$mainFieldKey]['edges']);

        for ($i = 0; $i < $responsePages; $i++) {
            $methods = [
                'getEdges' => $response['data'][$mainFieldKey]['edges'],
                'hasNextPage' => $i !== $responsePages - 1,
            ];

            $returns[] = $this->createConfiguredMock($proxyClass, $methods);
        }

        $this->ergonodeGqlClientMock->expects($this->exactly($responsePages))
            ->method('query')
            ->willReturnOnConsecutiveCalls(...$returns);
    }
}
