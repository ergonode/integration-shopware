# Shopware Ergonode Integration

## Description

This plugin synchronizes data from Ergonode to Shopware. It takes advantage of Ergonode's GraphQL API and utilizes
Ergonode's streams.

## Key features

### Category sync

This plugin synchronizes Ergonode's categories and single category tree into Shopware. First, it looks into
categoryTreeStream to see if the main tree has changed since last sync. If it did, the plugin then iterates through
all category leaves using the cursor. Categories not found in Shopware are created with code as its name. Translations
are not persisted at this point. Categories already existing in Shopware have their parents updated.

After it's done, or when no changes were detected, the categoryStream is fetched and category translations are
persisted.

> Missing features:
> - Categories removed from the tree in Ergonode are not removed from Shopware
> - Category order within the tree is not synchronized into Shopware

### Product sync

Products are synchronized using productStream. Main fields (fields that are found directly in Shopware's ProductEntity)
can be configured in Settings > Ergonode integration > Attribute mappings. One Shopware field can be mapped to one
Ergonode field and one Ergonode field can be mapped to many Shopware fields. These mappings are not required; the plugin
will use Ergonode's code as product name if no such mapping is provided.

Ergonode attributes of type `select` and `multiselect` will be added as properties in Shopware after attribute sync
(see below). Attributes of other type can be added as custom fields by selecting them in plugin's config page under
"Ergonode attributes as custom fields".

Deleted products are also deleted in Shopware using productDeletedStream.

### Attribute sync

In order for attributes of type `select` or `multiselect` to appear in Shopware as properties, first the attribute sync
must be executed.

### Product cross-selling

Product cross-selling can be set up by creating product relations in Ergonode, then in plugin config selecting those
fields under "Ergonode attributes as cross selling".

### Product visibility

Product visibility can be set up using Ergonode's segments functionality. In Shopware plugin configuration segments'
API keys can be set per sales channel and product visibility per sales channel will be updated accordingly.

### Languages

Ergonode languages are synchronized into Shopware.

### Executing sync and scheduling

The synchronization process is added as a bunch of scheduled tasks which are run periodically. The process can be
triggered manually in Settings > Ergonode integration > Synchronization. Tasks use a lock system so that only one task
of given type is ran at once. These tasks utilize Ergonode's cursors where applicable, meaning that they only process
changes that occurred since last sync.

### Sync history

Synchronization history can be viewed under Settings > Ergonode integration > Import history.

## Configuration

The minimal configuration required involves setting up plugin's configuration in Shopware. The required settings are:

- Ergonode GraphQL API endpoint (global setting)
- Ergonode API key (can be set up per sales channel)
- Code of the category tree to synchronize

## Development

### Dev notes

ProductSyncProcessor might not properly handle variants that were "detached" since last sync.

When uninstalling plugin and selecting "Remove all app data permanently", all plugin tables and mappings will be
removed. This means that all mappings will be lost and next synchronization will create duplicated entities.

### Testing

In plugin root you can use the make commando and then the test you want to perform:

`make phpstan`
`make phpunit`
`make phpmd`

### Building ZIP

To build Store package execute:

`make release`

NOTE: The make file is used from the plugin root.

### Cache

In order to cache Ergonode GQL API requests you need to change the parameter `ergonode_integration.use_gql_cache`
in `src/Resources/config/parameters.yml` to `true` and
use `Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientInterface` in your
classes instead of concrete `Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClient` class. Cached client class is
`Ergonode\IntegrationShopware\Api\Client\CachedErgonodeGqlClient`.

More cache config options can be found in
`src/Resources/config/packages/cache.yml`.

In order to clear cache pool run `bin/console cache:pool:clear ergonode_gql_request_cache`.

Available cache pools:
- ergonode_gql_request_cache
- ergonode_attribute_mapping_cache

## Plugin version compatibility
| Shopware         | Plugin      |
|------------------|-------------|
| 6.6 from 6.6.0.0 | Version 3.x |
| 6.5 from 6.5.0.0 | Version 2.x  |
| 6.4 from 6.4.0.0 | Version 1.x  |