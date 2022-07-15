<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware;

use Ergonode\IntegrationShopware\DependencyInjection\CompilerPass\GqlClientCacheCompilerPass;
use Ergonode\IntegrationShopware\Lifecycle\DatabaseLifecycleManager;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

class ErgonodeIntegrationShopware extends Plugin
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