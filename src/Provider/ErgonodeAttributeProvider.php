<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Ergonode\IntegrationShopware\Api\AbstractStreamResultsProxy;
use Ergonode\IntegrationShopware\Api\AttributeDeletedStreamResultsProxy;
use Ergonode\IntegrationShopware\Api\AttributeResultsProxy;
use Ergonode\IntegrationShopware\Api\AttributeStreamResultsProxy;
use Ergonode\IntegrationShopware\Api\CategoryAttributeListResultsProxy;
use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientInterface;
use Ergonode\IntegrationShopware\QueryBuilder\AttributeQueryBuilder;
use Ergonode\IntegrationShopware\QueryBuilder\CategoryAttributeQueryBuilder;
use Generator;

class ErgonodeAttributeProvider
{
    private const MAX_ATTRIBUTES_PER_PAGE = 200;

    private AttributeQueryBuilder $attributeQueryBuilder;

    private CategoryAttributeQueryBuilder $categoryAttributeQueryBuilder;

    private ErgonodeGqlClientInterface $ergonodeGqlClient;

    public function __construct(
        AttributeQueryBuilder $attributeQueryBuilder,
        CategoryAttributeQueryBuilder $categoryAttributeQueryBuilder,
        ErgonodeGqlClientInterface $ergonodeGqlClient
    ) {
        $this->attributeQueryBuilder = $attributeQueryBuilder;
        $this->categoryAttributeQueryBuilder = $categoryAttributeQueryBuilder;
        $this->ergonodeGqlClient = $ergonodeGqlClient;
    }

    public function provideProductAttributes(?string $endCursor = null): Generator
    {
        do {
            $query = $this->attributeQueryBuilder->build(self::MAX_ATTRIBUTES_PER_PAGE, $endCursor);
            $results = $this->ergonodeGqlClient->query($query, AttributeStreamResultsProxy::class);

            if (!$results instanceof AttributeStreamResultsProxy) {
                continue;
            }

            $results = $this->addOptionListPages($results);

            yield $results;

            $endCursor = $results->getEndCursor();
        } while ($results instanceof AbstractStreamResultsProxy && $results->hasNextPage());
    }

    public function provideDeletedAttributes(?string $endCursor = null): Generator
    {
        do {
            $query = $this->attributeQueryBuilder->buildDeleted(self::MAX_ATTRIBUTES_PER_PAGE, $endCursor);
            $results = $this->ergonodeGqlClient->query($query, AttributeDeletedStreamResultsProxy::class);

            if (!$results instanceof AttributeDeletedStreamResultsProxy) {
                continue;
            }

            yield $results;

            $endCursor = $results->getEndCursor();
        } while ($results instanceof AbstractStreamResultsProxy && $results->hasNextPage());
    }

    private function addOptionListPages(AttributeStreamResultsProxy $results): AttributeStreamResultsProxy
    {
        foreach ($results->getEdges() as $edge) {
            if (isset(
                    $edge['node']['optionList'],
                    $edge['node']['optionList']['pageInfo']['endCursor'], $edge['node']['optionList']['pageInfo']['hasNextPage']
                )
                && $edge['node']['optionList']['pageInfo']['hasNextPage']) {

                $endCursor = $edge['node']['optionList']['pageInfo']['endCursor'];
                do {
                    $query = $this->attributeQueryBuilder->buildSingle(
                        $edge['node']['code'],
                        $endCursor
                    );
                    $attributeResults = $this->ergonodeGqlClient->query($query, AttributeResultsProxy::class);
                    $mainData = $attributeResults->getMainData();

                    $results->addOptions($edge['node']['code'], $mainData['optionList']['edges']);
                    $hasNextPage = false;
                    if (isset(
                        $mainData['optionList']['pageInfo']['hasNextPage'],
                        $mainData['optionList']['pageInfo']['endCursor'])
                    ) {
                        $hasNextPage = $mainData['optionList']['pageInfo']['hasNextPage'];
                        $endCursor = $mainData['optionList']['pageInfo']['endCursor'];
                    }
                } while ($hasNextPage);
            }
        }

        return $results;
    }

    public function provideProductCategoryAttributes(?string $endCursor = null): Generator
    {
        do {
            $query = $this->categoryAttributeQueryBuilder->build(self::MAX_ATTRIBUTES_PER_PAGE, $endCursor);
            $results = $this->ergonodeGqlClient->query($query, CategoryAttributeListResultsProxy::class);

            if (!$results instanceof CategoryAttributeListResultsProxy) {
                continue;
            }

            yield $results;

            $endCursor = $results->getEndCursor();
        } while ($results instanceof CategoryAttributeListResultsProxy && $results->hasNextPage());
    }
}
