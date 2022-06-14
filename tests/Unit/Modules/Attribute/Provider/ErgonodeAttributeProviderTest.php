<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Unit\Modules\Attribute\Provider;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Strix\Ergonode\Api\Client\CachedErgonodeGqlClient;
use Strix\Ergonode\Modules\Attribute\Api\AttributeStreamResultsProxy;
use Strix\Ergonode\Modules\Attribute\Provider\ErgonodeAttributeProvider;
use Strix\Ergonode\Modules\Attribute\QueryBuilder\AttributeQueryBuilder;
use Strix\Ergonode\Modules\Product\Api\ProductStreamResultsProxy;
use Strix\Ergonode\Tests\Fixture\GqlAttributeResponse;

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
        $this->mockGqlResponse($responsePages, $response, $proxyClass);

        $result = $this->provider->provideProductAttributes();

        $this->assertEquals($expectedOutput, $result);
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
                array_merge(
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'],
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'],
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges']
                ),
            ],
            [
                1,
                GqlAttributeResponse::attributeStreamResponse(),
                ProductStreamResultsProxy::class,
                [],
            ],
        ];
    }

    private function mockGqlResponse(int $responsePages, array $response, string $proxyClass = AttributeStreamResultsProxy::class): void
    {
        $returns = [];

        for ($i = 0; $i < $responsePages; $i++) {
            $methods = [
                'getEdges' => $response['data']['attributeStream']['edges'],
                'hasNextPage' => $i !== $responsePages - 1,
            ];

            $returns[] = $this->createConfiguredMock($proxyClass, $methods);
        }

        $this->ergonodeGqlClientMock->expects($this->exactly($responsePages))
            ->method('query')
            ->willReturnOnConsecutiveCalls(...$returns);
    }
}
