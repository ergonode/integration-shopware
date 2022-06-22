<?php

declare(strict_types=1);

namespace Strix\Ergonode\QueryBuilder;

use GraphQL\Query;

class LanguageQueryBuilder
{
    public function buildActiveLanguages(int $count, ?string $cursor = null): Query
    {
        $arguments = [
            'first' => $count,
        ];

        if ($cursor !== null) {
            $arguments['after'] = $cursor;
        }

        return (new Query('languageList'))
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
                        new Query('node', 'locale'),
                    ]),
            ]);
    }
}