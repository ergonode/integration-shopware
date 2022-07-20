<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

class Constants
{
    public const ATTRIBUTE_SCOPE_GLOBAL = 'GLOBAL';
    public const ATTRIBUTE_SCOPE_LOCAL = 'LOCAL';

    public const PRODUCT_CUSTOM_FIELD_SET_NAME = 'ergonode_integration_custom_fields';

    public const SW_PRODUCT_MAPPABLE_FIELDS = [
        'active',
        'name',
        'stock',
        'media',
        'ean',
        'manufacturerNumber',
        'weight',
        'height',
        'width',
        'length',
        'customSearchKeywords',
        'description',
        'metaTitle',
        'metaDescription',
        'keywords',
        'purchaseSteps',
        'maxPurchase',
        'minPurchase',
        'packUnit',
        'packUnitPlural',
        'purchaseUnit',
        'referenceUnit',
        'isCloseout',
        'shippingFree',
        'restockTime',
        'markAsTopseller',
    ];
}