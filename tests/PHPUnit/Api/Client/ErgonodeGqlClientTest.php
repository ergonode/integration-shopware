<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests\PHPUnit\Api\Client;

use Ergonode\IntegrationShopware\Api\AttributeStreamResultsProxy;
use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClient;
use Ergonode\IntegrationShopware\Tests\PHPUnit\Fixture\GqlQueryFixture;
use GraphQL\Client;
use GraphQL\Query;
use GraphQL\Results;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;

class ErgonodeGqlClientTest extends TestCase
{
    /**
     * @var Client|MockObject
     */
    private $gqlClientMock;

    private ErgonodeGqlClient $gqlClient;

    private LoggerInterface $testLogger;

    protected function setUp(): void
    {
        $this->gqlClientMock = $this->createMock(Client::class);
        $this->testLogger = new TestLogger();

        $this->gqlClient = new ErgonodeGqlClient(
            $this->gqlClientMock,
            $this->testLogger
        );
    }

    public function testSuccessQueryMethod()
    {
        $query = GqlQueryFixture::basicProductStreamQuery();

        $this->mockGqlRequest($query);

        $result = $this->gqlClient->query($query);

        $this->assertInstanceOf(Results::class, $result);
        $this->assertEquals(['attributeStream' => ['some' => 'data']], $result->getData());
    }

    public function testSuccessQueryMethodWhenProvidedResultsProxy()
    {
        $query = GqlQueryFixture::basicProductStreamQuery();

        $this->mockGqlRequest($query);

        $result = $this->gqlClient->query($query, AttributeStreamResultsProxy::class);

        $this->assertInstanceOf(AttributeStreamResultsProxy::class, $result);
        $this->assertEquals(['attributeStream' => ['some' => 'data']], $result->getData());
    }

    public function testFailQueryMethod()
    {
        $query = GqlQueryFixture::basicProductStreamQuery();

        $this->mockGqlRequest($query)
            ->willThrowException($this->createMock(ClientException::class));

        $result = $this->gqlClient->query($query);

        $this->assertNull($result);
    }

    private function mockGqlRequest(Query $query): InvocationMocker
    {
        return $this->gqlClientMock
            ->expects($this->once())
            ->method('runQuery')
            ->with($query)
            ->willReturn(
                $this->createConfiguredMock(Results::class, [
                    'getResults' => [
                        'data' => [
                            'attributeStream' => [
                                'some' => 'data',
                            ],
                        ],
                    ],
                    'getData' => [
                        'attributeStream' => [
                            'some' => 'data',
                        ],
                    ],
                    'getResponseObject' => $this->createConfiguredMock(ResponseInterface::class, [
                        'getBody' => Utils::streamFor('{"data": {"attributeStream": {"some":"data"}}}'),
                    ]),
                ])
            );
    }
}
