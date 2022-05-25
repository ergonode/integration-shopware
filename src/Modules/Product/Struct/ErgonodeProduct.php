<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Product\Struct;

use Strix\Ergonode\Struct\AbstractErgonodeEntity;

class ErgonodeProduct extends AbstractErgonodeEntity
{
    private string $sku;

    private string $createdAt;

    private ?string $editedAt = null;

    private string $typename;

    private string $templateName;

    private array $categoryCodes = [];

    private array $attributeCodes = [];

    public function setFromResponse(array $response): void
    {
        $this->sku = $response['sku'];
        $this->createdAt = $response['createdAt'];
        $this->editedAt = $response['editedAt'];
        $this->typename = $response['__typename'];
        $this->templateName = $response['template']['name'];

        foreach ($response['categoryList']['edges'] as $category) {
            $this->categoryCodes[] = $category['node']['code'];
        }

        foreach ($response['attributeList']['edges'] as $attribute) {
            $this->attributeCodes[] = $attribute['node']['attribute']['code'];
        }

        $this->setPrimaryValue($this->sku);
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getEditedAt(): ?string
    {
        return $this->editedAt;
    }

    public function getTypename(): string
    {
        return $this->typename;
    }

    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    public function getCategoryCodes(): array
    {
        return $this->categoryCodes;
    }

    public function getAttributeCodes(): array
    {
        return $this->attributeCodes;
    }
}