<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Persistor;

use Ergonode\IntegrationShopware\DTO\CategoryTransformationDTO;
use Ergonode\IntegrationShopware\Struct\CategoryContainer;
use Ergonode\IntegrationShopware\Transformer\CategoryAttributesTransformerChain;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\EntityRepositoryNotFoundException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Throwable;

class CategoryAttributesPersistor
{
    public function __construct(
        private EntityRepository $categoryRepository,
        private CategoryAttributesTransformerChain $categoryAttributesTransformerChain,
        private DefinitionInstanceRegistry $definitionInstanceRegistry,
        private LoggerInterface $ergonodeSyncLogger,
        private EntityRepository $languageRepository,
    ) {
    }

    /**
     * @return array Persisted primary keys
     */
    public function persistCategoryAttributes(
        array $ergonodeCategoryEdges,
        CategoryContainer $categoryContainer,
        Context $context
    ): array
    {
        $payloads = [];

        $swLangCodes = $this->getShopwareLanguageCodes($context);
        foreach ($ergonodeCategoryEdges as $edge) {
            $node = $edge['node'] ?? null;
            $ergonodeCategoryCode = $node['code'];

            if (null === $categoryContainer->getShopwareId($ergonodeCategoryCode)) {
                //Category was not downloaded to shopware

                continue;
            }

            $dto = new CategoryTransformationDTO(
                $categoryContainer->getShopwareId($ergonodeCategoryCode),
                $node,
                $swLangCodes
            );

            $this->categoryAttributesTransformerChain->transform($dto, $context);
            $payloads[] = $dto->getShopwareData();

            try {
                $this->deleteEntities($dto, $context);
            } catch (Throwable $e) {
                $this->ergonodeSyncLogger->error(
                    'Error while deleting related entities. Category delete has been omitted.',
                    [
                        'sw_category_id' => $dto->getShopwareCategoryId(),
                        'ids' => $dto->getEntitiesToDelete(),
                        'message' => $e->getMessage(),
                        'file' => $e->getFile() . ':' . $e->getLine(),
                    ]
                );

                return [];
            }
        }

        $writeResult = $this->categoryRepository->upsert($payloads, $context);

        return $writeResult->getPrimaryKeys(CategoryDefinition::ENTITY_NAME);
    }

    private function deleteEntities(CategoryTransformationDTO $dto, Context $context): void
    {
        foreach ($dto->getEntitiesToDelete() as $entityName => $payload) {
            if (!is_array($payload)) {
                continue;
            }

            try {
                $repository = $this->definitionInstanceRegistry->getRepository($entityName);
                $repository->delete(array_values($payload), $context);

                $this->ergonodeSyncLogger->info(
                    'Deleting entities',
                    [
                        'entity' => $entityName,
                        'count' => count($payload),
                    ]
                );
            } catch (EntityRepositoryNotFoundException $e) {
                continue;
            }
        }
    }

    private function getShopwareLanguageCodes(Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addAssociation('locale');
        $languages = $this->languageRepository->search($criteria, $context);

        $result = [];
        foreach ($languages as $language) {
            $result[] = $language->getLocale()->getCode();
        }

        return $result;
    }
}
