<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

use Ergonode\IntegrationShopware\Exception\InvalidCustomFieldTypeException;
use Ergonode\IntegrationShopware\Provider\CustomFieldProvider;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;

class CustomFieldTypeValidator extends AttributeTypeValidator
{
    protected string $exceptionClass = InvalidCustomFieldTypeException::class;

    private CustomFieldProvider $customFieldProvider;

    public function __construct(
        LoggerInterface $ergonodeSyncLogger,
        CustomFieldProvider $customFieldProvider
    ) {
        $this->customFieldProvider = $customFieldProvider;

        parent::__construct($ergonodeSyncLogger);
    }

    protected function getValidTypes(string $swKey, Context $context): array
    {
        $customField = $this->customFieldProvider->getCustomFieldByName($swKey, $context);
        $map = CustomFieldUtil::getValidErgonodeTypes($customField);

        return array_map(fn(string $type) => str_replace('type_', '', $type), $map);
    }
}