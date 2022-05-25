<?php

declare(strict_types=1);

namespace Strix\Ergonode\Struct;

use RuntimeException;

class StreamResult
{
    private string $entityClass;

    private ?int $totalCount = null;

    private bool $hasNextPage = false;

    private ?string $endCursor = null;

    private array $entities;

    public function __construct(string $entityClass, array $response)
    {
        $this->setEntityClass($entityClass);
        $this->setFromResponse($response);
    }

    public function setEntityClass(string $entityClass): void
    {
        if (!in_array(ErgonodeEntityInterface::class, class_implements($entityClass))) {
            throw new RuntimeException(sprintf(
                'Class %s, does not implement %s',
                $entityClass,
                ErgonodeEntityInterface::class
            ));
        }

        $this->entityClass = $entityClass;
    }

    public function setFromResponse(array $response): void
    {
        $this->totalCount = $response['totalCount'] ?? null;
        $this->hasNextPage = $response['pageInfo']['hasNextPage'] ?? false;
        $this->endCursor = $response['pageInfo']['endCursor'] ?? null;
        $this->entities = [];

        if (!isset($response['edges']) && isset($response['product'])) {
            $response['edges'][] = $response['product'];
        }

        foreach ($response['edges'] as $edge) {
            /** @var ErgonodeEntityInterface $entity */
            $entity = new $this->entityClass;
            $entity->setCursor($edge['cursor'] ?? null);
            $node = isset($edge['node']) ?
                (is_array($edge['node']) ?
                    $edge['node'] :
                    ['__value__' => $edge['node']]) :
                $edge;
            $entity->setFromResponse($node);
            $this->entities[$entity->getPrimaryValue()] = $entity;
        }
    }

    public function getTotalCount(): ?int
    {
        return $this->totalCount;
    }

    public function isHasNextPage(): bool
    {
        return $this->hasNextPage;
    }

    public function getEndCursor(): ?string
    {
        return $this->endCursor;
    }

    public function getEntities(): array
    {
        return $this->entities;
    }
}
