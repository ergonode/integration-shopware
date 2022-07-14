<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Struct;

abstract class AbstractErgonodeEntity
{
    protected ?string $cursor = null;

    protected ?string $code = null;

    public function __construct(?string $code = null)
    {
        $this->code = $code;
    }

    public function setCursor(?string $cursor): void
    {
        $this->cursor = $cursor;
    }

    public function getCursor(): ?string
    {
        return $this->cursor;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }
}