<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Controller\Admin;

use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class SyncHistoryController extends AbstractController
{
    private SyncHistoryLogger $syncHistoryService;

    public function __construct(SyncHistoryLogger $syncHistoryService)
    {
        $this->syncHistoryService = $syncHistoryService;
    }

    /**
     *
     * @param string $id
     * @return JsonResponse
     */
    #[Route(path: '/api/ergonode/sync-history-log/{id}', name: 'api.ergonode.syncHistoryLog', methods: ['GET'])]
    public function syncHistoryLog(string $id): JsonResponse
    {
        if (empty($id)) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $logContent = $this->syncHistoryService->getLogs(strtolower($id));

        return new JsonResponse($logContent);
    }
}
