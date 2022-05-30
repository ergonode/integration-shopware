<?php

declare(strict_types=1);

namespace Strix\Ergonode\Struct;

use Shopware\Core\Framework\Struct\Collection;

abstract class AbstractErgonodeEntityCollection extends Collection
{
    public function merge(self $collection): self
    {
        $this->elements = array_merge($this->elements, $collection->getElements());

        return $this;
    }

    protected function getExpectedClass(): ?string
    {
        return AbstractErgonodeEntity::class;
    }
}