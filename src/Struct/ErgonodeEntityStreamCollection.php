<?php

declare(strict_types=1);

namespace Strix\Ergonode\Struct;

use RuntimeException;

/**
 * @method void add(AbstractErgonodeEntity $entity)
 * @method void set(string $key, AbstractErgonodeEntity $entity)
 * @method AbstractErgonodeEntity[] getIterator()
 * @method AbstractErgonodeEntity[] getElements()
 * @method AbstractErgonodeEntity|null get(string $key)
 * @method AbstractErgonodeEntity|null first()
 * @method AbstractErgonodeEntity|null last()
 * @deprecated ?
 */
class ErgonodeEntityStreamCollection extends AbstractErgonodeEntityCollection
{
    private ?string $expectedClass = null;

    private ?int $totalCount = null;

    private bool $hasNextPage = false;

    private ?string $endCursor = null;

    public function setExpectedClass(string $expectedClass): void
    {
        if (!in_array(ErgonodeEntityInterface::class, class_implements($expectedClass))) {
            throw new RuntimeException(sprintf(
                'Class %s, does not implement %s',
                $expectedClass,
                ErgonodeEntityInterface::class
            ));
        }

        $this->expectedClass = $expectedClass;
    }

    /**
     * @return int|null
     */
    public function getTotalCount(): ?int
    {
        return $this->totalCount;
    }

    /**
     * @param int|null $totalCount
     */
    public function setTotalCount(?int $totalCount): void
    {
        $this->totalCount = $totalCount;
    }

    /**
     * @return bool
     */
    public function isHasNextPage(): bool
    {
        return $this->hasNextPage;
    }

    /**
     * @param bool $hasNextPage
     */
    public function setHasNextPage(bool $hasNextPage): void
    {
        $this->hasNextPage = $hasNextPage;
    }

    /**
     * @return string|null
     */
    public function getEndCursor(): ?string
    {
        return $this->endCursor;
    }

    /**
     * @param string|null $endCursor
     */
    public function setEndCursor(?string $endCursor): void
    {
        $this->endCursor = $endCursor;
    }

    protected function getExpectedClass(): ?string
    {
        return $this->expectedClass;
    }
}