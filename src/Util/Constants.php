<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

class Constants
{
    public const ATTRIBUTE_SCOPE_GLOBAL = 'GLOBAL';
    public const ATTRIBUTE_SCOPE_LOCAL = 'LOCAL';
    public const DEFAULT_TRANSLATION_KEY = 'sw-product-stream.filter.values.';

    public const PRODUCT_CUSTOM_FIELD_SET_NAME = 'ergonode_integration_custom_fields';

    public const SW_PRODUCT_MAPPABLE_FIELDS = [
        'active' => ['bool'],
        'name' => ['text', 'textarea', 'select'],
        'price.net' => ['numeric', 'price'],
        'price.gross' => ['numeric', 'price'],
        'tax.rate' => ['numeric'],
        'stock' => ['numeric'],
        'media' => ['gallery'],
        'ean' => ['text', 'textarea', 'select'],
        'manufacturerNumber' => ['text', 'textarea', 'select'],
        'manufacturer' => ['select'],
        'weight' => ['numeric'],
        'height' => ['numeric'],
        'width' => ['numeric'],
        'length' => ['numeric'],
        'customSearchKeywords' => ['multiselect'],
        'description' => ['text', 'textarea'],
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

    // put translations keys here if key is different than self::DEFAULT_TRANSLATION_KEY
    public const SW_PRODUCT_TRANSLATION_KEYS = [
        'media' => 'sw-product.list.columnMedia',
        'customSearchKeywords' => 'sw-settings-search.generalTab.configFields.customSearchKeywords',
        'metaTitle' => 'sw-product.seoForm.labelMetaTitle',
        'metaDescription' => 'sw-product.seoForm.labelMetaDescription',
        'keywords' => 'sw-product.seoForm.labelKeywords',
        'purchaseSteps' => 'sw-product.settingsForm.labelPurchaseSteps',
        'manufacturerNumber' => 'sw-product.settingsForm.labelManufacturerNumber',
        'tax.rate' => 'sw-product.priceForm.labelTaxRate',
        'price.net' => 'global.sw-price-field.labelPriceNet',
        'price.gross' => 'global.sw-price-field.labelPriceGross'
    ];
}
