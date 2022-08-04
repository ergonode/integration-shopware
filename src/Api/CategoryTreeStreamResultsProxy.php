<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Api;

class CategoryTreeStreamResultsProxy extends AbstractStreamResultsProxy
{
    public const MAIN_FIELD = 'categoryTreeStream';
    public const CATEGORY_TREE_LEAF_LIST_FIELD = 'categoryTreeLeafList';
    public const TREE_LEAF_LIST_CURSOR = self::MAIN_FIELD . '.' . self::CATEGORY_TREE_LEAF_LIST_FIELD;
}