<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Controller;

use Ergonode\IntegrationShopware\Service\AttributeMapper;
use Shopware\Core\Framework\Validation\DataBag\QueryDataBag;
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
    public function ergonodeAttributes(QueryDataBag $dataBag): JsonResponse
    {
        $types = $dataBag->get('types', []);
        if ($types instanceof QueryDataBag) {
            $types = $types->all();
        }

        if (!is_array($types)) {
            return new JsonResponse([
                'message' => 'Field types must be array.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $attributes = $this->attributeMapper->getAllErgonodeAttributes($types);

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