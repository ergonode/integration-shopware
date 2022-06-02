<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Product\Struct;

use Strix\Ergonode\Struct\AbstractErgonodeEntity;

class ErgonodeDeletedProduct extends AbstractErgonodeEntity
{
    public function getSku(): string
    {
        return $this->getCode();
    }

    public function setSku(string $sku): void
    {
        $this->code = $sku;
    }
}