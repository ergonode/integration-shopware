<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Controller\Admin;

use Ergonode\IntegrationShopware\Manager\ErgonodeCursorManager;
use Ergonode\IntegrationShopware\MessageQueue\Message\AttributeSync;
use Ergonode\IntegrationShopware\MessageQueue\Message\CategorySync;
use Ergonode\IntegrationShopware\MessageQueue\Message\DeletedAttributeSync;
use Ergonode\IntegrationShopware\MessageQueue\Message\DeletedProductSync;
use Ergonode\IntegrationShopware\MessageQueue\Message\LanguageSync;
use Ergonode\IntegrationShopware\MessageQueue\Message\ProductSync;
use Ergonode\IntegrationShopware\MessageQueue\Message\ProductVisibilitySync;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
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
     *
     * @param RequestDataBag $dataBag
     * @param Context $context
     * @return JsonResponse
     */
    #[Route(path: '/api/_action/ergonode/trigger-sync', name: 'api.admin.ergonode.trigger-sync', methods: ['POST'])]
    public function triggerSync(RequestDataBag $dataBag, Context $context): JsonResponse
    {
        $force = $dataBag->getBoolean('force');

        if ($force) {
            $this->cursorManager->deleteCursors([], $context);
        }

        $this->messageBus->dispatch(new LanguageSync());
        $this->messageBus->dispatch(new AttributeSync());
        $this->messageBus->dispatch(new CategorySync());
        $this->messageBus->dispatch(new ProductSync());
        $this->messageBus->dispatch(new ProductVisibilitySync());
        $this->messageBus->dispatch(new DeletedProductSync());
        $this->messageBus->dispatch(new DeletedAttributeSync());

        return new JsonResponse([
            'success' => true,
        ]);
    }
}
