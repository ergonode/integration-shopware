<?php

declare(strict_types=1);

namespace Strix\Ergonode\DependencyInjection\CompilerPass;

use Strix\Ergonode\Api\Client\CachedErgonodeGqlClient;
use Strix\Ergonode\Api\Client\ErgonodeGqlClient;
use Strix\Ergonode\Api\Client\ErgonodeGqlClientInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GqlClientCacheCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $useCache = $container->getParameter('strix.ergonode.use_gql_cache') ?? false;
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