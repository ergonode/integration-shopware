<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\DTO;

use Shopware\Core\Content\Product\ProductEntity;

class ProductTransformationDTO
{
    private ProductErgonodeData $ergonodeData;

    private ProductShopwareData $shopwareData;

    private ?ProductEntity $swProduct = null;

    private array $entitiesToDelete = [];

    private array $bindingCodes = [];

    private bool $isInitialPaginatedImport;

    private string $defaultLanguage;

    public function __construct(
        ProductErgonodeData $ergonodeData,
        ProductShopwareData $shopwareData,
        string $defaultLanguage,
        bool $isInitialPaginatedImport = false,
    ) {
        $this->ergonodeData = $ergonodeData;
        $this->shopwareData = $shopwareData;
        $this->isInitialPaginatedImport = $isInitialPaginatedImport;
        $this->defaultLanguage = $defaultLanguage;
    }

    public function getErgonodeData(): ProductErgonodeData
    {
        return $this->ergonodeData;
    }

    public function getShopwareData(): ProductShopwareData
    {
        return $this->shopwareData;
    }

    public function setShopwareData(ProductShopwareData $shopwareData): void
    {
        $this->shopwareData = $shopwareData;
    }

    public function getSwProduct(): ?ProductEntity
    {
        return $this->swProduct;
    }

    public function setSwProduct(?ProductEntity $swProduct): void
    {
        $this->swProduct = $swProduct;
    }

    public function getSwProductId(): ?string
    {
        $swProduct = $this->getSwProduct();
        if (null === $swProduct) {
            return null;
        }

        return $swProduct->getId();
    }

    public function getBindingCodes(): array
    {
        return $this->bindingCodes;
    }

    public function setBindingCodes(array $bindingCodes): void
    {
        $this->bindingCodes = $bindingCodes;
    }

    public function ergonodeDataHasVariants(): bool
    {
        return count($this->ergonodeData->getVariants()) > 0;
    }

    public function swProductHasVariants(): bool
    {
        return null !== $this->swProduct && false === empty($this->swProduct->getChildren());
    }

    public function isVariant(): bool
    {
        return false === empty($this->bindingCodes);
    }

    public function isUpdate(): bool
    {
        return null !== $this->swProduct;
    }

    public function getEntitiesToDelete(): array
    {
        return $this->entitiesToDelete;
    }

    /**
     * @param array $payload [ 'id' => 'entityId' ] OR [ [ 'id' => 'entityId1' ], [ 'id' => 'entityId2' ] ]
     */
    public function addEntitiesToDelete(string $entityName, array $payload): void
    {
        if (empty($payload)) {
            return;
        }

        if (!isset($this->entitiesToDelete[$entityName])) {
            $this->entitiesToDelete[$entityName] = [];
        }

        foreach ($payload as $payloadPart) {
            if (!is_array($payloadPart)) {
                $this->entitiesToDelete[$entityName][] = $payload;

                return;
            }

            $this->addEntitiesToDelete($entityName, $payloadPart);
        }
    }

    public function isInitialPaginatedImport(): bool
    {
        return $this->isInitialPaginatedImport;
    }

    public function getDefaultLanguage(): string
    {
        return $this->defaultLanguage;
    }
}
