<?php

declare(strict_types=1);

namespace Strix\Ergonode;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Strix\Ergonode\DependencyInjection\CompilerPass\GqlClientCacheCompilerPass;
use Strix\Ergonode\Lifecycle\DatabaseLifecycleManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

class StrixErgonode extends Plugin
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new GqlClientCacheCompilerPass());
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        DatabaseLifecycleManager::getInstance($this->container)->uninstall($uninstallContext);
    }
}