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
     * @dataProvider getAttributesOfTypesDataProvider
     */
    public function testGetAttributesOfTypesMethod(array $requestedTypes, array $expectedOutput)
    {
        $output = $this->results->filterByAttributeTypes($requestedTypes);

        $this->assertSame($expectedOutput, array_values($output->getEdges()));
    }

    public function getAttributesOfTypesDataProvider(): array
    {
        return [
            [
                [],
                [],
            ],
            [
                [AttributeTypes::SELECT],
                [
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][5]
                ],
            ],
            [
                [AttributeTypes::SELECT, AttributeTypes::PRICE],
                [
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][3],
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][5]
                ],
            ],
        ];
    }
}
