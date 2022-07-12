<?php

declare(strict_types=1);

namespace Strix\Ergonode\Controller\Admin;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Strix\Ergonode\Service\ScheduledTask\CategoryTreeSyncTask;
use Strix\Ergonode\Service\ScheduledTask\ProductSyncTask;
use Strix\Ergonode\Service\ScheduledTask\ProductVisibilitySyncTask;
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
     * @return JsonResponse
     */
    public function triggerSync(): JsonResponse
    {
        $this->messageBus->dispatch(new CategoryTreeSyncTask());
        // TODO once CategoryTreeSyncTask uses categoryTreeStream instead of pulling whole category tree, the following
        // line should be uncommented (see SWERG-54, SWERG-49)
        // $this->messageBus->dispatch(new CategorySyncTask());
        $this->messageBus->dispatch(new ProductSyncTask());
        $this->messageBus->dispatch(new ProductVisibilitySyncTask());

        return new JsonResponse([
            'success' => true,
        ]);
    }
}
