<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Unit\Modules\Attribute\Api;

use GraphQL\Results;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Strix\Ergonode\Modules\Attribute\Api\AttributeStreamResultsProxy;
use Strix\Ergonode\Enum\AttributeTypesEnum;
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
     * @dataProvider filterByAttributeTypesDataProvider
     */
    public function testFilterByAttributeTypesMethod(array $requestedTypes, array $expectedOutput)
    {
        $output = $this->results->filterByAttributeTypes($requestedTypes);

        $this->assertSame($expectedOutput, array_values($output->getEdges()));
    }

    /**
     * @dataProvider filterByCodesDataProvider
     */
    public function testFilterByCodesMethod(array $requestedCodes, array $expectedOutput)
    {
        $output = $this->results->filterByCodes($requestedCodes);

        $this->assertSame($expectedOutput, array_values($output->getEdges()));
    }

    public function filterByAttributeTypesDataProvider(): array
    {
        return [
            [
                [],
                [],
            ],
            [
                [AttributeTypesEnum::NUMERIC],
                [
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][0],
                ],
            ],
            [
                [AttributeTypesEnum::SELECT],
                [
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][4],
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][5],
                ],
            ],
            [
                [AttributeTypesEnum::SELECT, AttributeTypesEnum::PRICE],
                [
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][3],
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][4],
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][5],
                ],
            ],
        ];
    }

    public function filterByCodesDataProvider(): array
    {
        return [
            [
                [],
                [],
            ],
            [
                ['stock'],
                [
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][0],
                ],
            ],
            [
                ['stock', 'name'],
                [
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][0],
                    GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][1],
                ],
            ],
        ];
    }
}
