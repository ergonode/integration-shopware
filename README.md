# Shopware Ergonode Integration

## Development

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