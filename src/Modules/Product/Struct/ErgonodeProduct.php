<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Product\Struct;

use Strix\Ergonode\Struct\AbstractErgonodeEntity;

class ErgonodeProduct extends AbstractErgonodeEntity
{
    protected string $createdAt;

    protected ?string $editedAt = null;

    protected string $typename;

    protected string $templateName;

    /**
     * @var string[]
     */
    protected array $categoryCodes = [];

    /**
     * @var string[]
     */
    protected array $attributeCodes = [];

    public function getSku(): string
    {
        return $this->getCode();
    }

    public function setSku(string $sku): void
    {
        $this->code = $sku;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getEditedAt(): ?string
    {
        return $this->editedAt;
    }

    public function setEditedAt(?string $editedAt): void
    {
        $this->editedAt = $editedAt;
    }

    public function getTypename(): string
    {
        return $this->typename;
    }

    public function setTypename(string $typename): void
    {
        $this->typename = $typename;
    }

    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    public function setTemplateName(string $templateName): void
    {
        $this->templateName = $templateName;
    }

    public function getCategoryCodes(): array
    {
        return $this->categoryCodes;
    }

    public function setCategoryCodes(array $categoryCodes): void
    {
        $this->categoryCodes = $categoryCodes;
    }

    public function addCategoryCode(string $categoryCode): void
    {
        $this->categoryCodes[] = $categoryCode;
    }

    public function getAttributeCodes(): array
    {
        return $this->attributeCodes;
    }

    public function setAttributeCodes(array $attributeCodes): void
    {
        $this->attributeCodes = $attributeCodes;
    }

    public function addAttributeCode(string $attributeCode): void
    {
        $this->attributeCodes[] = $attributeCode;
    }
}