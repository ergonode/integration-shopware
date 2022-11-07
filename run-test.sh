#!/usr/bin/env bash
set -e

cd /var/www/html || exit 1
composer install -d custom/plugins/ErgonodeIntegrationShopware -q

echo "### PHPMD"
custom/plugins/ErgonodeIntegrationShopware/vendor/phpmd/phpmd/src/bin/phpmd custom/plugins/ErgonodeIntegrationShopware/src ansi custom/plugins/ErgonodeIntegrationShopware/phpmd.xml

echo "### PHPSTAN"
custom/plugins/ErgonodeIntegrationShopware/vendor/bin/phpstan analyse -c custom/plugins/ErgonodeIntegrationShopware/phpstan.neon --error-format=raw

echo "### PHPUNIT"
vendor/phpunit/phpunit/phpunit --configuration custom/plugins/ErgonodeIntegrationShopware/phpunit.xml
