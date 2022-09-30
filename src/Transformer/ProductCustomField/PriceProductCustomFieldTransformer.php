<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\ProductCustomField;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Ergonode\IntegrationShopware\Transformer\TranslationTransformer;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Currency\CurrencyEntity;

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

    public function transformNode(array $node, string $customFieldName, Context $context): array
    {
        $translated = $this->translationTransformer->transform(
            $node['translations']
        );

        $currencyId = $this->getCurrencyIdByCode($node['attribute']['currency'] ?? '', $context);

        foreach ($translated as &$value) {
            $value = [
                'customFields' => [
                    $customFieldName => [
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

        /** @var CurrencyEntity|null $currency * */
        $currency = $this->currencyRepository->search($criteria, $context)->first();

        if (null !== $currency) {
            return $currency->getId();
        }

        return Defaults::CURRENCY;
    }
}
