<?php

declare(strict_types=1);

namespace Strix\Ergonode\Exception;

class MissingRequiredProductMappingException extends \Exception
{
    public function __construct(array $missingMappings)
    {
        parent::__construct(
            \sprintf(
                'Missing required product attributes mapping for following Shopware keys: %s',
                \implode(', ', $missingMappings)
            )
        );
    }
}