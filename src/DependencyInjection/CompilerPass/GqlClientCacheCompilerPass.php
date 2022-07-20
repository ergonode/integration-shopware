<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\DependencyInjection\CompilerPass;

use Ergonode\IntegrationShopware\Api\Client\CachedErgonodeGqlClient;
use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClient;
use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GqlClientCacheCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $useCache = $container->getParameter('ergonode_integration.use_gql_cache') ?? false;
        $container->setDefinition(
            ErgonodeGqlClientInterface::class,
            $container->getDefinition(
                $useCache ?
                    CachedErgonodeGqlClient::class :
                    ErgonodeGqlClient::class
            )
        );
    }
}