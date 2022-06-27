<?php

declare(strict_types=1);

namespace Strix\Ergonode\Enum;

class ProductAttributeTypesEnum extends AbstractEnum
{
    public const MULTIMEDIA = 'MultimediaAttributeValue';
    public const MULTIMEDIA_ARRAY = 'MultimediaArrayAttributeValue';
    public const NUMERIC = 'NumericAttributeValue';
    public const PRODUCT_ARRAY = 'ProductArrayAttributeValue';
    public const STRING = 'StringAttributeValue';
    public const STRING_ARRAY = 'StringArrayAttributeValue';

    public static function getNodeType(array $node): string
    {
        return $node['valueTranslations'][0]['__typename']; // todo find other way
    }
}