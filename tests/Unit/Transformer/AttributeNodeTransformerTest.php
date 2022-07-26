<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests\Unit\Transformer;

use Ergonode\IntegrationShopware\DTO\PropertyGroupTransformationDTO;
use Ergonode\IntegrationShopware\Entity\ErgonodeMappingExtension\ErgonodeMappingExtensionEntity;
use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Ergonode\IntegrationShopware\Extension\PropertyGroup\PropertyGroupExtension;
use Ergonode\IntegrationShopware\Extension\PropertyGroupOption\PropertyGroupOptionExtension;
use Ergonode\IntegrationShopware\Provider\PropertyGroupProvider;
use Ergonode\IntegrationShopware\Tests\Fixture\GqlAttributeResponse;
use Ergonode\IntegrationShopware\Transformer\PropertyGroupTransformer;
use Ergonode\IntegrationShopware\Transformer\TranslationTransformer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionCollection;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class AttributeNodeTransformerTest extends TestCase
{
    private PropertyGroupTransformer $transformer;

    /**
     * @var MockObject|PropertyGroupProvider
     */
    private PropertyGroupProvider $propertyGroupProviderMock;

    /**
     * @var MockObject|TranslationTransformer
     */
    private TranslationTransformer $translationTransformerMock;

    protected function setUp(): void
    {
        $this->propertyGroupProviderMock = $this->createMock(PropertyGroupProvider::class);
        $this->translationTransformerMock = $this->createMock(TranslationTransformer::class);
        $this->contextMock = $this->createMock(Context::class);

        $this->transformer = new PropertyGroupTransformer(
            $this->propertyGroupProviderMock,
            $this->translationTransformerMock
        );
    }

    /**
     * @dataProvider bindingAttributeDataProvider
     */
    public function testTransformNodeMethod(array $nodeInput, ?PropertyGroupEntity $providerReturnValue, array $expectedOutput)
    {
        $this->mockTranslationTransformation(2 + count($nodeInput['options']));
        $this->mockPropertyGroupProvider($providerReturnValue);

        $output = $this->transformer->transformAttributeNode(
            new PropertyGroupTransformationDTO($nodeInput),
            $this->contextMock
        );

        $this->assertSame($expectedOutput, $output->getPropertyGroupPayload());
    }

    public function bindingAttributeDataProvider(): array
    {
        return [
            'totally_new_property_group_and_options' => [
                GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][4]['node'],
                null,
                [
                    'id' => null,
                    'displayType' => PropertyGroupDefinition::DISPLAY_TYPE_TEXT,
                    'sortingType' => PropertyGroupDefinition::SORTING_TYPE_ALPHANUMERIC,
                    'name' => 'color',
                    'options' => [
                        [
                            'id' => null,
                            'name' => 'black',
                            'translations' => [
                                'pl-PL' => [
                                    'name' => 'name_pl',
                                ],
                                'en-US' => [
                                    'name' => 'name_en',
                                ],
                            ],
                            'extensions' => [
                                AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                    'id' => null,
                                    'code' => 'color_black',
                                    'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                                ],
                            ],
                        ],
                        [
                            'id' => null,
                            'name' => 'white',
                            'translations' => [
                                'pl-PL' => [
                                    'name' => 'name_pl',
                                ],
                                'en-US' => [
                                    'name' => 'name_en',
                                ],
                            ],
                            'extensions' => [
                                AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                    'id' => null,
                                    'code' => 'color_white',
                                    'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                                ],
                            ],
                        ],
                        [
                            'id' => null,
                            'name' => 'blue',
                            'translations' => [
                                'pl-PL' => [
                                    'name' => 'name_pl',
                                ],
                                'en-US' => [
                                    'name' => 'name_en',
                                ],
                            ],
                            'extensions' => [
                                AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                    'id' => null,
                                    'code' => 'color_blue',
                                    'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                                ],
                            ],
                        ],
                        [
                            'id' => null,
                            'name' => 'violet',
                            'translations' => [
                                'pl-PL' => [
                                    'name' => 'name_pl',
                                ],
                                'en-US' => [
                                    'name' => 'name_en',
                                ],
                            ],
                            'extensions' => [
                                AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                    'id' => null,
                                    'code' => 'color_violet',
                                    'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                                ],
                            ],
                        ],
                    ],
                    'translations' => [
                        'pl-PL' => [
                            'name' => 'name_pl',
                            'description' => 'description_pl',
                        ],
                        'en-US' => [
                            'name' => 'name_en',
                            'description' => 'description_en',
                        ],
                    ],
                    'extensions' => [
                        AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                            'id' => null,
                            'code' => 'color',
                            'type' => PropertyGroupExtension::ERGONODE_TYPE,
                        ],
                    ],
                ],
            ],
            'extension_with_wrong_type' => [
                GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][4]['node'],
                $this->createConfiguredMock(PropertyGroupEntity::class, [
                    'getId' => '9',
                    'getExtension' => $this->createMock(Entity::class),
                ]),
                [
                    'id' => null,
                    'displayType' => PropertyGroupDefinition::DISPLAY_TYPE_TEXT,
                    'sortingType' => PropertyGroupDefinition::SORTING_TYPE_ALPHANUMERIC,
                    'name' => 'color',
                    'options' => [
                        [
                            'id' => null,
                            'name' => 'black',
                            'translations' => [
                                'pl-PL' => [
                                    'name' => 'name_pl',
                                ],
                                'en-US' => [
                                    'name' => 'name_en',
                                ],
                            ],
                            'extensions' => [
                                AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                    'id' => null,
                                    'code' => 'color_black',
                                    'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                                ],
                            ],
                        ],
                        [
                            'id' => null,
                            'name' => 'white',
                            'translations' => [
                                'pl-PL' => [
                                    'name' => 'name_pl',
                                ],
                                'en-US' => [
                                    'name' => 'name_en',
                                ],
                            ],
                            'extensions' => [
                                AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                    'id' => null,
                                    'code' => 'color_white',
                                    'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                                ],
                            ],
                        ],
                        [
                            'id' => null,
                            'name' => 'blue',
                            'translations' => [
                                'pl-PL' => [
                                    'name' => 'name_pl',
                                ],
                                'en-US' => [
                                    'name' => 'name_en',
                                ],
                            ],
                            'extensions' => [
                                AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                    'id' => null,
                                    'code' => 'color_blue',
                                    'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                                ],
                            ],
                        ],
                        [
                            'id' => null,
                            'name' => 'violet',
                            'translations' => [
                                'pl-PL' => [
                                    'name' => 'name_pl',
                                ],
                                'en-US' => [
                                    'name' => 'name_en',
                                ],
                            ],
                            'extensions' => [
                                AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                    'id' => null,
                                    'code' => 'color_violet',
                                    'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                                ],
                            ],
                        ],
                    ],
                    'translations' => [
                        'pl-PL' => [
                            'name' => 'name_pl',
                            'description' => 'description_pl',
                        ],
                        'en-US' => [
                            'name' => 'name_en',
                            'description' => 'description_en',
                        ],
                    ],
                    'extensions' => [
                        AbstractErgonodeMappingExtension::EXTENSION_NAME => array(
                            'id' => null,
                            'code' => 'color',
                            'type' => PropertyGroupExtension::ERGONODE_TYPE,
                        ),
                    ],
                ],
            ],
            'property_group_exists_options_not' => [
                GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][5]['node'],
                $this->createConfiguredMock(PropertyGroupEntity::class, [
                    'getId' => '9',
                    'getExtension' => $this->createConfiguredMock(ErgonodeMappingExtensionEntity::class, [
                        'getId' => '99',
                    ]),
                    'getOptions' => new PropertyGroupOptionCollection(),
                ]),
                [
                    'id' => null,
                    'displayType' => PropertyGroupDefinition::DISPLAY_TYPE_TEXT,
                    'sortingType' => PropertyGroupDefinition::SORTING_TYPE_ALPHANUMERIC,
                    'name' => 'size',
                    'options' => [
                        [
                            'id' => null,
                            'name' => 's',
                            'translations' => [
                                'pl-PL' => [
                                    'name' => 'name_pl',
                                ],
                                'en-US' => [
                                    'name' => 'name_en',
                                ],
                            ],
                            'extensions' => [
                                AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                    'id' => null,
                                    'code' => 'size_s',
                                    'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                                ],
                            ],
                        ],
                        [
                            'id' => null,
                            'name' => 'm',
                            'translations' => [
                                'pl-PL' => [
                                    'name' => 'name_pl',
                                ],
                                'en-US' => [
                                    'name' => 'name_en',
                                ],
                            ],
                            'extensions' => [
                                AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                    'id' => null,
                                    'code' => 'size_m',
                                    'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                                ],
                            ],
                        ],
                        [
                            'id' => null,
                            'name' => 'l',
                            'translations' => [
                                'pl-PL' => [
                                    'name' => 'name_pl',
                                ],
                                'en-US' => [
                                    'name' => 'name_en',
                                ],
                            ],
                            'extensions' => [
                                AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                    'id' => null,
                                    'code' => 'size_l',
                                    'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                                ],
                            ],
                        ],
                        [
                            'id' => null,
                            'name' => 'xl',
                            'translations' => [
                                'pl-PL' => [
                                    'name' => 'name_pl',
                                ],
                                'en-US' => [
                                    'name' => 'name_en',
                                ],
                            ],
                            'extensions' => [
                                AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                    'id' => null,
                                    'code' => 'size_xl',
                                    'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                                ],
                            ],
                        ],
                    ],
                    'translations' => [
                        'pl-PL' => [
                            'name' => 'name_pl',
                            'description' => 'description_pl',
                        ],
                        'en-US' => [
                            'name' => 'name_en',
                            'description' => 'description_en',
                        ],
                    ],
                    'extensions' => [
                        AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                            'id' => null,
                            'code' => 'size',
                            'type' => PropertyGroupExtension::ERGONODE_TYPE,
                        ],
                    ],
                ],
            ],
            'property_group_exists_options_too' => [
                GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][5]['node'],
                $this->createConfiguredMock(PropertyGroupEntity::class, [
                    'getId' => '9',
                    'getExtension' => $this->createConfiguredMock(ErgonodeMappingExtensionEntity::class, [
                        'getId' => '99',
                        'getCode' => 'size',
                    ]),
                    'getOptions' => new PropertyGroupOptionCollection([
                        '1' => $this->createConfiguredMock(PropertyGroupOptionEntity::class, [
                            'getUniqueIdentifier' => '1',
                            'getId' => '1',
                            'getExtension' => $this->createConfiguredMock(ErgonodeMappingExtensionEntity::class, [
                                'getId' => '11',
                                'getCode' => 'size_s',
                            ]),
                        ]),
                        '2' => $this->createConfiguredMock(PropertyGroupOptionEntity::class, [
                            'getUniqueIdentifier' => '2',
                            'getId' => '2',
                            'getExtension' => $this->createConfiguredMock(ErgonodeMappingExtensionEntity::class, [
                                'getId' => '22',
                                'getCode' => 'size_m',
                            ]),
                        ]),
                        '3' => $this->createConfiguredMock(PropertyGroupOptionEntity::class, [
                            'getUniqueIdentifier' => '3',
                            'getId' => '3',
                            'getExtension' => $this->createConfiguredMock(ErgonodeMappingExtensionEntity::class, [
                                'getId' => '33',
                                'getCode' => 'size_l',
                            ]),
                        ]),
                        '4' => $this->createConfiguredMock(PropertyGroupOptionEntity::class, [
                            'getUniqueIdentifier' => '4',
                            'getId' => '4',
                            'getExtension' => $this->createConfiguredMock(ErgonodeMappingExtensionEntity::class, [
                                'getId' => '44',
                                'getCode' => 'size_xl',
                            ]),
                        ]),
                    ]),
                ]),
                [
                    'id' => null,
                    'displayType' => PropertyGroupDefinition::DISPLAY_TYPE_TEXT,
                    'sortingType' => PropertyGroupDefinition::SORTING_TYPE_ALPHANUMERIC,
                    'name' => 'size',
                    'options' => [
                        [
                            'id' => null,
                            'name' => 's',
                            'translations' => [
                                'pl-PL' => [
                                    'name' => 'name_pl',
                                ],
                                'en-US' => [
                                    'name' => 'name_en',
                                ],
                            ],
                            'extensions' => [
                                AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                    'id' => null,
                                    'code' => 'size_s',
                                    'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                                ],
                            ],
                        ],
                        [
                            'id' => null,
                            'name' => 'm',
                            'translations' => [
                                'pl-PL' => [
                                    'name' => 'name_pl',
                                ],
                                'en-US' => [
                                    'name' => 'name_en',
                                ],
                            ],
                            'extensions' => [
                                AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                    'id' => null,
                                    'code' => 'size_m',
                                    'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                                ],
                            ],
                        ],
                        [
                            'id' => null,
                            'name' => 'l',
                            'translations' => [
                                'pl-PL' => [
                                    'name' => 'name_pl',
                                ],
                                'en-US' => [
                                    'name' => 'name_en',
                                ],
                            ],
                            'extensions' => [
                                AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                    'id' => null,
                                    'code' => 'size_l',
                                    'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                                ],
                            ],
                        ],
                        [
                            'id' => null,
                            'name' => 'xl',
                            'translations' => [
                                'pl-PL' => [
                                    'name' => 'name_pl',
                                ],
                                'en-US' => [
                                    'name' => 'name_en',
                                ],
                            ],
                            'extensions' => [
                                AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                    'id' => null,
                                    'code' => 'size_xl',
                                    'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                                ],
                            ],
                        ],
                    ],
                    'translations' => [
                        'pl-PL' => [
                            'name' => 'name_pl',
                            'description' => 'description_pl',
                        ],
                        'en-US' => [
                            'name' => 'name_en',
                            'description' => 'description_en',
                        ],
                    ],
                    'extensions' => [
                        AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                            'id' => null,
                            'code' => 'size',
                            'type' => PropertyGroupExtension::ERGONODE_TYPE,
                        ],
                    ],
                ],
            ],
            'property_group_exists_one_of_options_not' => [
                GqlAttributeResponse::attributeStreamResponse()['data']['attributeStream']['edges'][5]['node'],
                $this->createConfiguredMock(PropertyGroupEntity::class, [
                    'getId' => '9',
                    'getExtension' => $this->createConfiguredMock(ErgonodeMappingExtensionEntity::class, [
                        'getId' => '99',
                        'getCode' => 'size',
                    ]),
                    'getOptions' => new PropertyGroupOptionCollection([
                        '1' => $this->createConfiguredMock(PropertyGroupOptionEntity::class, [
                            'getUniqueIdentifier' => '1',
                            'getId' => '1',
                            'getExtension' => $this->createConfiguredMock(ErgonodeMappingExtensionEntity::class, [
                                'getId' => '11',
                                'getCode' => 'size_s',
                            ]),
                        ]),
                        '2' => $this->createConfiguredMock(PropertyGroupOptionEntity::class, [
                            'getUniqueIdentifier' => '2',
                            'getId' => '2',
                            'getExtension' => $this->createConfiguredMock(ErgonodeMappingExtensionEntity::class, [
                                'getId' => '22',
                                'getCode' => 'size_m',
                            ]),
                        ]),
                        '3' => $this->createConfiguredMock(PropertyGroupOptionEntity::class, [
                            'getUniqueIdentifier' => '3',
                            'getId' => '3',
                            'getExtension' => $this->createConfiguredMock(ErgonodeMappingExtensionEntity::class, [
                                'getId' => '33',
                                'getCode' => 'size_l',
                            ]),
                        ]),
                    ]),
                ]),
                [
                    'id' => null,
                    'displayType' => PropertyGroupDefinition::DISPLAY_TYPE_TEXT,
                    'sortingType' => PropertyGroupDefinition::SORTING_TYPE_ALPHANUMERIC,
                    'name' => 'size',
                    'options' => [
                        [
                            'id' => null,
                            'name' => 's',
                            'translations' => [
                                'pl-PL' => [
                                    'name' => 'name_pl',
                                ],
                                'en-US' => [
                                    'name' => 'name_en',
                                ],
                            ],
                            'extensions' => [
                                AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                    'id' => null,
                                    'code' => 'size_s',
                                    'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                                ],
                            ],
                        ],
                        [
                            'id' => null,
                            'name' => 'm',
                            'translations' => [
                                'pl-PL' => [
                                    'name' => 'name_pl',
                                ],
                                'en-US' => [
                                    'name' => 'name_en',
                                ],
                            ],
                            'extensions' => [
                                AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                    'id' => null,
                                    'code' => 'size_m',
                                    'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                                ],
                            ],
                        ],
                        [
                            'id' => null,
                            'name' => 'l',
                            'translations' => [
                                'pl-PL' => [
                                    'name' => 'name_pl',
                                ],
                                'en-US' => [
                                    'name' => 'name_en',
                                ],
                            ],
                            'extensions' => [
                                AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                    'id' => null,
                                    'code' => 'size_l',
                                    'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                                ],
                            ],
                        ],
                        [
                            'id' => null,
                            'name' => 'xl',
                            'translations' => [
                                'pl-PL' => [
                                    'name' => 'name_pl',
                                ],
                                'en-US' => [
                                    'name' => 'name_en',
                                ],
                            ],
                            'extensions' => [
                                AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                    'id' => null,
                                    'code' => 'size_xl',
                                    'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                                ],
                            ],
                        ],
                    ],
                    'translations' => [
                        'pl-PL' => [
                            'name' => 'name_pl',
                            'description' => 'description_pl',
                        ],
                        'en-US' => [
                            'name' => 'name_en',
                            'description' => 'description_en',
                        ],
                    ],
                    'extensions' => [
                        AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                            'id' => null,
                            'code' => 'size',
                            'type' => PropertyGroupExtension::ERGONODE_TYPE,
                        ],
                    ],
                ],
            ],
        ];
    }

    private function mockTranslationTransformation(int $callCount): void
    {
        $this->translationTransformerMock
            ->expects($this->exactly($callCount))
            ->method('transform')
            ->willReturnCallback(fn(array $ergonodeTranslation, string $shopwareKey) => $this->getTranslation($shopwareKey));
    }

    private function mockPropertyGroupProvider(?PropertyGroupEntity $returnValue): void
    {
        $this->propertyGroupProviderMock
            ->method('getPropertyGroupByMapping')
            ->willReturn($returnValue);
    }

    private function getTranslation(string $key): array
    {
        return [
            'pl-PL' => [
                $key => "{$key}_pl",
            ],
            'en-US' => [
                $key => "{$key}_en",
            ],
        ];
    }
}
