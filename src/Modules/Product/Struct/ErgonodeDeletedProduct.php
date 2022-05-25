<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Product\Struct;

use Strix\Ergonode\Struct\AbstractErgonodeEntity;

class ErgonodeDeletedProduct extends AbstractErgonodeEntity
{
    private string $sku;

    public function setFromResponse(array $response): void
    {
        $this->sku = $response['__value__'];
        $this->setPrimaryValue($this->sku);
    }

    public function getSku(): string
    {
        return $this->sku;
    }
}