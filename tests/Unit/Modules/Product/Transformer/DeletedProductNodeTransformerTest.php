<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Unit\Modules\Product\Transformer;

use PHPUnit\Framework\TestCase;
use Strix\Ergonode\Modules\Product\Transformer\DeletedProductNodeTransformer;
use Strix\Ergonode\Tests\Fixtures\GqlProductResponse;

class DeletedProductNodeTransformerTest extends TestCase
{
    private DeletedProductNodeTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new DeletedProductNodeTransformer();
    }

    /**
     * @dataProvider responseDataProvider
     */
    public function testTransformNodeMethod(string $input, string $expectedOutput)
    {
        $output = $this->transformer->transformNode(['__value__' => $input]);

        $this->assertSame($expectedOutput, $output->getSku());
    }

    public function testTransformNodeMethodWhenEmptyInput()
    {
        $output = $this->transformer->transformNode([]);

        $this->assertSame(null, $output);
    }

    public function responseDataProvider(): array
    {
        return [
            [
                GqlProductResponse::deletedProductsResponse()['data']['productDeletedStream']['edges'][0]['node'],
                'skirt_001', // todo change to transformed array after removing structs
            ]
        ];
    }
}
