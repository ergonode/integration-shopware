<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer\CustomField;

interface CustomFieldTransformerInterface
{
    public function supports(array $node): bool;

    public function transformNode(array $node): array;
}