<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Ergonode\IntegrationShopware\Util\Constants;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetEntity;
use Shopware\Core\System\CustomField\CustomFieldEntity;

class CustomFieldProvider
{
    private ?string $customFieldSetId = null;

    private EntityRepository $customFieldRepository;

    private EntityRepository $customFieldSetRepository;

    public function __construct(
        EntityRepository $customFieldRepository,
        EntityRepository $customFieldSetRepository
    ) {
        $this->customFieldRepository = $customFieldRepository;
        $this->customFieldSetRepository = $customFieldSetRepository;
    }

    public function getCustomFieldByName(string $name, Context $context): ?CustomFieldEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $name));
        $criteria->addAssociation('customFieldSet');

        return $this->customFieldRepository->search($criteria, $context)->first();
    }

    public function getErgonodeCustomFieldByName(string $name, Context $context): ?CustomFieldEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $name));
        $criteria->addFilter(new EqualsFilter('customFieldSet.name', Constants::PRODUCT_CUSTOM_FIELD_SET_NAME));
        $criteria->addAssociation('customFieldSet');

        return $this->customFieldRepository->search($criteria, $context)->first();
    }

    public function getErgonodeCustomFieldSetId(Context $context): ?string
    {
        if (null === $this->customFieldSetId) {
            $set = $this->getErgonodeCustomFieldSet($context);

            if (null !== $set) {
                $this->customFieldSetId = $set->getId();
            }
        }

        return $this->customFieldSetId;
    }

    public function getErgonodeCustomFieldSet(Context $context): ?CustomFieldSetEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', Constants::PRODUCT_CUSTOM_FIELD_SET_NAME));

        return $this->customFieldSetRepository->search($criteria, $context)->first();
    }

    /**
     * @param string[] $codes
     */
    public function getIdsByCodes(array $codes, Context $context): array
    {
        $codes = \array_map(static fn($code) => Constants::PRODUCT_CUSTOM_FIELD_SET_NAME . '_' . $code, $codes);
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('name', $codes));

        return $this->customFieldRepository->searchIds($criteria, $context)->getIds();
    }
}
