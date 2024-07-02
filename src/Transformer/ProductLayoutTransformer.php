<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Service\ConfigService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;

class ProductLayoutTransformer implements ProductDataTransformerInterface
{
    public function __construct(
        private readonly ConfigService $configService
    ) {
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        if (null !== $productData->getSwProduct()?->getCmsPageId()) {
            return $productData;
        }

        $mapping = $this->configService->getTemplateLayoutMapping();
        $shopwareData = $productData->getShopwareData();

        $templateName = $productData->getErgonodeData()->getTemplateName();

        if (array_key_exists($templateName, $mapping)) {
            $cmsPageId = $mapping[$templateName];
            if (false === Uuid::isValid($cmsPageId)) {
                return $productData;
            }

            $shopwareData->setCmsPageId($cmsPageId);
        }

        $productData->setShopwareData($shopwareData);

        return $productData;
    }
}
