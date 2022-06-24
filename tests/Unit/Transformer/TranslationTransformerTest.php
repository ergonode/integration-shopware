<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Unit\Transformer;

use PHPUnit\Framework\TestCase;
use Strix\Ergonode\Tests\Fixture\GqlAttributeResponse;
use Strix\Ergonode\Transformer\TranslationTransformer;

class TranslationTransformerTest extends TestCase
{
    private TranslationTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new TranslationTransformer();
    }

    /**
     * @dataProvider ergonodeTranslationDataProvider
     */
    public function testTransformMethod(array $ergonodeTranslationInput, ?string $shopwareKeyInput, array $expectedOutput)
    {
        $output = $this->transformer->transform($ergonodeTranslationInput, $shopwareKeyInput);

        $this->assertSame($expectedOutput, $output);
    }

    public function ergonodeTranslationDataProvider(): array
    {
        return [
            [
                [],
                'name',
                [],
            ],
            [
                GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][4]['node']['label'],
                'custom_shopware_field_name',
                [
                    'pl-PL' => [
                        'custom_shopware_field_name' => 'kolor',
                    ],
                    'en-US' => [
                        'custom_shopware_field_name' => 'color',
                    ],
                ],
            ],
            [
                GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][5]['node']['label'],
                'name',
                [
                    'pl-PL' => [
                        'name' => 'rozmiar',
                    ],
                    'en-US' => [
                        'name' => 'size',
                    ],
                ],
            ],
            [
                GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][5]['node']['label'],
                null,
                [
                    'pl-PL' => 'rozmiar',
                    'en-US' => 'size',
                ],
            ],
        ];
    }
}
