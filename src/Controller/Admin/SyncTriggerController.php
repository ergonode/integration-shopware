<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Controller\Admin;

use Ergonode\IntegrationShopware\Service\ScheduledTask\CategorySyncTask;
use Ergonode\IntegrationShopware\Service\ScheduledTask\CategoryTreeSyncTask;
use Ergonode\IntegrationShopware\Service\ScheduledTask\DeletedAttributeSyncTask;
use Ergonode\IntegrationShopware\Service\ScheduledTask\DeletedProductSyncTask;
use Ergonode\IntegrationShopware\Service\ScheduledTask\ProductSyncTask;
use Ergonode\IntegrationShopware\Service\ScheduledTask\ProductVisibilitySyncTask;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"api"}})
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
     *     methods={"POST"}
     * )
     * @return JsonResponse
     */
    public function triggerSync(): JsonResponse
    {
        $this->messageBus->dispatch(new CategorySyncTask());
        $this->messageBus->dispatch(new ProductSyncTask());
        $this->messageBus->dispatch(new ProductVisibilitySyncTask());
        $this->messageBus->dispatch(new DeletedProductSyncTask());
        $this->messageBus->dispatch(new DeletedAttributeSyncTask());

        return new JsonResponse([
            'success' => true,
        ]);
    }
}
