<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Unit\Api\Client;

use GraphQL\Query;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Strix\Ergonode\Api\Client\ErgonodeGqlClient;
use Strix\Ergonode\Api\GqlResponse;
use Strix\Ergonode\Tests\Fixture\GqlQueryFixture;

class ErgonodeGqlClientTest extends TestCase
{
    /**
     * @var Client|MockObject
     */
    private $guzzleClientMock;

    private ErgonodeGqlClient $gqlClient;

    protected function setUp(): void
    {
        $this->guzzleClientMock = $this->createMock(Client::class);

        $this->gqlClient = new ErgonodeGqlClient(
            $this->guzzleClientMock
        );
    }

    public function testSuccessQueryMethod()
    {
        $query = GqlQueryFixture::basicProductStreamQuery();

        $this->mockGuzzleRequest($query);

        $result = $this->gqlClient->query($query);

        $this->assertInstanceOf(GqlResponse::class, $result);
        $this->assertEquals(['productStream' => ['some' => 'data']], $result->getData());
    }

    public function testFailQueryMethod()
    {
        $query = GqlQueryFixture::basicProductStreamQuery();

        $this->mockGuzzleRequest($query)
            ->willThrowException($this->createMock(GuzzleException::class));

        $result = $this->gqlClient->query($query);

        $this->assertNull($result);
    }

    private function mockGuzzleRequest(Query $query): InvocationMocker
    {
        return $this->guzzleClientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'api/graphql/',
                [
                    'json' => [
                        'query' => strval($query),
                    ],
                ]
            )
            ->willReturn(
                $this->createConfiguredMock(ResponseInterface::class, [
                    'getBody' => Utils::streamFor('{"data": {"productStream": {"some": "data"}}}'),
                ])
            );
    }
}
