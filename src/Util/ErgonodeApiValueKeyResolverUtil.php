<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

class ErgonodeApiValueKeyResolverUtil
{
    public static function resolve(string $typename): string
    {
        switch ($typename) {
            case 'StringAttributeValue':
                return 'value_string';
            case 'NumericAttributeValue':
                return 'value_numeric';
            case 'StringArrayAttributeValue':
                return 'value_array';
            case 'MultimediaAttributeValue':
                return 'value_multimedia';
            case 'MultimediaArrayAttributeValue':
                return 'value_multimedia_array';
            case 'ProductArrayAttributeValue':
                return 'value_product_array';
            default:
                throw new \RuntimeException(\sprintf('Unknown value typename: %s', $typename));
        }
    }
}