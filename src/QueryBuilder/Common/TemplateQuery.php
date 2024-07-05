<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\QueryBuilder\Common;

use GraphQL\Query;

class TemplateQuery
{
    public static function getTemplateFragment(): Query
    {
        return (new Query('template'))
            ->setSelectionSet([
                'code',
            ]);
    }
}
