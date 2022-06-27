<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Product\Api;

use Strix\Ergonode\Api\AbstractResultsProxy;

class ProductResultsProxy extends AbstractResultsProxy
{
    public const MAIN_FIELD = 'product';

    public function getProductData(): array
    {
        return $this->getMainData();
    }

    public function getVariants(): array
    {
        return $this->getMainData()['variantList']['edges'] ?? [];
    }

    public function getAttributeList(): array
    {
        return $this->getProductData()['attributeList']['edges'] ?? [];
    }

    public function filterAttributes(callable $callback): self
    {
        $filteredResults = clone $this;

        $filteredAttributes = array_filter(
            $filteredResults->getAttributeList() ?? [],
            $callback
        );

        $filteredResults->results['data'][static::MAIN_FIELD]['attributeList']['edges'] = $filteredAttributes;

        return $filteredResults;
    }

    /**
     * @param string[] $codes
     */
    public function filterAttributesByCodes(array $codes): self
    {
        return $this->filterAttributes(
            fn(array $attribute) => in_array($attribute['node']['attribute']['code'] ?? '', $codes)
        );
    }
}