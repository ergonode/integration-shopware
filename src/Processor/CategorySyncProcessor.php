<?php

declare(strict_types=1);

namespace Strix\Ergonode\Processor;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Strix\Ergonode\Api\Client\ErgonodeGqlClientInterface;
use Strix\Ergonode\Manager\ErgonodeCursorManager;
use Strix\Ergonode\Modules\Category\Api\CategoryStreamResultsProxy;
use Strix\Ergonode\Modules\Category\QueryBuilder\CategoryQueryBuilder;
use Strix\Ergonode\Persistor\CategoryPersistor;

class CategorySyncProcessor
{
    public const DEFAULT_CATEGORY_COUNT = 10;

    private ErgonodeGqlClientInterface $gqlClient;
    private CategoryQueryBuilder $categoryQueryBuilder;
    private CategoryPersistor $categoryPersistor;
    private ErgonodeCursorManager $cursorManager;
    private LoggerInterface $logger;

    public function __construct(
        ErgonodeGqlClientInterface $gqlClient,
        CategoryQueryBuilder $categoryQueryBuilder,
        CategoryPersistor $categoryPersistor,
        ErgonodeCursorManager $cursorManager,
        LoggerInterface $syncLoggerLogger
    ) {
        $this->gqlClient = $gqlClient;
        $this->categoryQueryBuilder = $categoryQueryBuilder;
        $this->categoryPersistor = $categoryPersistor;
        $this->cursorManager = $cursorManager;
        $this->logger = $syncLoggerLogger;
    }

    /**
     * @param int $categoryCount Number of categories to process (categories per page)
     * @return bool Returns true if source has next page and false otherwise
     */
    public function processStream(
        string $treeCode,
        Context $context,
        int $categoryCount = self::DEFAULT_CATEGORY_COUNT
    ): bool {
        $cursorEntity = $this->cursorManager->getCursorEntity(CategoryStreamResultsProxy::MAIN_FIELD, $context);
        $cursor = null === $cursorEntity ? null : $cursorEntity->getCursor();

        $query = $this->categoryQueryBuilder->build($treeCode, $categoryCount, $cursor);
        /** @var CategoryStreamResultsProxy|null $result */
        $result = $this->gqlClient->query($query, CategoryStreamResultsProxy::class);

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

        foreach ($result->getEdges() as $edge) {
            $node = $edge['node'] ?? null;
            try {
                $this->categoryPersistor->persist($node, $context);

                $this->logger->info('Processed category', [
                    'code' => $node['code'],
                ]);
            } catch (\Throwable $e) {
                $this->logger->error('Error while persisting category', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'code' => $node['code'] ?? null,
                ]);
            }
        }

        $this->cursorManager->persist($endCursor, CategoryStreamResultsProxy::MAIN_FIELD, $context);

        return $result->hasNextPage();
    }
}