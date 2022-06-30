# Shopware Ergonode Integration

## Development

### Dev notes

`ProductSyncProcessor` and `CategorySyncProcessor` both have a setting of products/categories fetched per page when
syncing using Ergonode streams (10). Scheduled task runners have a limit on how many pages per run are processed (25) to
prevent infinite loop when something goes wrong.

ProductSyncProcessor might not properly handle variants that were "detached" since last sync.

### Testing

In Shopware root run:

`php /var/www/html/vendor/phpunit/phpunit/phpunit --configuration /var/www/html/custom/plugins/StrixErgonode/phpunit.xml`

### GQL Request Cache (needs fixing)

> Currently the cache does not work with GraphQL\Results proxies

In order to cache Ergonode GQL API requests you need to change the parameter `strix.ergonode.use_gql_cache` in
`src/Resources/config/parameters.yml` to `true` and use `Strix\Ergonode\Api\Client\ErgonodeGqlClientInterface` in your
classes instead of concrete `Strix\Ergonode\Api\Client\ErgonodeGqlClient` class. Cached client class is
`Strix\Ergonode\Api\Client\CachedErgonodeGqlClient`. More cache config options can be found in
`src/Resources/config/packages/cache.yml`.

In order to clear request cache run `bin/console cache:pool:clear gql_request_cache`.