<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Controller\Admin;

use Ergonode\IntegrationShopware\Provider\MappableFieldsProvider;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Validation\DataBag\QueryDataBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class MappingController extends AbstractController
{
    private MappableFieldsProvider $mappableFieldsProvider;

    public function __construct(
        MappableFieldsProvider $mappableFieldsProvider
    ) {
        $this->mappableFieldsProvider = $mappableFieldsProvider;
    }

    #[Route(path: '/api/ergonode/ergonode-attributes', name: 'api.ergonode.ergonodeAttributes', methods: ['GET'])]
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

        $attributes = $this->mappableFieldsProvider->getErgonodeAttributesWithTypes($types);

        return new JsonResponse([
            'data' => $attributes,
        ], Response::HTTP_OK);
    }

    #[Route(path: '/api/ergonode/ergonode-category-trees', name: 'api.ergonode.ergonodeCategoryTrees', methods: ['GET'])]
    public function ergonodeCategoryTrees(): JsonResponse
    {
        $codes = $this->mappableFieldsProvider->getErgonodeCategoryTreeCodes();

        return new JsonResponse([
            'data' => $codes,
        ], Response::HTTP_OK);
    }

    #[Route(path: '/api/ergonode/shopware-attributes', name: 'api.ergonode.shopwareAttributes', methods: ['GET'])]
    public function shopwareAttributes(): JsonResponse
    {
        $attributes = $this->mappableFieldsProvider->getShopwareAttributesWithTypes();

        return new JsonResponse([
            'data' => $attributes,
        ], Response::HTTP_OK);
    }

    #[Route(path: '/api/ergonode/shopware-custom-fields', name: 'api.ergonode.shopwareCustomFields', methods: ['GET'])]
    public function shopwareCustomFields(Context $context): JsonResponse
    {
        $attributes = $this->mappableFieldsProvider->getShopwareCustomFieldsWithTypes($context);

        return new JsonResponse([
            'data' => $attributes,
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/api/ergonode/shopware-categories", name="api.ergonode.shopwareCategories", methods={"GET"})
     */
    public function shopwareCategories(Context $context): JsonResponse
    {
        $attributes = $this->mappableFieldsProvider->getShopwareCategories($context);

        return new JsonResponse([
            'data' => $attributes,
        ], Response::HTTP_OK);
    }


    /**
     * @Route("/api/ergonode/ergonode-categories", name="api.ergonode.ergonodeCategories", methods={"GET"})
     */
    public function ergonodeCategories(): JsonResponse
    {
        $attributes = $this->mappableFieldsProvider->getErgonodeCategories();

        return new JsonResponse([
            'data' => $attributes,
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/api/ergonode/timezones", name="api.ergonode.timezones", methods={"GET"})
     */
    #[Route(path: '/api/ergonode/timezones', name: 'api.ergonode.timezones', methods: ['GET'])]
    public function timezones(): JsonResponse
    {
        $timezones = timezone_identifiers_list();

        return new JsonResponse([
            'data' => $timezones,
        ], Response::HTTP_OK);
    }


    #[Route(path: '/api/ergonode/shopware-category-attributes', name: 'api.ergonode.shopwareCategoryAttributes',
        methods: ['GET'])]
    public function shopwareCategoryAttributes(): JsonResponse
    {
        $attributes = $this->mappableFieldsProvider->getShopwareCategoriesAttributesWithTypes();

        return new JsonResponse([
            'data' => $attributes,
        ], Response::HTTP_OK);
    }


    #[Route(path: '/api/ergonode/ergonode-category-attributes', name: 'api.ergonode.ergonodeCategoryAttributes',
        methods: ['GET'])]
    public function ergonodeCategoryAttributes(QueryDataBag $dataBag): JsonResponse
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

        $attributes = $this->mappableFieldsProvider->getErgonodeCategoryAttributesWithTypes($types);

        return new JsonResponse([
            'data' => $attributes,
        ], Response::HTTP_OK);
    }
}
