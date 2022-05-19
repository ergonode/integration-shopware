<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests;

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

$loader = require TEST_PROJECT_DIR . '/vendor/autoload.php';

$loader->addPsr4('Strix\\Ergonode\\', TEST_PROJECT_DIR . '/custom/plugins/StrixErgonode/src', true);
$loader->addPsr4('Strix\\Ergonode\\Tests\\', TEST_PROJECT_DIR . '/custom/plugins/StrixErgonode/tests', true);
$loader->register();
