<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests\Unit\Transformer;

use Ergonode\IntegrationShopware\Provider\LanguageProvider;
use Ergonode\IntegrationShopware\Tests\Fixture\GqlAttributeResponse;
use Ergonode\IntegrationShopware\Transformer\TranslationTransformer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;

class TranslationTransformerTest extends TestCase
{
    private TranslationTransformer $transformer;

    /**
     * @var MockObject|Context
     */
    private Context $contextMock;

    /**
     * @var MockObject|LanguageProvider
     */
    private LanguageProvider $languageProviderMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->languageProviderMock = $this->createMock(LanguageProvider::class);
        $this->languageProviderMock->method('getDefaultLanguageLocale')
            ->willReturn('en-GB');

        $this->transformer = new TranslationTransformer(
            $this->languageProviderMock
        );
    }

    /**
     * @dataProvider ergonodeTranslationDataProvider
     */
    public function testTransformMethod(array $ergonodeTranslationInput, ?string $shopwareKeyInput, array $expectedOutput)
    {
        $output = $this->transformer->transform($ergonodeTranslationInput, $shopwareKeyInput);

        $this->assertSame($expectedOutput, $output);
    }

    /**
     * @dataProvider defaultLocaleTestProvider
     */
    public function testTransformDefaultLocale($input, $expectedOutput): void
    {
        $result = $this->transformer->transformDefaultLocale($input, $this->contextMock);

        $this->assertEquals($expectedOutput, $result);
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
                        '__typename' => 'NumericAttributeValueTranslation',
                        'value_numeric' => 666,
                    ],
                    [
                        'inherited' => false,
                        'language' => 'de_DE',
                        '__typename' => 'NumericAttributeValueTranslation',
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

    public function defaultLocaleTestProvider(): iterable
    {
        yield [
            [
                [
                    'language' => 'en_GB',
                    '__typename' => 'TextAttributeValueTranslation',
                    'value_string' => ['value']
                ]
            ],
            [
                'value'
            ]
        ];
    }
}
