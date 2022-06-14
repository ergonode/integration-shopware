<?php

declare(strict_types=1);

namespace Strix\Ergonode\Api\Client;

use GraphQL\Query;
use GraphQL\Results;

interface ErgonodeGqlClientInterface
{
    public function query(Query $query, ?string $proxyClass = null): ?Results;
}