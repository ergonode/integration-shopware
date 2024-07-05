<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\CategoryTransformationDTO;
use Shopware\Core\Framework\Context;

interface CategoryDataTransformerInterface
{
    public function transform(CategoryTransformationDTO $categoryData, Context $context): CategoryTransformationDTO;
}