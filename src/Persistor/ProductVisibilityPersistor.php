<?php

declare(strict_types=1);

namespace Strix\Ergonode\Persistor;

use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Strix\Ergonode\DTO\ProductVisibilityDTO;
use Strix\Ergonode\Transformer\ProductVisibilityTransformer;

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
        $deleted = $this->productVisibilityRepository->delete($dto->getDeletePayload(), $context);

        return [
            ProductVisibilityDefinition::ENTITY_NAME . '.created' =>
                $created->getPrimaryKeys(ProductVisibilityDefinition::ENTITY_NAME),
            ProductVisibilityDefinition::ENTITY_NAME . '.deleted' =>
                $deleted->getPrimaryKeys(ProductVisibilityDefinition::ENTITY_NAME),
        ];
    }
}