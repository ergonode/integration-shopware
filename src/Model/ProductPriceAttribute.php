<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Model;

class ProductPriceAttribute extends ProductAttribute
{
    public function __construct(string $code, string $type, private readonly string $currency)
    {
        parent::__construct($code, $type);
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}
