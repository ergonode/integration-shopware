<?php

declare(strict_types=1);

namespace Strix\Ergonode\Provider;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetEntity;
use Shopware\Core\System\CustomField\CustomFieldEntity;
use Strix\Ergonode\Util\Constants;

class CustomFieldProvider
{
    private ?string $customFieldSetId = null;

    private EntityRepositoryInterface $customFieldRepository;

    private EntityRepositoryInterface $customFieldSetRepository;

    public function __construct(
        EntityRepositoryInterface $customFieldRepository,
        EntityRepositoryInterface $customFieldSetRepository
    ) {
        $this->customFieldRepository = $customFieldRepository;
        $this->customFieldSetRepository = $customFieldSetRepository;
    }

    public function getCustomFieldByName(string $name, Context $context): ?CustomFieldEntity
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
            $set = $this->getCustomFieldSet($context);

            if (null !== $set) {
                $this->customFieldSetId = $set->getId();
            }
        }

        return $this->customFieldSetId;
    }

    public function getCustomFieldSet(Context $context): ?CustomFieldSetEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', Constants::PRODUCT_CUSTOM_FIELD_SET_NAME));

        return $this->customFieldSetRepository->search($criteria, $context)->first();
    }
}