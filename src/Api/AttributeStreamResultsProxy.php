<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Api;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;

class AttributeStreamResultsProxy extends AbstractStreamResultsProxy
{
    public const MAIN_FIELD = 'attributeStream';

    /**
     * @param string[] $array
     */
    public function filterByAttributeTypes(array $array = []): AbstractStreamResultsProxy
    {
        return $this->filter(
            fn(array $attribute) => in_array(AttributeTypesEnum::getNodeType($attribute['node']), $array)
        );
    }

    /**
     * @param string[] $array
     */
    public function filterByCodes(array $array = []): AbstractStreamResultsProxy
    {
        return $this->filter(
            fn(array $attribute) => in_array($attribute['node']['code'] ?? null, $array)
        );
    }
}