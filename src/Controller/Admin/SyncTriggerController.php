<?php

declare(strict_types=1);

namespace Strix\Ergonode\Controller\Admin;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Strix\Ergonode\Service\ScheduledTask\CategorySyncTask;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"administration"})
 */
class SyncTriggerController extends AbstractController
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * @Route(
     *     "/api/_action/strix/ergonode/trigger-sync",
     *     name="api.admin.strix.ergonode.trigger-sync",
     *     methods={"POST"},
     *     defaults={"_route_scope"={"administration"}}
     *     )
     * @param RequestDataBag $dataBag
     * @return JsonResponse
     */
    public function triggerSync(RequestDataBag $dataBag): JsonResponse
    {
        $this->messageBus->dispatch(new CategorySyncTask());
        //$this->messageBus->dispatch(new ProductSyncTask());
        return new JsonResponse([
            'success' => true,
        ]);
    }
}
