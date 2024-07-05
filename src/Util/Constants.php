<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum as Attr;
use Ergonode\IntegrationShopware\Model\ProductAttribute;

class Constants
{
    public const STATE_PRODUCT_APPEND_CATEGORIES = 'ergonode-product-append-categories';

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
        'weight' => [Attr::NUMERIC, Attr::UNIT],
        'height' => [Attr::NUMERIC, Attr::UNIT],
        'width' => [Attr::NUMERIC, Attr::UNIT],
        'length' => [Attr::NUMERIC, Attr::UNIT],
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


    public const SW_PRODUCT_MAPPABLE_FIELD_TYPES = [
        'active' => [ProductAttribute::TYPE_BOOL],
        'name' => [ProductAttribute::TYPE_TEXT, ProductAttribute::TYPE_TEXTAREA, ProductAttribute::TYPE_SELECT],
        'price.net' => [ProductAttribute::TYPE_NUMERIC, ProductAttribute::TYPE_PRICE],
        'price.gross' => [ProductAttribute::TYPE_NUMERIC, ProductAttribute::TYPE_PRICE],
        'tax.rate' => [ProductAttribute::TYPE_NUMERIC],
        'stock' => [ProductAttribute::TYPE_NUMERIC],
        'media' => [ProductAttribute::TYPE_GALLERY],
        'ean' => [ProductAttribute::TYPE_TEXT, ProductAttribute::TYPE_TEXTAREA, ProductAttribute::TYPE_SELECT],
        'manufacturerNumber' => [ProductAttribute::TYPE_TEXT, ProductAttribute::TYPE_TEXTAREA, ProductAttribute::TYPE_SELECT],
        'manufacturer' => [ProductAttribute::TYPE_SELECT],
        'weight' => [ProductAttribute::TYPE_NUMERIC, ProductAttribute::TYPE_UNIT],
        'height' => [ProductAttribute::TYPE_NUMERIC, ProductAttribute::TYPE_UNIT],
        'width' => [ProductAttribute::TYPE_NUMERIC, ProductAttribute::TYPE_UNIT],
        'length' => [ProductAttribute::TYPE_NUMERIC, ProductAttribute::TYPE_UNIT],
        'customSearchKeywords' => [ProductAttribute::TYPE_MULTI_SELECT],
        'description' => [ProductAttribute::TYPE_TEXT, ProductAttribute::TYPE_TEXTAREA],
        'metaTitle' => [ProductAttribute::TYPE_TEXT, ProductAttribute::TYPE_TEXTAREA, ProductAttribute::TYPE_SELECT],
        'metaDescription' => [ProductAttribute::TYPE_TEXT, ProductAttribute::TYPE_TEXTAREA, ProductAttribute::TYPE_SELECT],
        'keywords' => [ProductAttribute::TYPE_TEXT, ProductAttribute::TYPE_TEXTAREA, ProductAttribute::TYPE_SELECT],
        'purchaseSteps' => [ProductAttribute::TYPE_NUMERIC],
        'maxPurchase' => [ProductAttribute::TYPE_NUMERIC],
        'minPurchase' => [ProductAttribute::TYPE_NUMERIC],
        'packUnit' => [ProductAttribute::TYPE_TEXT, ProductAttribute::TYPE_TEXTAREA, ProductAttribute::TYPE_SELECT],
        'packUnitPlural' => [ProductAttribute::TYPE_TEXT, ProductAttribute::TYPE_TEXTAREA, ProductAttribute::TYPE_SELECT],
        'purchaseUnit' => [ProductAttribute::TYPE_NUMERIC],
        'referenceUnit' => [ProductAttribute::TYPE_NUMERIC],
        'isCloseout' => [ProductAttribute::TYPE_BOOL],
        'shippingFree' => [ProductAttribute::TYPE_BOOL],
        'restockTime' => [ProductAttribute::TYPE_NUMERIC],
        'markAsTopseller' => [ProductAttribute::TYPE_BOOL],
        'deliveryTime' => [ProductAttribute::TYPE_SELECT],
        'scaleUnit' => [ProductAttribute::TYPE_SELECT],
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
        'scaleUnit' => 'sw-product.packagingForm.labelUnit',
        'markAsTopseller' => 'sw-product.settingsForm.labelMarkAsTopseller',
    ];

    /**
     * Those mappings are processed by specific transformers. Should not be processed in main ProductTransformer
     */
    public const MAPPINGS_WITH_SEPARATE_TRANSFORMERS = [
        'price.net' ,
        'price.gross',
        'tax.rate',
        'media',
        'manufacturer',
        'deliveryTime',
        'scaleUnit',
    ];


    public const SW_CATEGORY_ATTRIBUTES_MAPPABLE_FIELDS = [
        # General tab
        'active' => [Attr::BOOL],
        'tags' => [Attr::MULTISELECT],
        'type' => [Attr::SELECT],
        # General `product` type
        'visible' => [Attr::BOOL],
        'media' => [Attr::IMAGE],
        'description' => [Attr::TEXTAREA],
        # General `url` type
        'linkNewTab' => [Attr::BOOL],
        'linkType' => [Attr::SELECT],
        'externalLink' => [Attr::TEXT],
        'internalLink' => [Attr::TEXT],
        # Seo tab
        'metaTitle' => [Attr::TEXT],
        'metaDescription' => [Attr::TEXTAREA],
        'keywords' => [Attr::TEXT],
    ];

    public const SW_CATEGORY_ATTRIBUTES_TRANSLATION_KEYS = [
        # General tab
        'active' => 'sw-category.base.general.isCategoryActiveLabel',
        'tags' => 'sw-category.base.general.labelCategoryTags',
        'type' => 'sw-category.base.general.types.title',
        'visible' => 'sw-category.base.menu.visible',
        'media' => 'sw-category.base.menu.imageLabel',
        'description' => 'sw-category.base.menu.descriptionLabel',
        # Links
        'linkNewTab' => 'sw-category.base.link.linkNewTabLabel',
        'linkType' => 'sw-category.base.link.mainTypeLabel',
        'externalLink' => 'sw-category.base.link.externalLink',
        'internalLink' => 'sw-category.base.link.internalLink',
        # Seo tab
        'metaTitle' => 'sw-category.base.seo.labelMetaTitle',
        'metaDescription' => 'sw-landing-page.base.seo.labelMetaDescription',
        'keywords' => 'sw-category.base.seo.labelKeywords',
    ];
}
