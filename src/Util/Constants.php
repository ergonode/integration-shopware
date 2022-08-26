<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

class Constants
{
    public const ATTRIBUTE_SCOPE_GLOBAL = 'GLOBAL';
    public const ATTRIBUTE_SCOPE_LOCAL = 'LOCAL';

    public const PRODUCT_CUSTOM_FIELD_SET_NAME = 'ergonode_integration_custom_fields';

    public const SW_PRODUCT_MAPPABLE_FIELDS = [
        'active' => ['bool'],
        'name' => ['text', 'textarea', 'select'],
        'stock' => ['numeric'],
        'media' => ['gallery'],
        'ean' => ['text', 'textarea', 'select'],
        'manufacturerNumber' => ['text', 'textarea', 'select'],
        'weight' => ['numeric'],
        'height' => ['numeric'],
        'width' => ['numeric'],
        'length' => ['numeric'],
        'customSearchKeywords' => ['multiselect'],
        'description' => ['text', 'textarea', 'select'],
        'metaTitle' => ['text', 'textarea', 'select'],
        'metaDescription' => ['text', 'textarea', 'select'],
        'keywords' => ['text', 'textarea', 'select'],
        'purchaseSteps' => ['numeric'],
        'maxPurchase' => ['numeric'],
        'minPurchase' => ['numeric'],
        'packUnit' => ['text', 'textarea', 'select'],
        'packUnitPlural' => ['text', 'textarea', 'select'],
        'purchaseUnit' => ['numeric'],
        'referenceUnit' => ['numeric'],
        'isCloseout' => ['bool'],
        'shippingFree' => ['bool'],
        'restockTime' => ['numeric'],
        'markAsTopseller' => ['bool'],
    ];
}