<?php

declare(strict_types=1);

namespace Strix\Ergonode\Struct;

/**
 * @deprecated ?
 */
interface ErgonodeEntityInterface
{
    public function setCursor(?string $cursor): void;

    public function getCursor(): ?string;

    public function setCode(?string $code): void;

    public function getCode(): ?string;
}