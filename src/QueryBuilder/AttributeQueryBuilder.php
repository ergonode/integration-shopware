<?php

declare(strict_types=1);

namespace Strix\Ergonode\QueryBuilder;

use GraphQL\InlineFragment;
use GraphQL\Query;
use Strix\Ergonode\Enum\AttributeTypesEnum;

class AttributeQueryBuilder
{
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
                                'code',
                                'scope',
                                (new Query('label'))
                                    ->setSelectionSet([
                                        'language',
                                        'value',
                                    ]),
                                (new Query('hint'))
                                    ->setSelectionSet([
                                        'language',
                                        'value',
                                    ]),
                                (new Query('placeholder'))
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
                                        (new Query('options'))
                                            ->setSelectionSet([
                                                new Query('code'),
                                                (new Query('label'))
                                                    ->setSelectionSet([
                                                        'language',
                                                        'value',
                                                    ]),
                                            ]),
                                    ]),
                                (new InlineFragment('MultiSelectAttribute'))
                                    ->setSelectionSet([
                                        new Query('code', AttributeTypesEnum::MULTISELECT),
                                        (new Query('options'))
                                            ->setSelectionSet([
                                                new Query('code'),
                                                (new Query('label'))
                                                    ->setSelectionSet([
                                                        'language',
                                                        'value',
                                                    ]),
                                            ]),
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
            ]);
    }

    public function buildOptions(string $attributeCode): Query
    {
        return (new Query('attribute'))
            ->setArguments([
                'code' => $attributeCode,
            ])
            ->setSelectionSet([
                (new InlineFragment('SelectAttribute'))
                    ->setSelectionSet([
                        (new Query('options'))
                            ->setSelectionSet([
                                'code',
                                (new Query('label'))
                                    ->setSelectionSet([
                                        'language',
                                        'value',
                                    ]),
                            ]),
                    ]),
                (new InlineFragment('MultiSelectAttribute'))
                    ->setSelectionSet([
                        (new Query('options'))
                            ->setSelectionSet([
                                'code',
                                (new Query('label'))
                                    ->setSelectionSet([
                                        'language',
                                        'value',
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
}