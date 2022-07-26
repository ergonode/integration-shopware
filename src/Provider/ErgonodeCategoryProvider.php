<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Ergonode\IntegrationShopware\Api\CategoryTreeResultsProxy;
use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientInterface;
use Ergonode\IntegrationShopware\QueryBuilder\CategoryQueryBuilder;
use Ergonode\IntegrationShopware\Struct\ErgonodeCategoryCollection;
use Ergonode\IntegrationShopware\Transformer\CategoryResponseTransformer;
use RuntimeException;

class ErgonodeCategoryProvider
{
    private const CATEGORIES_PER_PAGE = 1000;

    private CategoryQueryBuilder $categoryQueryBuilder;

    private ErgonodeGqlClientInterface $ergonodeGqlClient;

    private CategoryResponseTransformer $categoryResponseTransformer;

    public function __construct(
        CategoryQueryBuilder $categoryQueryBuilder,
        ErgonodeGqlClientInterface $ergonodeGqlClient,
        CategoryResponseTransformer $categoryResponseTransformer
    ) {
        $this->categoryQueryBuilder = $categoryQueryBuilder;
        $this->ergonodeGqlClient = $ergonodeGqlClient;
        $this->categoryResponseTransformer = $categoryResponseTransformer;
    }

    /**
     * @throws RuntimeException
     */
    public function provideCategoryTree(string $treeCode): ErgonodeCategoryCollection
    {
        $cursor = null;
        $categories = new ErgonodeCategoryCollection();

        do {
            $query = $this->categoryQueryBuilder->buildTree($treeCode, self::CATEGORIES_PER_PAGE, $cursor);
            $results = $this->ergonodeGqlClient->query($query, CategoryTreeResultsProxy::class);

            if (!$results instanceof CategoryTreeResultsProxy) {
                continue;
            }

            if ($results->isMainDataEmpty()) {
                throw new RuntimeException(sprintf('Tree with code %1 does not exist in Ergonode.', $treeCode));
            }

            $categories = $categories->merge($this->categoryResponseTransformer->transformResponse($results->getMainData()));
            $cursor = $results->getEndCursor();
        } while (null !== $cursor && $results->hasNextPage());

        return $categories;
    }
}
