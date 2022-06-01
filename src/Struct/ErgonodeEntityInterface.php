<?php

declare(strict_types=1);

namespace Strix\Ergonode\Struct;

interface ErgonodeEntityInterface
{
    public function setFromResponse(array $response): void;

    public function setCursor(?string $cursor): void;

    public function getCursor(): ?string;

    public function setPrimaryValue(string $primaryValue): void;

    public function getPrimaryValue(): string;
}