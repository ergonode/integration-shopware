<?php

declare(strict_types=1);

namespace Strix\Ergonode\Entity\ErgonodeSyncHistory;

use DateTimeInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ErgonodeSyncHistoryEntity extends Entity
{
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_FINISHED = 'finished';

    use EntityIdTrait;

    protected string $name;

    protected string $status;

    protected int $totalSuccess;

    protected int $totalError;

    protected DateTimeInterface $startDate;

    protected ?DateTimeInterface $endDate = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getTotalSuccess(): int
    {
        return $this->totalSuccess;
    }

    public function setTotalSuccess(int $totalSuccess): void
    {
        $this->totalSuccess = $totalSuccess;
    }

    public function getTotalError(): int
    {
        return $this->totalError;
    }

    public function setTotalError(int $totalError): void
    {
        $this->totalError = $totalError;
    }

    public function getStartDate(): DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeInterface $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTimeInterface $endDate): void
    {
        $this->endDate = $endDate;
    }
}