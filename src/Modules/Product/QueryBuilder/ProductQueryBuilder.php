<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Product\QueryBuilder;

use GraphQL\InlineFragment;
use GraphQL\Query;
use Strix\Ergonode\Enum\AttributeTypesEnum;

class ProductQueryBuilder
{
    private const ATTRIBUTE_LIST_COUNT = 1000;

    // methods copied from magento module TODO change method names and optimize queries - query only needed data
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
                                (new Query('template'))
                                    ->setSelectionSet([
                                        'name',
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
                                                $this->getAttributeFragment(),
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

    public function buildAttributesValue(string $sku, string $language): Query
    {
        return (new Query('product'))
            ->setArguments(['sku' => $sku])
            ->setSelectionSet([
                (new Query('attributeList'))
                    ->setArguments(['first' => self::ATTRIBUTE_LIST_COUNT])
                    ->setSelectionSet([
                        (new Query('edges'))
                            ->setSelectionSet([
                                (new Query('node'))
                                    ->setSelectionSet([
                                        $this->getAttributeFragment(),
                                        (new Query('valueTranslations'))
                                            ->setArguments(['languages' => [$language]])
                                            ->setSelectionSet([
                                                'language',
                                                '__typename',
                                                (new InlineFragment('StringAttributeValue'))
                                                    ->setSelectionSet([
                                                        (new Query('value'))->setAlias('value_string'),
                                                    ]),
                                                (new InlineFragment('NumericAttributeValue'))
                                                    ->setSelectionSet([
                                                        (new Query('value'))->setAlias('value_numeric'),
                                                    ]),
                                                (new InlineFragment('StringArrayAttributeValue'))
                                                    ->setSelectionSet([
                                                        (new Query('value'))->setAlias('value_array'),
                                                    ]),
                                                (new InlineFragment('MultimediaAttributeValue'))
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
                                                (new InlineFragment('MultimediaArrayAttributeValue'))
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
                                                (new InlineFragment('ProductArrayAttributeValue'))
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

    public function buildVariableProductDetails(string $sku): Query
    {
        return (new Query('product'))
            ->setArguments(['sku' => $sku])
            ->setSelectionSet([
                '__typename',
                (new InlineFragment('VariableProduct'))
                    ->setSelectionSet([
                        (new Query('bindings'))
                            ->setSelectionSet([
                                'code',
                            ]),
                        (new Query('variantList'))
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

    public function buildProductWithVariants(string $sku): Query
    {
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
                            ->setSelectionSet([
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
                                                                        (new Query('valueTranslations'))
                                                                            ->setSelectionSet([
                                                                                'language',
                                                                                '__typename',
                                                                                (new InlineFragment('StringAttributeValue'))
                                                                                    ->setSelectionSet([
                                                                                        (new Query('value'))->setAlias('value_string'),
                                                                                    ]),
                                                                                (new InlineFragment('NumericAttributeValue'))
                                                                                    ->setSelectionSet([
                                                                                        (new Query('value'))->setAlias('value_numeric'),
                                                                                    ]),
                                                                                (new InlineFragment('StringArrayAttributeValue'))
                                                                                    ->setSelectionSet([
                                                                                        (new Query('value'))->setAlias('value_array'),
                                                                                    ]),
                                                                                (new InlineFragment('MultimediaAttributeValue'))
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
                                                                                (new InlineFragment('MultimediaArrayAttributeValue'))
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
                                                                                (new InlineFragment('ProductArrayAttributeValue'))
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
                (new Query('template'))
                    ->setSelectionSet([
                        'code',
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
                                        (new Query('valueTranslations'))
                                            ->setSelectionSet([
                                                'language',
                                                '__typename',
                                                (new InlineFragment('StringAttributeValue'))
                                                    ->setSelectionSet([
                                                        (new Query('value'))->setAlias('value_string'),
                                                    ]),
                                                (new InlineFragment('NumericAttributeValue'))
                                                    ->setSelectionSet([
                                                        (new Query('value'))->setAlias('value_numeric'),
                                                    ]),
                                                (new InlineFragment('StringArrayAttributeValue'))
                                                    ->setSelectionSet([
                                                        (new Query('value'))->setAlias('value_array'),
                                                    ]),
                                                (new InlineFragment('MultimediaAttributeValue'))
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
                                                (new InlineFragment('MultimediaArrayAttributeValue'))
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
                                                (new InlineFragment('ProductArrayAttributeValue'))
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

    public function buildSingleProduct(string $sku): Query
    {
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
                        (new Query('attributeList'))
                            ->setArguments(['first' => self::ATTRIBUTE_LIST_COUNT])
                            ->setSelectionSet([
                                (new Query('edges'))
                                    ->setSelectionSet([
                                        (new Query('node'))
                                            ->setSelectionSet([
                                                (new Query('attribute'))
                                                    ->setSelectionSet([
                                                        'code',
                                                    ]),
                                            ]),
                                    ]),
                            ]),
                        (new Query('variantList'))
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
                (new Query('template'))
                    ->setSelectionSet([
                        'name',
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
                                    ]),
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