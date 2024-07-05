<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Api;

class CategoryStreamResultsProxy extends AbstractStreamResultsProxy
{
    public const MAIN_FIELD = 'categoryStream';
    public const CATEGORY_ATTRIBUTE_LIST_FIELD = 'attributeList';
    public const CATEGORY_ATTRIBUTES_LIST_CURSOR = self::MAIN_FIELD . '.' . self::CATEGORY_ATTRIBUTE_LIST_FIELD;
}