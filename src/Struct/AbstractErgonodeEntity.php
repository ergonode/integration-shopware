<?php

declare(strict_types=1);

namespace Strix\Ergonode\Struct;

abstract class AbstractErgonodeEntity implements ErgonodeEntityInterface
{
    protected ?string $cursor = null;

    protected string $primaryValue;

    public function setCursor(?string $cursor): void
    {
        $this->cursor = $cursor;
    }

    public function getCursor(): ?string
    {
        return $this->cursor;
    }

    public function setPrimaryValue(string $primaryValue): void
    {
        $this->primaryValue = $primaryValue;
    }

    public function getPrimaryValue(): string
    {
        return $this->primaryValue;
    }
}