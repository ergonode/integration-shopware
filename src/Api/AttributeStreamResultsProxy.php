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

    public function addOptions(string $attributeCode, array $options): void
    {
        $edges = $this->results['data'][static::MAIN_FIELD]['edges'];

        foreach ($edges as $key => $edge) {
            if ($edge['node']['code'] === $attributeCode) {
                $edge['node']['optionList']['edges'] = array_merge($edge['node']['optionList']['edges'], $options);
                $this->results['data'][static::MAIN_FIELD]['edges'][$key] = $edge;

                return;
            }
        }
    }
}
