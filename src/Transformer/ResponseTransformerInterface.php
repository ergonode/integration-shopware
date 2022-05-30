<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Strix\Ergonode\Struct\AbstractErgonodeEntityCollection;

interface ResponseTransformerInterface
{
    public function transformResponse(array $response): AbstractErgonodeEntityCollection;
}