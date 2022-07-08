<?php

declare(strict_types=1);

namespace Strix\Ergonode\Processor;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\Language\LanguageCollection;
use Strix\Ergonode\Api\Client\ErgonodeGqlClientInterface;
use Strix\Ergonode\Manager\ErgonodeCursorManager;
use Strix\Ergonode\Modules\Category\Api\CategoryTreeStreamResultsProxy;
use Strix\Ergonode\Persistor\CategoryPersistor;
use Strix\Ergonode\Provider\CategoryProvider;
use Strix\Ergonode\Provider\ConfigProvider;
use Strix\Ergonode\Provider\LanguageProvider;
use Strix\Ergonode\QueryBuilder\CategoryQueryBuilder;
use Strix\Ergonode\Util\IsoCodeConverter;

class CategoryTreeSyncProcessor
{
    public const DEFAULT_CATEGORY_COUNT = 10;

    private ErgonodeGqlClientInterface $gqlClient;
    private CategoryQueryBuilder $categoryQueryBuilder;
    private CategoryPersistor $categoryPersistor;
    private ErgonodeCursorManager $cursorManager;
    private LoggerInterface $logger;
    private ConfigProvider $configProvider;
    private LanguageProvider $languageProvider;
    private CategoryProvider $categoryProvider;

    public function __construct(
        ErgonodeGqlClientInterface $gqlClient,
        CategoryQueryBuilder $categoryQueryBuilder,
        CategoryPersistor $categoryPersistor,
        ErgonodeCursorManager $cursorManager,
        LoggerInterface $syncLogger,
        ConfigProvider $configProvider,
        LanguageProvider $languageProvider,
        CategoryProvider $categoryProvider
    ) {
        $this->gqlClient = $gqlClient;
        $this->categoryQueryBuilder = $categoryQueryBuilder;
        $this->categoryPersistor = $categoryPersistor;
        $this->cursorManager = $cursorManager;
        $this->logger = $syncLogger;
        $this->configProvider = $configProvider;
        $this->languageProvider = $languageProvider;
        $this->categoryProvider = $categoryProvider;
    }

    /**
     * @param int $categoryCount Number of categories to process (categories per page)
     * @return bool Returns true if source has next page and false otherwise
     */
    public function processStream(
        Context $context,
        int $categoryCount = self::DEFAULT_CATEGORY_COUNT
    ): bool {
        $treeCode = $this->configProvider->getCategoryTreeCode();
        if (empty($treeCode)) {
            throw new \RuntimeException('Could not find category tree code in plugin config.');
        }

        $cursorEntity = $this->cursorManager->getCursorEntity(CategoryTreeStreamResultsProxy::MAIN_FIELD, $context);
        $cursor = null === $cursorEntity ? null : $cursorEntity->getCursor();

        $query = $this->categoryQueryBuilder->buildTreeStream($categoryCount, $cursor);
        /** @var CategoryTreeStreamResultsProxy|null $result */
        $result = $this->gqlClient->query($query, CategoryTreeStreamResultsProxy::class);

        if (null === $result) {
            throw new \RuntimeException('Request failed');
        }

        if (0 === \count($result->getEdges())) {
            throw new \RuntimeException('End of stream');
        }

        $endCursor = $result->getEndCursor();
        if (null === $endCursor) {
            throw new \RuntimeException('Could not retrieve end cursor from the response');
        }

        $activeLanguages = $this->languageProvider->getActiveLanguages($context);

        foreach ($result->getEdges() as $edge) {
            $node = $edge['node'] ?? null;

            if ($treeCode !== $node['code']) {
                continue;
            }

            $this->persistTreeRootStub($treeCode, $activeLanguages, $context);

            try {
                foreach ($activeLanguages as $language) {
                    foreach ($node['categoryTreeLeafList']['edges'] as $leafEdge) {
                        $leafNode = $leafEdge['node'];
                        $this->categoryPersistor->persistStub(
                            $leafNode['category']['code'],
                            $leafNode['parentCategory']['code'] ?? $treeCode,
                            IsoCodeConverter::shopwareToErgonodeIso($language->getLocale()->getCode()),
                            $context
                        );
                    }

                    $this->logger->info('Processed category', [
                        'code' => $node['code'],
                    ]);
                }

                $categoryCodes = \array_map(
                    fn($item) => $item['node']['category']['code'],
                    $node['categoryTreeLeafList']['edges']
                );
                $categoryCodes[] = $treeCode;

                $idsToRemove = $this->categoryProvider->getCategoryIdsNotInArray($categoryCodes, $context);
                $this->categoryPersistor->deleteIds(
                    $idsToRemove,
                    $context
                );
            } catch (\Throwable $e) {
                $this->logger->error('Error while persisting category', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'code' => $node['code'],
                ]);
            }
        }

        $this->cursorManager->persist($endCursor, CategoryTreeStreamResultsProxy::MAIN_FIELD, $context);

        return $result->hasNextPage();
    }

    private function persistTreeRootStub($treeCode, LanguageCollection $activeLanguages, Context $context)
    {
        foreach ($activeLanguages as $language) {
            $this->categoryPersistor->persistStub(
                $treeCode,
                null,
                IsoCodeConverter::shopwareToErgonodeIso($language->getLocale()->getCode()),
                $context
            );
        }
    }
}