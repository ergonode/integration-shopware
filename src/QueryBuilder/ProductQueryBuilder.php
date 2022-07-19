<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\QueryBuilder;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use GraphQL\InlineFragment;
use GraphQL\Query;

class ProductQueryBuilder
{
    private const ATTRIBUTE_LIST_COUNT = 1000;

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
                                                                                                'inherited',
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
                                                        (new Query('valueTranslations'))
                                                            ->setSelectionSet([
                                                                'inherited',
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
                        (new Query('label'))
                            ->setSelectionSet([
                                'language',
                                'value'
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