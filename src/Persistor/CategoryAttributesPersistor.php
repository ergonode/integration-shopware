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
use Throwable;

class CategoryAttributesPersistor
{
    public function __construct(
        private EntityRepository $categoryRepository,
        private CategoryAttributesTransformerChain $categoryAttributesTransformerChain,
        private DefinitionInstanceRegistry $definitionInstanceRegistry,
        private LoggerInterface $ergonodeSyncLogger,
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
        foreach ($ergonodeCategoryEdges as $edge) {
            $node = $edge['node'] ?? null;
            $ergonodeCategoryCode = $node['code'];

            if (null === $categoryContainer->getShopwareId($ergonodeCategoryCode)) {
                //Category was not downloaded to shopware

                continue;
            }

            if ($edge['node']['attributeList']['edges'] === []) {
                // Empty attributes in ergo

                $this->ergonodeSyncLogger->info(
                    'No category attributes in ergonode',
                    [
                        'code' => $ergonodeCategoryCode,
                    ]
                );
                continue;
            }

            $dto = new CategoryTransformationDTO(
                $categoryContainer->getShopwareId($ergonodeCategoryCode),
                $node
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
}
