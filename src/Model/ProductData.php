<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Model;

class ProductData
{
    private array $ergonodeData;

    public function __construct(array $ergonodeData)
    {
        $this->ergonodeData = $ergonodeData;
    }

    public function findValueForAttributeCode(string $code): ?ProductAttributeData
    {
        foreach ($this->ergonodeData['attributeList']['edges'] ?? [] as $attributeList) {
            if ($attributeList['node']['attribute']['code'] === $code) {
                return new ProductAttributeData($attributeList['node']);
            }
        }

        return null;
    }
}
