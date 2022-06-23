<?php

declare(strict_types=1);

namespace Strix\Ergonode\Processor;

use Shopware\Core\Framework\Context;
use Strix\Ergonode\Exception\MissingRequiredProductMappingException;
use Strix\Ergonode\Modules\Product\Provider\ErgonodeProductProvider;
use Strix\Ergonode\Persistor\ProductMediaPersistor;
use Strix\Ergonode\Persistor\ProductPersistor;

class ProductSyncProcessor
{
    private ErgonodeProductProvider $ergonodeProductProvider;

    private ProductPersistor $productPersistor;

    private ProductMediaPersistor $productMediaPersistor;

    public function __construct(
        ErgonodeProductProvider $ergonodeProductProvider,
        ProductPersistor $productPersistor,
        ProductMediaPersistor $productMediaPersistor
    ) {
        $this->ergonodeProductProvider = $ergonodeProductProvider;
        $this->productPersistor = $productPersistor;
        $this->productMediaPersistor = $productMediaPersistor;
    }

    /**
     * @throws MissingRequiredProductMappingException
     */
    public function process(Context $context): array
    {
        $result = $this->ergonodeProductProvider->provideProductWithVariants('fko002'); // todo fetch stream

        if (null === $result) {
            return [];
        }

        return $this->productPersistor->persist($result, $context);
    }
}