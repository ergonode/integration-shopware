<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Controller\Admin;

use Ergonode\IntegrationShopware\Manager\ErgonodeCursorManager;
use Ergonode\IntegrationShopware\Service\ScheduledTask\CategorySyncTask;
use Ergonode\IntegrationShopware\Service\ScheduledTask\CategoryTreeSyncTask;
use Ergonode\IntegrationShopware\Service\ScheduledTask\DeletedAttributeSyncTask;
use Ergonode\IntegrationShopware\Service\ScheduledTask\DeletedProductSyncTask;
use Ergonode\IntegrationShopware\Service\ScheduledTask\ProductSyncTask;
use Ergonode\IntegrationShopware\Service\ScheduledTask\ProductVisibilitySyncTask;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
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

    private ErgonodeCursorManager $cursorManager;

    public function __construct(
        MessageBusInterface $messageBus,
        ErgonodeCursorManager $cursorManager
    ) {
        $this->messageBus = $messageBus;
        $this->cursorManager = $cursorManager;
    }

    /**
     * @Route(
     *     "/api/_action/ergonode/trigger-sync",
     *     name="api.admin.ergonode.trigger-sync",
     *     methods={"POST"}
     * )
     *
     * @param RequestDataBag $dataBag
     * @param Context $context
     * @return JsonResponse
     */
    public function triggerSync(RequestDataBag $dataBag, Context $context): JsonResponse
    {
        $force = $dataBag->getBoolean('force');

        if ($force) {
            $this->cursorManager->deleteCursors([], $context);
        }

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
