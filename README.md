# Shopware Ergonode Integration

## Development

### Dev notes

`ProductSyncProcessor` and `CategorySyncProcessor` both have a setting of products/categories fetched per page when
syncing using Ergonode streams (10). Scheduled task runners have a limit on how many pages per run are processed (25) to
prevent infinite loop when something goes wrong.

ProductSyncProcessor might not properly handle variants that were "detached" since last sync.

When uninstalling plugin and selecting "Remove all app data permanently", all plugin tables and mappings will be
removed. This means that all mappings will be lost and next synchronization will create duplicated entities.

### Testing

In Shopware root run:

`php /var/www/html/vendor/phpunit/phpunit/phpunit --configuration /var/www/html/custom/plugins/ErgonodeIntegrationShopware/phpunit.xml`

### GQL Request Cache

In order to cache Ergonode GQL API requests you need to change the parameter `ergonode_integration.use_gql_cache` in
`src/Resources/config/parameters.yml` to `true` and use `Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientInterface` in your
classes instead of concrete `Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClient` class. Cached client class is
`Ergonode\IntegrationShopware\Api\Client\CachedErgonodeGqlClient`. More cache config options can be found in
`src/Resources/config/packages/cache.yml`.

In order to clear request cache run `bin/console cache:pool:clear gql_request_cache`.

### Updating composer deps

Current composer deps list:
 - gmostafa/php-graphql-client

Execute all commands in plugin root dir:
1. Execute `composer update` or `composer install`
2. Remove all dependencies, except those listed above and composer directory (adjust find command if needed)
   - Execute `find vendor -mindepth 1 -maxdepth 1 -type d -not -name 'composer' -and -not -name 'gmostafa' -print -exec rm -rf {} \;` 
   - Execute `find vendor/composer -mindepth 1 -maxdepth 1 -type d -print -exec rm -rf {} \;`
4. Execute `composer dumpautoload`
5. Remove composer.lock - `rm composer.lock`