<?php

declare(strict_types=1);

namespace Strix\Ergonode\Entity\ErgonodeCursor;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ErgonodeCursorEntity extends Entity
{
    use EntityIdTrait;

    protected string $cursor;

    protected string $query;

    public function getCursor(): string
    {
        return $this->cursor;
    }

    public function setCursor(string $cursor): void
    {
        $this->cursor = $cursor;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function setQuery(string $query): void
    {
        $this->query = $query;
    }
}