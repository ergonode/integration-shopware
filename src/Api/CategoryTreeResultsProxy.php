<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Api;

class CategoryTreeResultsProxy extends AbstractResultsProxy
{
    public const MAIN_FIELD = 'categoryTree';

    public function getLeafList(): array
    {
        return $this->getMainData()['categoryTreeLeafList'] ?? [];
    }

    public function getEdges(): array
    {
        return $this->getLeafList()['edges'] ?? [];
    }

    public function getEndCursor(): ?string
    {
        return $this->getLeafList()['pageInfo']['endCursor'] ?? null;
    }

    public function hasNextPage(): bool
    {
        return (bool)($this->getLeafList()['pageInfo']['hasNextPage'] ?? false);
    }
}
