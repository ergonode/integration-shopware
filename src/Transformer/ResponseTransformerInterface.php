<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\Struct\AbstractErgonodeEntityCollection;

interface ResponseTransformerInterface
{
    public function transformResponse(array $response): AbstractErgonodeEntityCollection;
}