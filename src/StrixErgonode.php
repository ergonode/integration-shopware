<?php

declare(strict_types=1);

namespace Strix\Ergonode;

use Shopware\Core\Framework\Plugin;

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

class StrixErgonode extends Plugin
{
}