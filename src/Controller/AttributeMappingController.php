<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Controller;

use Ergonode\IntegrationShopware\Service\AttributeMapper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"api"}})
 */
class AttributeMappingController extends AbstractController
{
    private AttributeMapper $attributeMapper;

    public function __construct(
        AttributeMapper $attributeMapper
    ) {
        $this->attributeMapper = $attributeMapper;
    }

    /**
     * @Route("/api/ergonode/ergonode-attributes", name="api.ergonode.ergonodeAttributes", methods={"GET"})
     */
    public function ergonodeAttributes(): JsonResponse
    {
        $attributes = $this->attributeMapper->getAllErgonodeAttributes();

        return new JsonResponse([
            'data' => $attributes,
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/api/ergonode/shopware-attributes", name="api.ergonode.shopwareAttributes", methods={"GET"})
     */
    public function shopwareAttributes(): JsonResponse
    {
        $attributes = $this->attributeMapper->getMappableShopwareAttributes();

        return new JsonResponse([
            'data' => $attributes,
        ], Response::HTTP_OK);
    }
}