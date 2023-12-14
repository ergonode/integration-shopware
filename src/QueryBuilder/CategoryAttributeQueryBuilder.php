<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\QueryBuilder;

use GraphQL\Query;

class CategoryAttributeQueryBuilder extends AttributeQueryBuilder
{
    public function build(int $count, ?string $cursor = null): Query
    {
        $arguments = [
            'first' => $count,
        ];

        if ($cursor !== null) {
            $arguments['after'] = $cursor;
        }

        return (new Query('categoryAttributeList'))
            ->setArguments($arguments)
            ->setSelectionSet($this->getAttributeSelectionSet());
    }
}
