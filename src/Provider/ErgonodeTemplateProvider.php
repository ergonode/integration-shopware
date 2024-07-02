<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientInterface;
use Ergonode\IntegrationShopware\Api\TemplateListResultsProxy;
use Ergonode\IntegrationShopware\QueryBuilder\TemplateQueryBuilder;
use Generator;

class ErgonodeTemplateProvider
{
    private const int MAX_TEMPLATES_PER_PAGE = 100;

    public function __construct(
        private readonly TemplateQueryBuilder $templateQueryBuilder,
        private readonly ErgonodeGqlClientInterface $ergonodeGqlClient
    ) {
    }

    /**
     * @return Generator<TemplateListResultsProxy>
     */
    public function provideTemplates(?string $endCursor = null): Generator
    {
        do {
            $query = $this->templateQueryBuilder->build(self::MAX_TEMPLATES_PER_PAGE, $endCursor);
            $results = $this->ergonodeGqlClient->query($query, TemplateListResultsProxy::class);

            if (false === $results instanceof TemplateListResultsProxy) {
                return null;
            }

            yield $results;
        } while ($results->hasNextPage());
    }
}
