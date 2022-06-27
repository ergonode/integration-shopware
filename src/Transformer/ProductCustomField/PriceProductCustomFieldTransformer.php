<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer\ProductCustomField;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Currency\CurrencyEntity;
use Strix\Ergonode\Enum\AttributeTypesEnum;
use Strix\Ergonode\Transformer\TranslationTransformer;
use Strix\Ergonode\Util\CustomFieldUtil;

class PriceProductCustomFieldTransformer implements ProductCustomFieldTransformerInterface
{
    private TranslationTransformer $translationTransformer;

    private EntityRepositoryInterface $currencyRepository;

    public function __construct(
        TranslationTransformer $translationTransformer,
        EntityRepositoryInterface $currencyRepository
    ) {
        $this->translationTransformer = $translationTransformer;
        $this->currencyRepository = $currencyRepository;
    }

    public function supports(array $node): bool
    {
        return AttributeTypesEnum::PRICE === AttributeTypesEnum::getNodeType($node['attribute']);
    }

    public function transformNode(array $node, Context $context): array
    {
        $code = $node['attribute']['code'];

        $translated = $this->translationTransformer->transform(
            $node['valueTranslations']
        );

        $currencyId = $this->getCurrencyIdByCode($node['attribute']['currency'] ?? '', $context);

        foreach ($translated as &$value) {
            $value = [
                'customFields' => [
                    CustomFieldUtil::buildCustomFieldName($code) => [
                        [
                            'net' => $value,
                            'gross' => $value,
                            'linked' => true,
                            'listPrice' => null,
                            'currencyId' => $currencyId,
                            'extensions' => [],
                            'percentage' => null,
                            'regulationPrice' => null,
                        ],
                    ],
                ],
            ];
        }

        return $translated;
    }

    private function getCurrencyIdByCode(string $code, Context $context): string
    {
        if (empty($code)) {
            return Defaults::CURRENCY;
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('isoCode', $code));

        $currency = $this->currencyRepository->search($criteria, $context)->first();

        if ($currency instanceof CurrencyEntity) {
            return $currency->getId();
        }

        return Defaults::CURRENCY;
    }
}