#!/usr/bin/env bash

cd /var/www/html || exit 1
composer install -d /var/www/html/custom/plugins/ErgonodeIntegrationShopware
/var/www/html/custom/plugins/ErgonodeIntegrationShopware/vendor/phpmd/phpmd/src/bin/phpmd src text /var/www/html/custom/plugins/ErgonodeIntegrationShopware/phpmd.xml
/var/www/html/custom/plugins/ErgonodeIntegrationShopware/vendor/bin/phpstan -c /var/www/html/custom/plugins/ErgonodeIntegrationShopware/phpstan.neon
php /var/www/html/vendor/phpunit/phpunit/phpunit --configuration /var/www/html/custom/plugins/ErgonodeIntegrationShopware/phpunit.xml
