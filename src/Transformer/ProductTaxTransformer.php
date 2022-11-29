<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Provider\TaxProvider;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\Tax\TaxDefinition;

class ProductTaxTransformer implements ProductDataTransformerInterface
{
    private const AUTOGENERATED_TAX_NAME = 'Ergonode Autogenerated (%1$.1f%%)';

    private TaxProvider $taxProvider;

    private EntityRepositoryInterface $taxRepository;

    public function __construct(TaxProvider $taxProvider, EntityRepositoryInterface $taxRepository)
    {
        $this->taxProvider = $taxProvider;
        $this->taxRepository = $taxRepository;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $swData = $productData->getShopwareData();
        $productTaxRate = $swData['tax']['rate'] ?? null;

        $taxId = $this->getTaxEntityId($productTaxRate, $context);

        $swData['taxId'] = $taxId;
        unset($swData['tax']);

        $productData->setShopwareData($swData);

        return $productData;
    }

    /**
     * Gets tax entity ID for product payload, based on provided tax rate.
     * 1. returns first tax based on position if taxRate is null
     * 2. returns first tax if entity for given taxRate exists
     * 3. creates tax entity if taxRate does not exist using autogenerated name template and returns its ID
     */
    private function getTaxEntityId(?float $taxRate, Context $context): string
    {
        if (null === $taxRate) {
            $defaultTax = $this->taxProvider->getDefaultTax($context);
            if (null === $defaultTax) {
                throw new \RuntimeException('Could not load default tax entity');
            }

            return $defaultTax->getId();
        }

        $taxEntity = $this->taxProvider->getByTaxRate($taxRate, $context);
        if (null !== $taxEntity) {
            return $taxEntity->getId();
        }
        return $this->taxRepository->create(
            [
                [
                    'taxRate' => $taxRate,
                    'name' => \sprintf(self::AUTOGENERATED_TAX_NAME, $taxRate)
                ]
            ],
            $context
        )->getPrimaryKeys(TaxDefinition::ENTITY_NAME)[0];
    }
}