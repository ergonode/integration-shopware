<?php

declare(strict_types=1);

namespace Strix\Ergonode\Api\Client;

use GraphQL\Query;
use Strix\Ergonode\Api\GqlResponse;

interface ErgonodeGqlClientInterface
{
    public function query(Query $query): ?GqlResponse;
}