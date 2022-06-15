<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Unit\Modules\Attribute\Api;

use GraphQL\Results;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Strix\Ergonode\Modules\Attribute\Api\AttributeStreamResultsProxy;
use Strix\Ergonode\Modules\Attribute\Enum\AttributeTypes;
use Strix\Ergonode\Tests\Fixture\GqlAttributeResponse;

class AttributeStreamResultsProxyTest extends TestCase
{
    private AttributeStreamResultsProxy $results;

    protected function setUp(): void
    {
        $response = new Response(200, [], json_encode(GqlAttributeResponse::attributeStreamResponse()));

        $this->results = new AttributeStreamResultsProxy(new Results($response, true));
    }

    /**
     * @dataProvider filterAttributesOfTypesDataProvider
     */
    public function testFilterAttributesOfTypesMethod(array $requestedTypes, array $expectedOutput)
    {
        $output = $this->results->filterByAttributeTypes($requestedTypes);

        $this->assertSame($expectedOutput, array_values($output->getEdges()));
    }

    public function filterAttributesOfTypesDataProvider(): array
    {
        return [
            [
                [],
                [],
            ],
            [
                [AttributeTypes::NUMERIC],
                [
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][0],
                ],
            ],
            [
                [AttributeTypes::SELECT],
                [
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][4],
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][5],
                ],
            ],
            [
                [AttributeTypes::SELECT, AttributeTypes::PRICE],
                [
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][3],
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][4],
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][5],
                ],
            ],
        ];
    }
}
