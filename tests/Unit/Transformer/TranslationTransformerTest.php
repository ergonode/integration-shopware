<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests\Unit\Transformer;

use Ergonode\IntegrationShopware\Tests\Fixture\GqlAttributeResponse;
use Ergonode\IntegrationShopware\Transformer\TranslationTransformer;
use PHPUnit\Framework\TestCase;

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
            [
                [
                    [
                        'inherited' => false,
                        'language' => 'pl_PL',
                        '__typename' => 'NumericAttributeValue',
                        'value_numeric' => 666,
                    ],
                    [
                        'inherited' => false,
                        'language' => 'de_DE',
                        '__typename' => 'NumericAttributeValue',
                        'value_numeric' => 1234,
                    ],
                ],
                'number_number',
                [
                    'pl-PL' => [
                        'number_number' => 666,
                    ],
                    'de-DE' => [
                        'number_number' => 1234,
                    ],
                ],
            ],
        ];
    }
}
