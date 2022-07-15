<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Controller\Admin;

use Ergonode\IntegrationShopware\Api\Client\HttpGqlClientFactory;
use Ergonode\IntegrationShopware\Api\ErgonodeAccessData;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"administration"})
 */
class TestCredentialsController extends AbstractController
{
    private HttpGqlClientFactory $clientFactory;

    public function __construct(HttpGqlClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    /**
     * @Route(
     *     "/api/_action/strix/ergonode/test-credentials",
     *     name="api.admin.strix.ergonode.test-credentials",
     *     methods={"POST"},
     *     defaults={"_route_scope"={"administration"}}
     * )
     */
    public function testCredentials(RequestDataBag $dataBag): JsonResponse
    {
        $client = $this->clientFactory->create(
            new ErgonodeAccessData(
                (string)$dataBag->get('baseUrl'),
                (string)$dataBag->get('apiKey')
            )
        );

        $success = true;
        try {
            $client->runRawQuery('');
        } catch (\Throwable $e) {
            $success = false;
        }

        return new JsonResponse([
            'success' => $success,
        ]);
    }
}
