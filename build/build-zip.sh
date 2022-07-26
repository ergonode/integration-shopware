#!/usr/bin/env bash
set -e

# config
PLUGIN_NAME="ErgonodeIntegrationShopware"
TAG="${TAG:-master}"

SCRIPT_PATH=$(dirname "$(realpath -s "${BASH_SOURCE[0]}")")
BUILD_PATH="$SCRIPT_PATH/$PLUGIN_NAME"
COMPOSER_DIST_PATH="$SCRIPT_PATH/composer-dist"
PLUGIN_PATH=$(dirname "$SCRIPT_PATH")
SHOPWARE_PATH=$(realpath "$PLUGIN_PATH/../../../")

ZIP_PATH="$SCRIPT_PATH/$PLUGIN_NAME-$TAG.zip"

DIRS_EXCLUDED_FROM_ZIP=("build" ".git" ".gitignore" "*.css.map" "*.js.map")

# actual script
echo -e "1/6 \e[33mRemoving old release(s)...\e[39m"
rm -rf "$BUILD_PATH" "$BUILD_PATH"-*.zip
rm -rf "$COMPOSER_DIST_PATH/vendor"
rm -rf "$PLUGIN_PATH/vendor"
echo -e "\e[32mOK\e[39m\n"

echo -e "2/6 \e[33mInstalling plugin dependencies...\e[39m"
composer install -d "$COMPOSER_DIST_PATH"
cp -R "$COMPOSER_DIST_PATH/vendor" "$PLUGIN_PATH/vendor"
echo -e "\e[32mOK\e[39m\n"

echo -e "3/6 \e[33mBuilding storefront and administration...\e[39m"
bash "$SHOPWARE_PATH/bin/build-storefront.sh"
bash "$SHOPWARE_PATH/bin/build-administration.sh"
echo -e "\e[32mOK\e[39m\n"

echo -e "4/6 \e[33mCopying files...\e[39m"
rsync -av --progress "$PLUGIN_PATH" "$SCRIPT_PATH" "${DIRS_EXCLUDED_FROM_ZIP[@]/#/--exclude=}"
echo -e "\e[32mOK\e[39m\n"

echo -e "5/6 \e[33mZipping...\e[39m"
cd "$SCRIPT_PATH" # prevent zipping whole file structure from current dir
zip -rq "$ZIP_PATH" "$PLUGIN_NAME"
cd - # keep original working dir
echo -e "\e[32mOK\e[39m\n"

echo -e "6/6 \e[33mCleaning build directory...\e[39m"
rm -rf "$BUILD_PATH"
rm -rf "$COMPOSER_DIST_PATH/vendor"
echo -e "\e[32mOK\e[39m\n"

echo -e "\e[32mZip file build completed! :)\e[39m"
echo -e "You will find it under \e[33m$ZIP_PATH\e[39m\n"
