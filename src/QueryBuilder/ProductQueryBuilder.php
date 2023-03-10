<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\QueryBuilder;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use GraphQL\InlineFragment;
use GraphQL\Query;

class ProductQueryBuilder
{
    private const ATTRIBUTE_LIST_COUNT = 1000;
    private const VARIANT_LIST_COUNT = 25;

    public function build(int $count, ?string $cursor = null): Query
    {
        $arguments = [
            'first' => $count,
        ];

        if ($cursor !== null) {
            $arguments['after'] = $cursor;
        }

        return (new Query('productStream'))
            ->setArguments($arguments)
            ->setSelectionSet([
                'totalCount',
                (new Query('pageInfo'))
                    ->setSelectionSet([
                        'endCursor',
                        'hasNextPage',
                    ]),
                (new Query('edges'))
                    ->setSelectionSet([
                        'cursor',
                        (new Query('node'))
                            ->setSelectionSet([
                                'sku',
                                'createdAt',
                                'editedAt',
                                '__typename',
                                (new InlineFragment('VariableProduct'))
                                    ->setSelectionSet([
                                        (new Query('bindings'))
                                            ->setSelectionSet([
                                                'code',
                                            ]),
                                        (new Query('variantList'))
                                            ->setArguments(['first' => self::VARIANT_LIST_COUNT])
                                            ->setSelectionSet([
                                                (new Query('pageInfo'))
                                                    ->setSelectionSet([
                                                        'endCursor',
                                                        'hasNextPage',
                                                    ]),
                                                (new Query('edges'))
                                                    ->setSelectionSet([
                                                        (new Query('node'))
                                                            ->setSelectionSet([
                                                                'sku',
                                                                (new Query('attributeList'))
                                                                    ->setArguments(['first' => self::ATTRIBUTE_LIST_COUNT])
                                                                    ->setSelectionSet([
                                                                        (new Query('edges'))
                                                                            ->setSelectionSet([
                                                                                (new Query('node'))
                                                                                    ->setSelectionSet([
                                                                                        $this->getAttributeFragment(),
                                                                                        (new Query('translations'))
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
                                                                                                        (new Query('value'))
                                                                                                            ->setAlias('value_multi_array')
                                                                                                            ->setSelectionSet([
                                                                                                                'code',
                                                                                                            ]),
                                                                                                    ]),
                                                                                                (new InlineFragment('SelectAttributeValueTranslation'))
                                                                                                    ->setSelectionSet([
                                                                                                        (new Query('value'))
                                                                                                            ->setAlias('value_array')
                                                                                                            ->setSelectionSet([
                                                                                                                'code',
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
                                                                                            ]),
                                                                                    ]),
                                                                            ]),
                                                                    ]),
                                                            ]),
                                                    ]),
                                                'totalCount',
                                            ]),
                                    ]),
                                (new Query('categoryList'))
                                    ->setSelectionSet([
                                        (new Query('edges'))
                                            ->setSelectionSet([
                                                (new Query('node'))
                                                    ->setSelectionSet([
                                                        'code',
                                                    ]),
                                            ]),
                                    ]),
                                (new Query('attributeList'))
                                    ->setArguments(['first' => self::ATTRIBUTE_LIST_COUNT])
                                    ->setSelectionSet([
                                        (new Query('edges'))
                                            ->setSelectionSet([
                                                (new Query('node'))
                                                    ->setSelectionSet([
                                                        $this->getAttributeFragment(),
                                                        (new Query('translations'))
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
                                                                        (new Query('value'))
                                                                            ->setAlias('value_multi_array')
                                                                            ->setSelectionSet([
                                                                                'code',
                                                                            ]),
                                                                    ]),
                                                                (new InlineFragment('SelectAttributeValueTranslation'))
                                                                    ->setSelectionSet([
                                                                        (new Query('value'))
                                                                            ->setAlias('value_array')
                                                                            ->setSelectionSet([
                                                                                'code',
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
                                                            ]),
                                                    ]),
                                            ]),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public function buildDeleted(?int $count = null, ?string $cursor = null): Query
    {
        $arguments = [];

        if ($count !== null) {
            $arguments['first'] = $count;
        }

        if ($cursor !== null) {
            $arguments['after'] = $cursor;
        }

        return (new Query('productDeletedStream'))
            ->setArguments($arguments)
            ->setSelectionSet([
                'totalCount',
                (new Query('pageInfo'))
                    ->setSelectionSet([
                        'endCursor',
                        'hasNextPage',
                    ]),
                (new Query('edges'))
                    ->setSelectionSet([
                        'cursor',
                        'node',
                    ]),
            ]);
    }

    public function buildProductWithVariants(string $sku, ?string $cursor = null): Query
    {
        $variantArguments = ['first' => self::VARIANT_LIST_COUNT];
        if ($cursor) {
            $variantArguments['after'] = $cursor;
        }

        return (new Query('product'))
            ->setArguments(['sku' => $sku])
            ->setSelectionSet([
                'sku',
                'createdAt',
                'editedAt',
                '__typename',
                (new InlineFragment('VariableProduct'))
                    ->setSelectionSet([
                        (new Query('bindings'))
                            ->setSelectionSet([
                                'code',
                            ]),
                        (new Query('variantList'))
                            ->setArguments($variantArguments)
                            ->setSelectionSet([
                                (new Query('pageInfo'))
                                    ->setSelectionSet([
                                        'endCursor',
                                        'hasNextPage',
                                    ]),
                                (new Query('edges'))
                                    ->setSelectionSet([
                                        (new Query('node'))
                                            ->setSelectionSet([
                                                'sku',
                                                (new Query('attributeList'))
                                                    ->setArguments(['first' => self::ATTRIBUTE_LIST_COUNT])
                                                    ->setSelectionSet([
                                                        (new Query('edges'))
                                                            ->setSelectionSet([
                                                                (new Query('node'))
                                                                    ->setSelectionSet([
                                                                        $this->getAttributeFragment(),
                                                                        (new Query('translations'))
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
                                                                                        (new Query('value'))
                                                                                            ->setAlias('value_multi_array')
                                                                                            ->setSelectionSet([
                                                                                                'code',
                                                                                            ]),
                                                                                    ]),
                                                                                (new InlineFragment('SelectAttributeValueTranslation'))
                                                                                    ->setSelectionSet([
                                                                                        (new Query('value'))
                                                                                            ->setAlias('value_array')
                                                                                            ->setSelectionSet([
                                                                                                'code',
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
                                                                            ]),
                                                                    ]),
                                                            ]),
                                                        'totalCount',
                                                    ]),
                                            ]),
                                    ]),
                            ]),
                    ]),
                (new Query('categoryList'))
                    ->setSelectionSet([
                        (new Query('edges'))
                            ->setSelectionSet([
                                (new Query('node'))
                                    ->setSelectionSet([
                                        'code',
                                    ]),
                            ]),
                    ]),
                (new Query('attributeList'))
                    ->setArguments(['first' => self::ATTRIBUTE_LIST_COUNT])
                    ->setSelectionSet([
                        (new Query('edges'))
                            ->setSelectionSet([
                                (new Query('node'))
                                    ->setSelectionSet([
                                        $this->getAttributeFragment(),
                                        (new Query('translations'))
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
                                                        (new Query('value'))
                                                            ->setAlias('value_multi_array')
                                                            ->setSelectionSet([
                                                                'code',
                                                            ]),
                                                    ]),
                                                (new InlineFragment('SelectAttributeValueTranslation'))
                                                    ->setSelectionSet([
                                                        (new Query('value'))
                                                            ->setAlias('value_array')
                                                            ->setSelectionSet([
                                                                'code',
                                                                (new Query('name'))
                                                                    ->setSelectionSet([
                                                                        'value',
                                                                        'language'
                                                                    ])
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
                                            ]),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public function buildOnlySkus(int $count, ?string $cursor): Query
    {
        $arguments = [
            'first' => $count,
        ];

        if ($cursor !== null) {
            $arguments['after'] = $cursor;
        }

        return (new Query('productStream'))
            ->setArguments($arguments)
            ->setSelectionSet([
                'totalCount',
                (new Query('pageInfo'))
                    ->setSelectionSet([
                        'endCursor',
                        'hasNextPage',
                    ]),
                (new Query('edges'))
                    ->setSelectionSet([
                        'cursor',
                        (new Query('node'))
                            ->setSelectionSet([
                                'sku',
                                '__typename',
                            ]),
                    ]),
            ]);
    }

    public function buildVariantSkusForProduct(string $sku): Query
    {
        return (new Query('product'))
            ->setArguments(['sku' => $sku])
            ->setSelectionSet([
                (new InlineFragment('VariableProduct'))
                    ->setSelectionSet([
                        (new Query('variantList'))
                            ->setArguments(['first' => 10000]) // allow unlimited
                            ->setSelectionSet([
                                (new Query('edges'))
                                    ->setSelectionSet([
                                        (new Query('node'))
                                            ->setSelectionSet([
                                                'sku',
                                            ]),
                                    ]),
                                'totalCount',
                            ]),
                    ]),
            ]);
    }

    private function getAttributeFragment(): Query
    {
        return (new Query('attribute'))
            ->setSelectionSet([
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
}
