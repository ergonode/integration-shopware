<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Unit\Modules\Attribute\Provider;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Strix\Ergonode\Api\Client\ErgonodeGqlClient;
use Strix\Ergonode\Api\GqlResponse;
use Strix\Ergonode\Modules\Attribute\Provider\ErgonodeAttributeProvider;
use Strix\Ergonode\Modules\Attribute\QueryBuilder\AttributeQueryBuilder;
use Strix\Ergonode\Tests\Fixture\GqlAttributeResponse;
use Symfony\Component\HttpFoundation\Response;

class ErgonodeAttributeProviderTest extends TestCase
{
    private ErgonodeAttributeProvider $provider;

    /**
     * @var MockObject|AttributeQueryBuilder
     */
    private $attributeQueryBuilderMock;

    /**
     * @var MockObject|ErgonodeGqlClient
     */
    private $ergonodeGqlClientMock;

    protected function setUp(): void
    {
        $this->attributeQueryBuilderMock = $this->createMock(AttributeQueryBuilder::class);
        $this->ergonodeGqlClientMock = $this->createMock(ErgonodeGqlClient::class);

        $this->provider = new ErgonodeAttributeProvider(
            $this->attributeQueryBuilderMock,
            $this->ergonodeGqlClientMock
        );
    }

    /**
     * @dataProvider attributesOutputDataProvider
     */
    public function testProvideProductAttributesMethod(int $statusCode, int $responsePages, array $response, array $expectedOutput)
    {
        $this->mockGqlResponse($statusCode, $responsePages, GqlAttributeResponse::attributeStreamResponse());

        $result = $this->provider->provideProductAttributes();

        $this->assertEquals($expectedOutput, $result);
    }

    public function attributesOutputDataProvider(): array
    {
        return [
            [
                Response::HTTP_OK,
                1,
                GqlAttributeResponse::attributeStreamResponse(),
                GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'],

            ],
            [
                Response::HTTP_OK,
                3,
                GqlAttributeResponse::attributeStreamResponse(),
                array_merge(
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'],
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'],
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges']
                ),
            ],
            [
                Response::HTTP_BAD_REQUEST,
                1,
                GqlAttributeResponse::attributeStreamResponse(),
                [],
            ],
        ];
    }

    private function mockGqlResponse(int $statusCode, int $responsePages, array $response): void
    {
        $returns = [];
        $response['data']['attributeStream']['pageInfo']['hasNextPage'] = true;

        for ($i = 0; $i < $responsePages; $i++) {
            if ($i === $responsePages - 1) {
                $response['data']['attributeStream']['pageInfo']['hasNextPage'] = false;
            }

            $returns[] = $this->createConfiguredMock(GqlResponse::class, [
                'isOk' => $statusCode === Response::HTTP_OK,
                'getData' => $response['data'],
            ]);
        }

        $this->ergonodeGqlClientMock->expects($this->exactly($responsePages))
            ->method('query')
            ->willReturnOnConsecutiveCalls(...$returns);
    }
}
