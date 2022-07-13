<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Api;

use GraphQL\Results;

abstract class AbstractResultsProxy extends Results
{
    public const MAIN_FIELD = '';

    public function __construct(Results $originalResults)
    {
        $originalResults->getResponseObject()->getBody()->rewind();
        parent::__construct($originalResults->getResponseObject(), is_array($originalResults->getResults()));
    }

    public function getMainData(): array
    {
        return $this->getData()[static::MAIN_FIELD] ?? [];
    }

    public function isMainDataEmpty(): bool
    {
        return empty($this->getData()[static::MAIN_FIELD]);
    }
}