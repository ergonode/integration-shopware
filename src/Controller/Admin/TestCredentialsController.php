<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Controller\Admin;

use Ergonode\IntegrationShopware\Api\Client\HttpGqlClientFactory;
use Ergonode\IntegrationShopware\Api\ErgonodeAccessData;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * @Route(defaults={"_routeScope"={"api"}})
 */
class TestCredentialsController extends AbstractController
{
    private HttpGqlClientFactory $clientFactory;

    private LoggerInterface $apiLogger;

    public function __construct(
        HttpGqlClientFactory $clientFactory,
        LoggerInterface $ergonodeApiLogger
    ) {
        $this->clientFactory = $clientFactory;
        $this->apiLogger = $ergonodeApiLogger;
    }

    /**
     * @Route(
     *     "/api/_action/ergonode/test-credentials",
     *     name="api.admin.ergonode.test-credentials",
     *     methods={"POST"}
     * )
     */
    public function testCredentials(RequestDataBag $dataBag): JsonResponse
    {
        $client = $this->clientFactory->create(
            new ErgonodeAccessData(
                (string)$dataBag->get('apiEndpoint'),
                (string)$dataBag->get('apiKey')
            )
        );

        $success = true;
        try {
            $client->runRawQuery('');
        } catch (Throwable $e) {
            $this->apiLogger->error('Error while testing credentials.', [
                'message' => $e->getMessage(),
            ]);
            $success = false;
        }

        return new JsonResponse([
            'success' => $success,
        ]);
    }
}
