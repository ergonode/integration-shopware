<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

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
        } while (null !== $cursor && $results->hasNextPage());
    }
}
