<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\QueryBuilder;

use GraphQL\Query;

class TemplateQueryBuilder
{
    public function build(int $count, ?string $cursor = null): Query
    {
        $arguments = [
            'first' => $count,
        ];

        if ($cursor !== null) {
            $arguments['after'] = $cursor;
        }

        return (new Query('templateList'))
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
                                'code',
                            ]),
                    ]),
            ]);
    }
}
