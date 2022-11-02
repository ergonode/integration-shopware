<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

use Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingCollection;
use Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingEntity;
use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Ergonode\IntegrationShopware\Exception\InvalidAttributeTypeException;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;

class AttributeTypeValidator
{
    protected string $exceptionClass = InvalidAttributeTypeException::class;

    protected LoggerInterface $logger;

    public function __construct(
        LoggerInterface $ergonodeSyncLogger
    ) {
        $this->logger = $ergonodeSyncLogger;
    }

    public function filterWrongAttributes(
        array $attribute,
        ErgonodeAttributeMappingCollection $mappingKeys,
        Context $context,
        array $logContext = []
    ): void {
        if (empty($attribute)) {
            return;
        }

        foreach ($mappingKeys as $key => $mappingKey) {
            try {
                $this->validate($attribute, $mappingKey, $context);
            } catch (InvalidAttributeTypeException $e) {
                $mappingKeys->remove($key);

                // TODO SWERG-84: remove inlined context from message after adding context display in admin
                $this->logger->warning(
                    sprintf(
                        '%s [sku: %s; actualType: %s, validTypes: %s, ergonodeKey: %s; shopwareKey: %s]',
                        $e->getMessage(),
                        $logContext['sku'] ?? '',
                        $e->getActualType(),
                        $e->getValidTypesStr(),
                        $e->getMapping()->getErgonodeKey(),
                        $e->getMapping()->getShopwareKey(),
                    ),
                    array_merge($logContext, [
                        'actualType' => $e->getActualType(),
                        'validTypes' => $e->getValidTypes(),
                        'ergonodeKey' => $e->getMapping()->getErgonodeKey(),
                        'shopwareKey' => $e->getMapping()->getShopwareKey(),
                    ])
                );
            }
        }
    }

    /**
     * Actual validation.
     *
     * @throws InvalidAttributeTypeException
     */
    private function validate(
        array $ergonodeAttribute,
        ErgonodeAttributeMappingEntity $mapping,
        Context $context
    ): void {
        $swKey = $mapping->getShopwareKey();
        $validTypes = $this->getValidTypes($swKey, $context);
        if ($mapping->isCastToBool() && !in_array(Constants::ATTRIBUTE_TYPE_SELECT, $validTypes)) {
            $validTypes[] = Constants::ATTRIBUTE_TYPE_SELECT;
        }

        if (empty($ergonodeAttribute)) {
            throw new $this->exceptionClass($mapping, $validTypes);
        }

        $actualType = AttributeTypesEnum::getShortNodeType($ergonodeAttribute);

        if (false === in_array($actualType, $validTypes)) {
            throw new $this->exceptionClass($mapping, $validTypes, $actualType);
        }
    }

    protected function getValidTypes(string $swKey, Context $context): array
    {
        return Constants::SW_PRODUCT_MAPPABLE_FIELDS[$swKey] ?? [];
    }
}
