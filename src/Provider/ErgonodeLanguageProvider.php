<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientInterface;
use Ergonode\IntegrationShopware\Api\LanguageListStreamResultsProxy;
use Ergonode\IntegrationShopware\QueryBuilder\LanguageQueryBuilder;
use Generator;

class ErgonodeLanguageProvider
{
    private const MAX_LANGUAGES_PER_PAGE = 1000;

    private LanguageQueryBuilder $languageQueryBuilder;

    private ErgonodeGqlClientInterface $ergonodeGqlClient;

    public function __construct(
        LanguageQueryBuilder $languageQueryBuilder,
        ErgonodeGqlClientInterface $ergonodeGqlClient
    ) {
        $this->languageQueryBuilder = $languageQueryBuilder;
        $this->ergonodeGqlClient = $ergonodeGqlClient;
    }

    public function provideActiveLanguages(?string $endCursor = null): Generator
    {
        do {
            $query = $this->languageQueryBuilder->buildActiveLanguages(self::MAX_LANGUAGES_PER_PAGE, $endCursor);
            $results = $this->ergonodeGqlClient->query($query, LanguageListStreamResultsProxy::class);

            if (!$results instanceof LanguageListStreamResultsProxy) {
                continue;
            }

            yield $results;

            $endCursor = $results->getEndCursor();
        } while ($results->hasNextPage());
    }
}