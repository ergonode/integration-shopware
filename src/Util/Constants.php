<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum as Attr;

class Constants
{
    public const PRODUCT_CUSTOM_FIELD_SET_NAME = 'ergonode_integration_custom_fields';

    public const SW_PRODUCT_MAPPABLE_FIELDS = [
        'active' => [Attr::BOOL],
        'name' => [Attr::TEXT, Attr::TEXTAREA, Attr::SELECT],
        'price.net' => [Attr::NUMERIC, Attr::PRICE],
        'price.gross' => [Attr::NUMERIC, Attr::PRICE],
        'tax.rate' => [Attr::NUMERIC],
        'stock' => [Attr::NUMERIC],
        'media' => [Attr::GALLERY],
        'ean' => [Attr::TEXT, Attr::TEXTAREA, Attr::SELECT],
        'manufacturerNumber' => [Attr::TEXT, Attr::TEXTAREA, Attr::SELECT],
        'manufacturer' => [Attr::SELECT],
        'weight' => [Attr::NUMERIC],
        'height' => [Attr::NUMERIC],
        'width' => [Attr::NUMERIC],
        'length' => [Attr::NUMERIC],
        'customSearchKeywords' => [Attr::MULTISELECT],
        'description' => [Attr::TEXT, Attr::TEXTAREA],
        'metaTitle' => [Attr::TEXT, Attr::TEXTAREA, Attr::SELECT],
        'metaDescription' => [Attr::TEXT, Attr::TEXTAREA, Attr::SELECT],
        'keywords' => [Attr::TEXT, Attr::TEXTAREA, Attr::SELECT],
        'purchaseSteps' => [Attr::NUMERIC],
        'maxPurchase' => [Attr::NUMERIC],
        'minPurchase' => [Attr::NUMERIC],
        'packUnit' => [Attr::TEXT, Attr::TEXTAREA, Attr::SELECT],
        'packUnitPlural' => [Attr::TEXT, Attr::TEXTAREA, Attr::SELECT],
        'purchaseUnit' => [Attr::NUMERIC],
        'referenceUnit' => [Attr::NUMERIC],
        'isCloseout' => [Attr::BOOL],
        'shippingFree' => [Attr::BOOL],
        'restockTime' => [Attr::NUMERIC],
        'markAsTopseller' => [Attr::BOOL],
        'deliveryTime' => [Attr::SELECT],
        'scaleUnit' => [Attr::SELECT],
    ];

    public const DEFAULT_TRANSLATION_KEY = 'sw-product-stream.filter.values.';

    // put translations keys here if key is different from self::DEFAULT_TRANSLATION_KEY
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
        'price.gross' => 'global.sw-price-field.labelPriceGross',
        'purchaseUnit' => 'sw-product.priceForm.labelPurchaseUnit',
        'referenceUnit' => 'sw-product.priceForm.labelReferenceUnit',
        'scaleUnit' => 'sw-product-stream.filter.values.unit',
    ];
}
