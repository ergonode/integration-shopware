<?php

declare(strict_types=1);

namespace Strix\Ergonode\Controller;

use Strix\Ergonode\Service\ScheduledTask\ProductVisibilitySyncTask;
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
     *     "/api/_action/strix/ergonode/trigger-visibility-sync",
     *     name="api.admin.strix.ergonode.trigger-visibility-sync",
     *     methods={"POST"}
     * )
     * @return JsonResponse
     */
    public function triggerVisibilitySync(): JsonResponse
    {
        $this->messageBus->dispatch(new ProductVisibilitySyncTask());

        return new JsonResponse([
            'success' => true,
        ]);
    }
}