<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\QueryBuilder;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use GraphQL\InlineFragment;
use GraphQL\Query;

class AttributeQueryBuilder
{
    private const OPTION_COUNT = 200;

    public function build(int $count, ?string $cursor = null): Query
    {
        $arguments = [
            'first' => $count,
        ];

        if ($cursor !== null) {
            $arguments['after'] = $cursor;
        }

        return (new Query('attributeStream'))
            ->setArguments($arguments)
            ->setSelectionSet($this->getAttributeSelectionSet());
    }


    protected function getAttributeSelectionSet(): array
    {
        return [
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
                            'code',
                            'scope',
                            (new Query('name'))
                                ->setSelectionSet([
                                    'language',
                                    'value',
                                ]),
                            (new InlineFragment('DateAttribute'))
                                ->setSelectionSet([
                                    new Query('code', AttributeTypesEnum::DATE),
                                    new Query('format', 'additional_date_format'),
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
                                    $this->getOptionsFragment(),
                                ]),
                            (new InlineFragment('MultiSelectAttribute'))
                                ->setSelectionSet([
                                    new Query('code', AttributeTypesEnum::MULTISELECT),
                                    $this->getOptionsFragment(),
                                ]),
                            (new InlineFragment('NumericAttribute'))
                                ->setSelectionSet([
                                    new Query('code', AttributeTypesEnum::NUMERIC),
                                ]),
                            (new InlineFragment('PriceAttribute'))
                                ->setSelectionSet([
                                    new Query('code', AttributeTypesEnum::PRICE),
                                    new Query('currency', 'additional_currency'),
                                ]),
                            (new InlineFragment('ProductRelationAttribute'))
                                ->setSelectionSet([
                                    new Query('code', AttributeTypesEnum::RELATION),
                                ]),
                            (new InlineFragment('TextareaAttribute'))
                                ->setSelectionSet([
                                    new Query('code', AttributeTypesEnum::TEXTAREA),
                                    new Query('richEdit', 'additional_richEdit'),
                                ]),
                            (new InlineFragment('TextAttribute'))
                                ->setSelectionSet([
                                    new Query('code', AttributeTypesEnum::TEXT),
                                ]),
                            (new InlineFragment('UnitAttribute'))
                                ->setSelectionSet([
                                    new Query('code', AttributeTypesEnum::UNIT),
                                    (new Query('unit', 'additional_unit'))
                                        ->setSelectionSet([
                                            'name',
                                            'symbol',
                                        ]),
                                ]),
                        ]),
                ]),
        ];
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

        return (new Query('attributeDeletedStream'))
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

    private function getOptionsFragment(?string $cursor = null): Query
    {
        $arguments = ['first' => self::OPTION_COUNT];
        if ($cursor) {
            $arguments['after'] = $cursor;
        }

        return (new Query('optionList'))
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
                        (new Query('node'))
                            ->setSelectionSet([
                                new Query('code'),
                                (new Query('name'))
                                    ->setSelectionSet([
                                        'language',
                                        'value',
                                    ]),
                            ]),
                    ]),

            ]);
    }

    public function buildSingle(string $code, ?string $optionCursor = null): Query
    {
        return (new Query('attribute'))
            ->setArguments(['code' => $code])
            ->setSelectionSet([
                'code',
                'scope',
                (new Query('name'))
                    ->setSelectionSet([
                        'language',
                        'value',
                    ]),
                (new InlineFragment('DateAttribute'))
                    ->setSelectionSet([
                        new Query('code', AttributeTypesEnum::DATE),
                        new Query('format', 'additional_date_format'),
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
                        $this->getOptionsFragment($optionCursor),
                    ]),
                (new InlineFragment('MultiSelectAttribute'))
                    ->setSelectionSet([
                        new Query('code', AttributeTypesEnum::MULTISELECT),
                        $this->getOptionsFragment($optionCursor),
                    ]),
                (new InlineFragment('NumericAttribute'))
                    ->setSelectionSet([
                        new Query('code', AttributeTypesEnum::NUMERIC),
                    ]),
                (new InlineFragment('PriceAttribute'))
                    ->setSelectionSet([
                        new Query('code', AttributeTypesEnum::PRICE),
                        new Query('currency', 'additional_currency'),
                    ]),
                (new InlineFragment('ProductRelationAttribute'))
                    ->setSelectionSet([
                        new Query('code', AttributeTypesEnum::RELATION),
                    ]),
                (new InlineFragment('TextareaAttribute'))
                    ->setSelectionSet([
                        new Query('code', AttributeTypesEnum::TEXTAREA),
                        new Query('richEdit', 'additional_richEdit'),
                    ]),
                (new InlineFragment('TextAttribute'))
                    ->setSelectionSet([
                        new Query('code', AttributeTypesEnum::TEXT),
                    ]),
                (new InlineFragment('UnitAttribute'))
                    ->setSelectionSet([
                        new Query('code', AttributeTypesEnum::UNIT),
                        (new Query('unit', 'additional_unit'))
                            ->setSelectionSet([
                                'name',
                                'symbol',
                            ]),
                    ]),
            ]);
    }
}
