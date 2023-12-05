<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Model;

class ProductAttribute
{
    public const TYPE_IMAGE            = 'ImageAttribute';
    public const TYPE_GALLERY          = 'GalleryAttribute';
    public const TYPE_SELECT           = 'SelectAttribute';
    public const TYPE_MULTI_SELECT     = 'MultiSelectAttribute';
    public const TYPE_TEXT             = 'TextAttribute';
    public const TYPE_TEXTAREA         = 'TextareaAttribute';
    public const TYPE_PRODUCT_RELATION = 'ProductRelationAttribute';
    public const TYPE_FILE             = 'FileAttribute';
    public const TYPE_PRICE            = 'PriceAttribute';
    public const TYPE_NUMERIC          = 'NumericAttribute';
    public const TYPE_UNIT             = 'UnitAttribute';
    public const TYPE_DATE             = 'DateAttribute';
    /** Bool type is used only in Shopware, not exist in Ergonode. Created for data processing purposes */
    public const TYPE_BOOL             = 'BoolAttribute';

    public const SCOPE_LOCAL  = 'local';
    public const SCOPE_GLOBAL = 'global';

    /**
     * @var ProductSimpleAttributeTranslation[]
     */
    protected array $translations = [];

    public function __construct(
        private readonly string $code,
        private readonly string $type
    ) {

    }

    public function addTranslation(ProductSimpleAttributeTranslation $translation): void
    {
        $this->translations[$translation->getLanguage()] = $translation;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function getTranslation(string $language): ?ProductSimpleAttributeTranslation
    {
        return $this->translations[$language] ?? null;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
