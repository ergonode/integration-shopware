#!/usr/bin/env bash

cd /var/www/html/custom/plugins/ErgonodeIntergrationShopware
composer install
./vendor/phpmd/phpmd/src/bin/phpmd src text phpmd.xml
./vendor/bin/phpstan
php /var/www/html/vendor/phpunit/phpunit/phpunit --configuration /var/www/html/custom/plugins/ErgonodeIntergrationShopware/phpunit.xml
