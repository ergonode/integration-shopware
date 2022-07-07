<?php

declare(strict_types=1);

namespace Strix\Ergonode\Struct;

use Strix\Ergonode\Struct\AbstractErgonodeEntityCollection;
use Strix\Ergonode\Struct\ErgonodeCategory;

/**
 * @method void add(ErgonodeCategory $entity)
 * @method void set(string $key, ErgonodeCategory $entity)
 * @method ErgonodeCategory[] getIterator()
 * @method ErgonodeCategory[] getElements()
 * @method ErgonodeCategory|null get(string $key)
 * @method ErgonodeCategory|null first()
 * @method ErgonodeCategory|null last()
 */
class ErgonodeCategoryCollection extends AbstractErgonodeEntityCollection
{
    protected function getExpectedClass(): ?string
    {
        return ErgonodeCategory::class;
    }
}