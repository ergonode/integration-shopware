<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Ergonode\IntegrationShopware\Api\AbstractResultsProxy;
use Ergonode\IntegrationShopware\Api\AbstractStreamResultsProxy;
use Ergonode\IntegrationShopware\Api\CategoryTreeResultsProxy;
use Ergonode\IntegrationShopware\Api\CategoryTreeStreamResultsProxy;
use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientInterface;
use Ergonode\IntegrationShopware\QueryBuilder\CategoryQueryBuilder;
use Generator;
use RuntimeException;

class ErgonodeCategoryProvider
{
    private const TREES_PER_PAGE = 200;

    private CategoryQueryBuilder $categoryQueryBuilder;

    private ErgonodeGqlClientInterface $ergonodeGqlClient;

    public function __construct(
        CategoryQueryBuilder $categoryQueryBuilder,
        ErgonodeGqlClientInterface $ergonodeGqlClient
    ) {
        $this->categoryQueryBuilder = $categoryQueryBuilder;
        $this->ergonodeGqlClient = $ergonodeGqlClient;
    }

    public function provideCategoryTreeCodes(): Generator
    {
        $cursor = null;

        do {
            $query = $this->categoryQueryBuilder->buildTreeStreamWithOnlyCodes(self::TREES_PER_PAGE, $cursor);
            $results = $this->ergonodeGqlClient->query($query, CategoryTreeStreamResultsProxy::class);

            if (!$results instanceof CategoryTreeStreamResultsProxy) {
                continue;
            }

            if ($results->isMainDataEmpty()) {
                throw new RuntimeException('Could not fetch category trees from Ergonode (empty response).');
            }

            yield $results;

            $cursor = $results->getEndCursor();
        } while (null !== $cursor && $results instanceof AbstractStreamResultsProxy && $results->hasNextPage());
    }

    public function provideCategories(array $treeCodes): array
    {
        $results = [];
        foreach ($treeCodes as $treeCode) {
            $results[] = $this->provideCategoriesByTreeCode($treeCode);
        }

        return $results;
    }

    private function provideCategoriesByTreeCode(string $treeCode): array
    {
        $cursor = null;
        $categories = [];
        do {
            $query = $this->categoryQueryBuilder->buildTree($treeCode, 2, $cursor);
            $results = $this->ergonodeGqlClient->query($query, CategoryTreeResultsProxy::class);

            if (!$results instanceof CategoryTreeResultsProxy) {
                continue;
            }

            if ($results->isMainDataEmpty()) {
                throw new RuntimeException('Could not fetch category tree from Ergonode (empty response).');
            }

            $categories = array_merge($categories, $results->getEdges());

            $cursor = $results->getEndCursor();
        } while (null !== $cursor && $results instanceof AbstractResultsProxy && $results->hasNextPage());

        return $categories;
    }
}
