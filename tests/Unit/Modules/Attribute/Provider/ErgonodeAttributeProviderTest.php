<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Unit\Modules\Attribute\Provider;

use GraphQL\Results;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Strix\Ergonode\Api\Client\CachedErgonodeGqlClient;
use Strix\Ergonode\Modules\Attribute\Api\AttributeStreamResultsProxy;
use Strix\Ergonode\Modules\Attribute\Provider\ErgonodeAttributeProvider;
use Strix\Ergonode\Modules\Attribute\QueryBuilder\AttributeQueryBuilder;
use Strix\Ergonode\Modules\Product\Api\ProductStreamResultsProxy;
use Strix\Ergonode\Tests\Fixture\GqlAttributeResponse;
use Strix\Ergonode\Tests\Fixture\GqlProductResponse;

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

        $result = $this->provider->provideProductAttributes();

        $this->assertEquals($expectedOutput, $result);
    }

    /**
     * @dataProvider bindingAttributesOutputDataProvider
     */
    public function testProvideBindingAttributesMethod(int $responsePages, array $response, string $proxyClass, array $expectedOutput)
    {
        $this->mockRealGqlResults($responsePages, $response, $proxyClass);

        $result = $this->provider->provideBindingAttributes();

        $this->assertEquals($expectedOutput, array_values($result->getEdges()));
    }

    public function testIfProvideBindingAttributesMethodWillFailWhenApiRespondsWithWrongResults()
    {
        $this->mockRealGqlResults(1, GqlProductResponse::productStreamResponse(), ProductStreamResultsProxy::class);

        $result = $this->provider->provideBindingAttributes();

        $this->assertEquals(null, $result);
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

    public function bindingAttributesOutputDataProvider(): array
    {
        return [
            [
                1,
                GqlAttributeResponse::attributeStreamResponse(),
                AttributeStreamResultsProxy::class,
                [
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][4],
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][5],
                ],
            ],
            [
                2,
                GqlAttributeResponse::attributeStreamResponse(),
                AttributeStreamResultsProxy::class,
                [
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][4],
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][5],
                ],
            ],
        ];
    }

    private function mockGqlResults(int $responsePages, array $response, string $proxyClass = AttributeStreamResultsProxy::class): void
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

    private function mockRealGqlResults(int $responsePages, array $response, string $proxyClass = AttributeStreamResultsProxy::class): void
    {
        $returns = [];

        for ($i = 0; $i < $responsePages; $i++) {
            $response['data']['attributeStream']['pageInfo']['hasNextPage'] = $i !== $responsePages - 1;
            $resultsMock = $this->createConfiguredMock(Results::class, [
                'getResults' => [], // just to pass is_array check
                'getResponseObject' => $this->createConfiguredMock(ResponseInterface::class, [
                    'getBody' => Utils::streamFor(json_encode($response)),
                ]),
            ]);

            $returns[] = new $proxyClass($resultsMock);
        }

        $this->ergonodeGqlClientMock->expects($this->exactly($responsePages))
            ->method('query')
            ->willReturnOnConsecutiveCalls(...$returns);
    }
}
