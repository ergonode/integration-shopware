<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Unit\Modules\Category\Transformer;

use PHPUnit\Framework\TestCase;
use Strix\Ergonode\Transformer\CategoryResponseTransformer;
use Strix\Ergonode\Tests\Fixture\GqlCategoryResponse;

class CategoryResponseTransformerTest extends TestCase
{
    private CategoryResponseTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new CategoryResponseTransformer();
    }

    /**
     * @dataProvider responseDataProvider
     */
    public function testTransformResponseMethod(array $input, int $expectedCount)
    {
        $result = $this->transformer->transformResponse($input);

        $this->assertEquals($expectedCount, $result->count());
    }

    public function responseDataProvider(): array
    {
        return [
            [
                GqlCategoryResponse::fullCategoryTreeResponse()['data']['categoryTree'],
                6,
            ],
            [
                GqlCategoryResponse::emptyCategoryTreeResponse()['data']['categoryTree'],
                0,
            ],
        ];
    }
}
