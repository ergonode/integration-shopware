<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\ProductCustomField;

use Ergonode\IntegrationShopware\Model\ProductAttribute;
use Ergonode\IntegrationShopware\Model\ProductPriceAttribute;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Currency\CurrencyEntity;

class PriceProductCustomFieldTransformer implements ProductCustomFieldTransformerInterface
{
    private EntityRepository $currencyRepository;

    public function __construct(
        EntityRepository $currencyRepository
    ) {
        $this->currencyRepository = $currencyRepository;
    }

    public function supports(ProductAttribute $attribute): bool
    {
        return ProductAttribute::TYPE_PRICE === $attribute->getType();
    }

    public function transformNode(ProductAttribute $attribute, string $customFieldName, Context $context): array
    {
        if (!$attribute instanceof ProductPriceAttribute) {
            return [];
        }

        $currencyId = $this->getCurrencyIdByCode($attribute->getCurrency(), $context);

        $customFields = [];
        foreach ($attribute->getTranslations() as $translation) {
            $customFields[$translation->getLanguage()]['customFields'][$customFieldName] = [
                'net' => $translation->getValue(),
                'gross' => $translation->getValue(),
                'linked' => true,
                'listPrice' => null,
                'currencyId' => $currencyId,
                'extensions' => [],
                'percentage' => null,
                'regulationPrice' => null,
            ];
        }

        return $customFields;
    }

    private function getCurrencyIdByCode(string $code, Context $context): string
    {
        if (empty($code)) {
            return Defaults::CURRENCY;
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('isoCode', $code));

        /** @var CurrencyEntity|null $currency * */
        $currency = $this->currencyRepository->search($criteria, $context)->first();

        if (null !== $currency) {
            return $currency->getId();
        }

        return Defaults::CURRENCY;
    }
}
