<?php

declare(strict_types=1);

namespace Strix\Ergonode\Processor;

use Shopware\Core\Framework\Context;
use Strix\Ergonode\Exception\MissingRequiredProductMappingException;
use Strix\Ergonode\Modules\Product\Provider\ErgonodeProductProvider;
use Strix\Ergonode\Persistor\ProductPersistor;

class ProductSyncProcessor
{
    private ErgonodeProductProvider $ergonodeProductProvider;

    private ProductPersistor $productPersistor;

    public function __construct(
        ErgonodeProductProvider $ergonodeProductProvider,
        ProductPersistor $productPersistor
    ) {
        $this->ergonodeProductProvider = $ergonodeProductProvider;
        $this->productPersistor = $productPersistor;
    }

    /**
     * @throws MissingRequiredProductMappingException
     */
    public function process(Context $context): void
    {
        $result = $this->ergonodeProductProvider->provideProductWithVariants('fko002'); // todo fetch stream

        if (null === $result) {
            return;
        }

        $this->productPersistor->persist($result, $context);
    }
}