<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\DTO;

use Shopware\Core\Content\Product\ProductEntity;

class ProductTransformationDTO
{
    private array $ergonodeData;

    private array $shopwareData;

    private ?ProductEntity $swProduct = null;

    private array $entitiesToDelete = [];

    private array $bindingCodes = [];

    private bool $isInitialPaginatedImport;

    public function __construct(array $ergonodeData, array $shopwareData = [], bool $isInitialPaginatedImport = false)
    {
        $this->ergonodeData = $ergonodeData;
        $this->shopwareData = $shopwareData;
        $this->isInitialPaginatedImport = $isInitialPaginatedImport;
    }

    public function getErgonodeData(): array
    {
        return $this->ergonodeData;
    }

    public function setErgonodeData(array $ergonodeData): void
    {
        $this->ergonodeData = $ergonodeData;
    }

    public function getShopwareData(): array
    {
        return $this->shopwareData;
    }

    public function setShopwareData(array $shopwareData): void
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
        return false === empty($this->ergonodeData['variantList']['edges']);
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

    public function isCreate(): bool
    {
        return null === $this->swProduct;
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

    public function unsetSwData(string $fieldKey): void
    {
        unset($this->shopwareData[$fieldKey]);
    }

    public function isInitialPaginatedImport(): bool
    {
        return $this->isInitialPaginatedImport;
    }
}
