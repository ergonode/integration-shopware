<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Controller\Admin;

use Ergonode\IntegrationShopware\Manager\ErgonodeCursorManager;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"api"}})
 */
class ClearCursorsController extends AbstractController
{
    private ErgonodeCursorManager $cursorManager;

    private LoggerInterface $logger;

    public function __construct(ErgonodeCursorManager $cursorManager, LoggerInterface $logger)
    {
        $this->cursorManager = $cursorManager;
        $this->logger = $logger;
    }

    /**
     * @Route(
     *     "/api/_action/ergonode/clear-cursors",
     *     name="api.admin.ergonode.clear-cursors",
     *     methods={"POST"}
     * )
     * @return JsonResponse
     */
    public function clearCursors(Context $context): JsonResponse
    {
        $success = false;
        try {
            $this->cursorManager->deleteCursors([], $context);
            $success = true;
        } catch (\Throwable $e) {
            $this->logger->error('Error while clearing Ergonode cursors', [
                'message' => $e->getMessage()
            ]);
        }

        return new JsonResponse([
            'success' => $success,
        ]);
    }
}
