<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Controller\Admin;

use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientFactory;
use Ergonode\IntegrationShopware\Api\ErgonodeAccessData;
use Ergonode\IntegrationShopware\QueryBuilder\LanguageQueryBuilder;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

#[Route(defaults: ['_routeScope' => ['api']])]
class TestCredentialsController extends AbstractController
{
    private ErgonodeGqlClientFactory $clientFactory;

    private LanguageQueryBuilder $queryBuilder;

    private LoggerInterface $ergonodeApiLogger;

    public function __construct(
        ErgonodeGqlClientFactory $clientFactory,
        LanguageQueryBuilder $languageQueryBuilder,
        LoggerInterface $ergonodeApiLogger
    ) {
        $this->clientFactory = $clientFactory;
        $this->queryBuilder = $languageQueryBuilder;
        $this->ergonodeApiLogger = $ergonodeApiLogger;
    }

    #[Route(path: '/api/_action/ergonode/test-credentials', name: 'api.admin.ergonode.test-credentials', methods: ['POST'])]
    public function testCredentials(RequestDataBag $dataBag): JsonResponse
    {
        $client = $this->clientFactory->create(
            new ErgonodeAccessData(
                (string)$dataBag->get('apiEndpoint'),
                (string)$dataBag->get('apiKey')
            )
        );

        $success = false;

        try {
            $query = $this->queryBuilder->buildActiveLanguages(1); // some random query
            $result = $client->query($query);

            if (null !== $result) {
                $success = 200 === $result->getResponseObject()->getStatusCode();
            }
        } catch (Throwable $e) {
            $this->ergonodeApiLogger->error('Invalid API credentials');
        }

        return new JsonResponse([
            'success' => $success,
        ]);
    }
}
