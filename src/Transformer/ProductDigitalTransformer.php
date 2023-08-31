<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Manager\FileManager;
use Ergonode\IntegrationShopware\Model\ProductAttributeData;
use Ergonode\IntegrationShopware\Model\ProductData;
use Ergonode\IntegrationShopware\Model\ProductImageData;
use Ergonode\IntegrationShopware\Service\ConfigService;
use Shopware\Core\Content\Product\Aggregate\ProductDownload\ProductDownloadCollection;
use Shopware\Core\Content\Product\Aggregate\ProductDownload\ProductDownloadEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ProductDigitalTransformer implements ProductDataTransformerInterface
{
    private ConfigService $configService;

    private FileManager $fileManager;

    private EntityRepository $productDownloadRepository;

    public function __construct(
        ConfigService $configService,
        FileManager $fileManager,
        EntityRepository $productDownloadRepository
    ) {

        $this->configService = $configService;
        $this->fileManager = $fileManager;
        $this->productDownloadRepository = $productDownloadRepository;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        if (!in_array($productData->getSku(), $this->configService->getDigitalProductKeys())) {
            if ($productData->getSwProductId()) {
                $this->clearOldDownloads([], $productData->getSwProductId(), $context);
            }

            return $productData;
        }
        $swData = $productData->getShopwareData();
        $code = $this->configService->getDigitalProductAttribute();
        if (!$code) {
            return $productData;
        }

        $ergoData = $productData->getErgonodeData();

        $productModel = new ProductData($ergoData);
        $value = $productModel->findValueForAttributeCode($code);

        $downloads = $this->getDownloadsFromValue($value, $productData, $context);

        if ($productData->getSwProductId()) {
            $this->clearOldDownloads($downloads, $productData->getSwProductId(), $context);
        }
        $swData['downloads'] = $downloads;

        $productData->setShopwareData($swData);

        return $productData;
    }

    private function getDownloadData(
        ProductImageData $value,
        ?ProductDownloadCollection $existingDownloads,
        Context $context
    ): array {
        $mediaId = $this->fileManager->persist($value->toArray(), $context);
        if ($existingDownloads) {
            /** @var ProductDownloadEntity $existingDownload */
            foreach ($existingDownloads as $existingDownload) {
                if ($existingDownload->getMediaId() === $mediaId) {
                    return [
                        'mediaId' => $mediaId,
                        'id' => $existingDownload->getId(),
                    ];
                }
            }
        }

        return [
            'mediaId' => $mediaId,
        ];
    }

    private function clearOldDownloads(array $newDownloads, string $productId, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productId', $productId));

        $results = $this->productDownloadRepository->search($criteria, $context);
        $idsToDelete = [];
        /** @var ProductDownloadEntity $productDownload */
        foreach ($results as $productDownload) {
            foreach ($newDownloads as $newDownload) {
                if (isset($newDownload['id']) && $newDownload['id'] == $productDownload->getId()) {
                    continue 2;
                }
            }
            $idsToDelete[] = ['id' => $productDownload->getId()];
        }

        if (!empty($idsToDelete)) {
            $this->productDownloadRepository->delete($idsToDelete, $context);
        }
    }

    private function getDownloadsFromValue(
        ?ProductAttributeData $value,
        ProductTransformationDTO $productData,
        Context $context
    ): array {
        $downloads = [];
        if ($value instanceof ProductAttributeData) {
            $existingDownloads = $productData->getSwProduct()->getDownloads();
            $translation = $value->getTranslation('pl_PL');
            $translationData = is_array($translation) ? $translation : [$translation];
            foreach ($translationData as $value) {
                if (is_null($value)) {
                    continue;
                }
                $downloadData = $this->getDownloadData($value, $existingDownloads, $context);
                if ($downloadData) {
                    $downloads[] = $downloadData;
                }
            }
        }

        return $downloads;
    }

}
