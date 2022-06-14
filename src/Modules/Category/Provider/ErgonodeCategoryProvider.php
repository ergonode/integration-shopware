<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Category\Provider;

use RuntimeException;
use Strix\Ergonode\Api\Client\ErgonodeGqlClientInterface;
use Strix\Ergonode\Modules\Category\Api\CategoryTreeResultsProxy;
use Strix\Ergonode\Modules\Category\QueryBuilder\CategoryQueryBuilder;
use Strix\Ergonode\Modules\Category\Struct\ErgonodeCategoryCollection;
use Strix\Ergonode\Modules\Category\Transformer\CategoryResponseTransformer;

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
            $query = $this->categoryQueryBuilder->build($treeCode, self::CATEGORIES_PER_PAGE, $cursor);
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
