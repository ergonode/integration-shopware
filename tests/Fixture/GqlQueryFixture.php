<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests\Fixture;

use GraphQL\Query;

class GqlQueryFixture
{
    public static function basicProductStreamQuery(): Query
    {
        return (new Query('productStream'))
            ->setSelectionSet([
                'totalCount',
            ]);
    }
}