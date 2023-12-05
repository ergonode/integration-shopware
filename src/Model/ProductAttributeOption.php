<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Model;

class ProductAttributeOption
{
    public function __construct(private readonly string $code, private readonly array $name)
    {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): array
    {
        return $this->name;
    }
}
