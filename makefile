#
# Makefile
#

.PHONY: help
.DEFAULT_GOAL := help

PLUGIN_NAME=ErgonodeIntegrationShopware
PLUGIN_VERSION=`php -r 'echo json_decode(file_get_contents("$(PLUGIN_NAME)/composer.json"))->version;'`

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

# ------------------------------------------------------------------------------------------------------------

install: ## Installs all production dependencies
	@composer2 install --no-dev
	@{ [ -d src/Resources/app/administration ] && cd src/Resources/app/administration || exit 0; } && { [ -f package.json ] && npm install --production || exit 0; }
	@{ [ -d src/Resources/app/storefront ] && cd src/Resources/app/storefront || exit 0; } && { [ -f package.json ] && npm install --production || exit 0; }

dev: ## Installs all dev dependencies
	@composer2 install
	@{ [ -d src/Resources/app/administration ] && cd src/Resources/app/administration || exit 0; } && { [ -f package.json ] && npm install || exit 0; }
	@{ [ -d src/Resources/app/storefront ] && cd src/Resources/app/storefront || exit 0; } && { [ -f package.json ] && npm install || exit 0; }

clean: ## Cleans all dependencies
	rm -rf vendor
	rm -rf .reports | true
	@make clean-node

clean-node: ## Removes node_modules
	rm -rf src/Resources/app/administration/node_modules
	rm -rf src/Resources/app/storefront/node_modules

# ------------------------------------------------------------------------------------------------------------

insights: ## Starts the PHPInsights Analyser
	@php vendor/bin/phpinsights analyse --no-interaction

csfix: ## Starts the PHP CS Fixer
	@php vendor/bin/php-cs-fixer fix --config=./.php_cs.php --dry-run

phpcheck: ## Starts the PHP syntax checks
	@find . -name '*.php' -not -path "./vendor/*" -not -path "./tests/*" | xargs -n 1 -P4 php -l

phpmin: ## Starts the PHP compatibility checks
	@php vendor/bin/phpcs -p --standard=PHPCompatibility --extensions=php --runtime-set testVersion 8.2 ./src

phpstan: ## Starts the PHPStan Analyser
	@php vendor/bin/phpstan analyse -c ./.phpstan.neon

phpunit: ## Starts all Tests
	@phpdbg -qrr vendor/bin/phpunit --configuration=phpunit.xml --coverage-html ./.reports/$(PLUGIN_NAME)/coverage

infection: ## Starts all Infection/Mutation tests
	@phpdbg -qrr vendor/bin/infection --configuration=./.infection.json

phpmd: ## Starts all Tests
	@php vendor/bin/phpmd ./src ansi ./phpmd.xml

# ------------------------------------------------------------------------------------------------------------

pr: ## Prepares everything for a Pull Request
	@make dev
	@make csfix
	@make phpcheck -B
	@make phpmin -B
	@make phpstan -B

build: ## Builds the package
	@rm -rf src/Resources/app/storefront/dist
	@cd ../../.. && php bin/console plugin:refresh
	@cd ../../.. && php bin/console plugin:install $(PLUGIN_NAME) --activate --clearCache | true
	@cd ../../.. && php bin/console plugin:refresh
	@cd ../../.. && php bin/console theme:dump
	@cd ../../.. && php bin/console theme:refresh
	@cd ../../.. && PUPPETEER_SKIP_DOWNLOAD=1 ./bin/build-js.sh
	@cd ../../.. && php bin/console theme:refresh

release: ## Create a new release
	@make clean
	@make install
	@make build
	@make zip

zip: ## Creates a new ZIP package
	@php update-composer-require.php --shopware=~6.5.0 --env=prod --admin --storefront
	@cd .. && echo "Creating Zip file $(PLUGIN_NAME)-$(PLUGIN_VERSION).zip\n"
	@cd .. && rm -rf $(PLUGIN_NAME)-$(PLUGIN_VERSION).zip
	@cd .. && zip -qq -r -0 $(PLUGIN_NAME)-$(PLUGIN_VERSION).zip $(PLUGIN_NAME)/ -x '*.editorconfig' '*.git*' '*.reports*' '*/tests*' '*/makefile' '*.DS_Store' '*/phpunit.xml' '*/.phpstan.neon' '*/.php_cs.php' '*/phpinsights.php' '*node_modules*' '*administration/build*' '*storefront/build*' '*/update-composer-require.php'
	@php update-composer-require.php --shopware=~6.5.0 --env=dev --admin --storefront
