<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Persistor;

use Ergonode\IntegrationShopware\DTO\ProductVisibilityDTO;
use Ergonode\IntegrationShopware\Transformer\ProductVisibilityTransformer;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

class ProductVisibilityPersistor
{
    private ProductVisibilityTransformer $transformer;

    private EntityRepositoryInterface $productVisibilityRepository;

    public function __construct(
        ProductVisibilityTransformer $transformer,
        EntityRepositoryInterface $productVisibilityRepository
    ) {
        $this->transformer = $transformer;
        $this->productVisibilityRepository = $productVisibilityRepository;
    }

    public function persist(string $sku, array $salesChannelIds, Context $context): array
    {
        $dto = new ProductVisibilityDTO($sku, $salesChannelIds);
        $dto = $this->transformer->transform($dto, $context);

        $created = $this->productVisibilityRepository->create($dto->getCreatePayload(), $context);

        $this->productVisibilityRepository->delete($dto->getDeletePayload(), $context);

        return $created->getPrimaryKeys(ProductVisibilityDefinition::ENTITY_NAME);
    }
}