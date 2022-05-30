<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Strix\Ergonode\Struct\AbstractErgonodeEntity;

interface NodeTransformerInterface
{
    public function supports(string $className): bool;

    public function transformNode(array $node): AbstractErgonodeEntity;
}