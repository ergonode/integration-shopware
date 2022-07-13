<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Api;

abstract class AbstractStreamResultsProxy extends AbstractResultsProxy
{
    public function getEdges(): array
    {
        return $this->getMainData()['edges'] ?? [];
    }

    public function hasEndCursor(): bool
    {
        return null !== $this->getEndCursor();
    }

    public function getEndCursor(): ?string
    {
        return $this->getMainData()['pageInfo']['endCursor'] ?? null;
    }

    public function hasNextPage(): bool
    {
        return (bool)$this->getMainData()['pageInfo']['hasNextPage'] ?? false;
    }

    public function merge(AbstractStreamResultsProxy $results): self
    {
        array_merge($this->results['data'][static::MAIN_FIELD]['edges'], $results->getEdges());
        $this->results['data'][static::MAIN_FIELD]['pageInfo']['endCursor'] = $results->getEndCursor();
        $this->results['data'][static::MAIN_FIELD]['pageInfo']['hasNextPage'] = $results->hasNextPage();

        return $this;
    }

    public function filter(callable $callback): self
    {
        $filteredResults = clone $this;

        $filteredEdges = array_filter(
            $filteredResults->getEdges(),
            $callback
        );

        $filteredResults->results['data'][static::MAIN_FIELD]['edges'] = $filteredEdges;

        return $filteredResults;
    }

    public function map(callable $callback): array
    {
        return array_map($callback, $this->getEdges());
    }
}