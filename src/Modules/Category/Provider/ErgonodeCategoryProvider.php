<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Category\Provider;

use RuntimeException;
use Strix\Ergonode\Api\Client\ErgonodeGqlClient;
use Strix\Ergonode\Modules\Category\QueryBuilder\CategoryQueryBuilder;
use Strix\Ergonode\Modules\Category\Struct\ErgonodeCategoryCollection;
use Strix\Ergonode\Modules\Category\Transformer\CategoryResponseTransformer;

class ErgonodeCategoryProvider
{
    private const CATEGORIES_PER_PAGE = 1000;

    private CategoryQueryBuilder $categoryQueryBuilder;

    private ErgonodeGqlClient $ergonodeGqlClient;

    private CategoryResponseTransformer $categoryResponseTransformer;

    public function __construct(
        CategoryQueryBuilder $categoryQueryBuilder,
        ErgonodeGqlClient $ergonodeGqlClient,
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
            $response = $this->ergonodeGqlClient->query($query);

            if (false === $response->isOk()) {
                continue;
            }

            $data = $response->getData();
            if (empty($data['categoryTree'])) {
                throw new RuntimeException(sprintf('Tree with code %1 does not exist in Ergonode.', $treeCode));
            }

            $categories = $categories->merge($this->categoryResponseTransformer->transformResponse($data['categoryTree']));
            $cursor = $data['categoryTree']['categoryTreeLeafList']['pageInfo']['endCursor'] ?? null;
        } while (null !== $cursor && ($data['categoryTree']['categoryTreeLeafList']['pageInfo']['hasNextPage'] ?? false));

        return $categories;
    }
}
