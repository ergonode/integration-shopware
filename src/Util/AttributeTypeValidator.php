<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

use Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingEntity;
use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Ergonode\IntegrationShopware\Exception\InvalidAttributeTypeException;

class AttributeTypeValidator
{
    /**
     * @throws InvalidAttributeTypeException only if $throwException equals true
     */
    public function validate(array $ergonodeAttribute, ErgonodeAttributeMappingEntity $mapping, bool $throwException): bool
    {
        $swKey = $mapping->getShopwareKey();
        $validTypes = Constants::SW_PRODUCT_MAPPABLE_FIELDS[$swKey];

        if (empty($ergonodeAttribute)) {
            if ($throwException) {
                throw new InvalidAttributeTypeException($mapping, $validTypes);
            }

            return false;
        }

        $actualType = AttributeTypesEnum::getShortNodeType($ergonodeAttribute);

        $valid = in_array($actualType, $validTypes);

        if ($throwException && false === $valid) {
            throw new InvalidAttributeTypeException($mapping, $validTypes, $actualType);
        }

        return $valid;
    }
}