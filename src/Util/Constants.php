<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

class Constants
{
    public const ATTRIBUTE_SCOPE_GLOBAL = 'GLOBAL';
    public const ATTRIBUTE_SCOPE_LOCAL = 'LOCAL';

    public const PRODUCT_CUSTOM_FIELD_SET_NAME = 'ergonode_integration_custom_fields';

    public const SW_PRODUCT_MAPPABLE_FIELDS = [
        'active' => 'bool',
        'name' => 'text',
        'stock' => 'numeric',
        'media' => 'gallery',
        'ean' => 'text',
        'manufacturerNumber' => 'text',
        'weight' => 'numeric',
        'height' => 'numeric',
        'width' => 'numeric',
        'length' => 'numeric',
        'customSearchKeywords' => 'text',
        'description' => 'text',
        'metaTitle' => 'text',
        'metaDescription' => 'text',
        'keywords' => 'text',
        'purchaseSteps' => 'numeric',
        'maxPurchase' => 'numeric',
        'minPurchase' => 'numeric',
        'packUnit' => 'text',
        'packUnitPlural' => 'text',
        'purchaseUnit' => 'numeric',
        'referenceUnit' => 'numeric',
        'isCloseout' => 'bool',
        'shippingFree' => 'bool',
        'restockTime' => 'numeric',
        'markAsTopseller' => 'bool',
    ];
}