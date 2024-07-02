<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\QueryBuilder\Common;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use GraphQL\InlineFragment;
use GraphQL\Query;

class AttributesQuery
{
    public static function getAttributeFragment(): Query
    {
        return (new Query('attribute'))
            ->setSelectionSet([
                '__typename',
                'code',
                'scope',
                (new InlineFragment('DateAttribute'))
                    ->setSelectionSet([
                        new Query('code', AttributeTypesEnum::DATE),
                    ]),
                (new InlineFragment('FileAttribute'))
                    ->setSelectionSet([
                        new Query('code', AttributeTypesEnum::FILE),
                    ]),
                (new InlineFragment('GalleryAttribute'))
                    ->setSelectionSet([
                        new Query('code', AttributeTypesEnum::GALLERY),
                    ]),
                (new InlineFragment('ImageAttribute'))
                    ->setSelectionSet([
                        new Query('code', AttributeTypesEnum::IMAGE),
                    ]),
                (new InlineFragment('SelectAttribute'))
                    ->setSelectionSet([
                        new Query('code', AttributeTypesEnum::SELECT),
                    ]),
                (new InlineFragment('MultiSelectAttribute'))
                    ->setSelectionSet([
                        new Query('code', AttributeTypesEnum::MULTISELECT),
                    ]),
                (new InlineFragment('NumericAttribute'))
                    ->setSelectionSet([
                        new Query('code', AttributeTypesEnum::NUMERIC),
                    ]),
                (new InlineFragment('PriceAttribute'))
                    ->setSelectionSet([
                        new Query('code', AttributeTypesEnum::PRICE),
                        'currency',
                    ]),
                (new InlineFragment('ProductRelationAttribute'))
                    ->setSelectionSet([
                        new Query('code', AttributeTypesEnum::RELATION),
                        (new Query('name'))
                            ->setSelectionSet([
                                'language',
                                'value',
                            ]),
                    ]),
                (new InlineFragment('TextareaAttribute'))
                    ->setSelectionSet([
                        new Query('code', AttributeTypesEnum::TEXTAREA),
                    ]),
                (new InlineFragment('TextAttribute'))
                    ->setSelectionSet([
                        new Query('code', AttributeTypesEnum::TEXT),
                    ]),
                (new InlineFragment('UnitAttribute'))
                    ->setSelectionSet([
                        new Query('code', AttributeTypesEnum::UNIT),
                    ]),
            ]);
    }

    public static function attributesTranslations(): Query
    {
        return (new Query('translations'))
            ->setSelectionSet([
                'language',
                '__typename',
                (new InlineFragment('TextAttributeValueTranslation'))
                    ->setSelectionSet([
                        (new Query('value'))->setAlias('value_string'),
                    ]),
                (new InlineFragment('TextareaAttributeValueTranslation'))
                    ->setSelectionSet([
                        (new Query('value'))->setAlias('value_string'),
                    ]),
                (new InlineFragment('DateAttributeValueTranslation'))
                    ->setSelectionSet([
                        (new Query('value'))->setAlias('value_string'),
                    ]),
                (new InlineFragment('UnitAttributeValueTranslation'))
                    ->setSelectionSet([
                        (new Query('value'))->setAlias('value_numeric'),
                    ]),
                (new InlineFragment('PriceAttributeValueTranslation'))
                    ->setSelectionSet([
                        (new Query('value'))->setAlias('value_numeric'),
                    ]),
                (new InlineFragment('NumericAttributeValueTranslation'))
                    ->setSelectionSet([
                        (new Query('value'))->setAlias('value_numeric'),
                    ]),
                (new InlineFragment('MultiSelectAttributeValueTranslation'))
                    ->setSelectionSet([
                        (new Query('translatedValue'))
                            ->setAlias('value_multi_array')
                            ->setSelectionSet([
                                'code',
                                'name',
                            ]),
                    ]),
                (new InlineFragment('SelectAttributeValueTranslation'))
                    ->setSelectionSet([
                        (new Query('translatedValue'))
                            ->setAlias('value_array')
                            ->setSelectionSet([
                                'code',
                                'name',
                            ]),
                    ]),

                (new InlineFragment('FileAttributeValueTranslation'))
                    ->setSelectionSet([
                        (new Query('value'))
                            ->setAlias('value_multimedia_array')
                            ->setSelectionSet([
                                'name',
                                'extension',
                                'mime',
                                'size',
                                'url',
                            ]),
                    ]),
                (new InlineFragment('ImageAttributeValueTranslation'))
                    ->setSelectionSet([
                        (new Query('value'))
                            ->setAlias('value_multimedia')
                            ->setSelectionSet([
                                'name',
                                'extension',
                                'mime',
                                'size',
                                'url',
                            ]),
                    ]),
                (new InlineFragment('GalleryAttributeValueTranslation'))
                    ->setSelectionSet([
                        (new Query('value'))
                            ->setAlias('value_multimedia_array')
                            ->setSelectionSet([
                                'name',
                                'extension',
                                'mime',
                                'size',
                                'url',
                            ]),
                    ]),
                (new InlineFragment('ProductRelationAttributeValueTranslation'))
                    ->setSelectionSet([
                        (new Query('value'))
                            ->setAlias('value_product_array')
                            ->setSelectionSet([
                                'sku',
                            ]),
                    ]),
            ]);
    }
}
