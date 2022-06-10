<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Unit\Modules\Product\Transformer;

use PHPUnit\Framework\TestCase;
use Strix\Ergonode\Modules\Product\Transformer\ProductNodeTransformer;
use Strix\Ergonode\Tests\Fixture\GqlProductResponse;

class ProductNodeTransformerTest extends TestCase
{
    private ProductNodeTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new ProductNodeTransformer();
    }

    /**
     * @dataProvider responseDataProvider
     */
    public function testTransformNodeMethod(array $input, $expectedOutput)
    {
        $output = $this->transformer->transformNode($input);

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
                GqlProductResponse::productStreamResponse()['data']['productStream']['edges'][3]['node'],
                'some_random_sku_4', // todo change to transformed array after removing structs
            ]
        ];
    }
}
