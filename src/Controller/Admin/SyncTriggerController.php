<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Controller\Admin;

use Ergonode\IntegrationShopware\Service\ScheduledTask\CategorySyncTask;
use Ergonode\IntegrationShopware\Service\ScheduledTask\CategoryTreeSyncTask;
use Ergonode\IntegrationShopware\Service\ScheduledTask\ProductSyncTask;
use Ergonode\IntegrationShopware\Service\ScheduledTask\ProductVisibilitySyncTask;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
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
     *     "/api/_action/ergonode/trigger-sync",
     *     name="api.admin.ergonode.trigger-sync",
     *     methods={"POST"},
     *     defaults={"_route_scope"={"administration"}}
     * )
     * @return JsonResponse
     */
    public function triggerSync(): JsonResponse
    {
        $this->messageBus->dispatch(new CategoryTreeSyncTask());
        $this->messageBus->dispatch(new CategorySyncTask());
        $this->messageBus->dispatch(new ProductSyncTask());
        $this->messageBus->dispatch(new ProductVisibilitySyncTask());

        return new JsonResponse([
            'success' => true,
        ]);
    }
}
