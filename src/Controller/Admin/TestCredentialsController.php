<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Controller\Admin;

use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientFactory;
use Ergonode\IntegrationShopware\Api\ErgonodeAccessData;
use Ergonode\IntegrationShopware\QueryBuilder\LanguageQueryBuilder;
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
    private ErgonodeGqlClientFactory $clientFactory;

    private LanguageQueryBuilder $queryBuilder;

    public function __construct(
        ErgonodeGqlClientFactory $clientFactory,
        LanguageQueryBuilder $languageQueryBuilder
    ) {
        $this->clientFactory = $clientFactory;
        $this->queryBuilder = $languageQueryBuilder;
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

        try {
            $query = $this->queryBuilder->buildActiveLanguages(1); // some random query
            $result = $client->query($query);

            $success = 200 === $result->getResponseObject()->getStatusCode();
        } catch (Throwable $e) {
            $success = false;
        }

        return new JsonResponse([
            'success' => $success,
        ]);
    }
}
