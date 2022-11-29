<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests;

function getProjectDir(): string
{
    if (isset($_SERVER['PROJECT_ROOT']) && file_exists($_SERVER['PROJECT_ROOT'])) {
        return $_SERVER['PROJECT_ROOT'];
    }
    if (isset($_ENV['PROJECT_ROOT']) && file_exists($_ENV['PROJECT_ROOT'])) {
        return $_ENV['PROJECT_ROOT'];
    }

    if (file_exists('vendor')) {
        return (string)getcwd();
    }

    $dir = $rootDir = __DIR__;
    while (!file_exists($dir . '/vendor')) {
        if ($dir === dirname($dir)) {
            return $rootDir;
        }
        $dir = dirname($dir);
    }

    return $dir;
}

define('TEST_PROJECT_DIR', getProjectDir());

$loader = require_once TEST_PROJECT_DIR . '/vendor/autoload.php'; // load shopware deps
$loader = require_once TEST_PROJECT_DIR . '/custom/plugins/ErgonodeIntegrationShopware/vendor/autoload.php'; // load plugin deps

$loader->addPsr4('Ergonode\\IntegrationShopware\\', TEST_PROJECT_DIR . '/custom/plugins/ErgonodeIntegrationShopware/src', true);
$loader->addPsr4('Ergonode\\IntegrationShopware\\Tests\\', TEST_PROJECT_DIR . '/custom/plugins/ErgonodeIntegrationShopware/tests', true);
$loader->register();
