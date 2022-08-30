<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingCollection;
use Ergonode\IntegrationShopware\Exception\InvalidAttributeTypeException;
use Ergonode\IntegrationShopware\Exception\MissingRequiredProductMappingException;
use Ergonode\IntegrationShopware\Provider\AttributeMappingProvider;
use Ergonode\IntegrationShopware\Provider\LanguageProvider;
use Ergonode\IntegrationShopware\Util\ArrayUnfoldUtil;
use Ergonode\IntegrationShopware\Util\AttributeTypeValidator;
use Ergonode\IntegrationShopware\Util\ErgonodeApiValueKeyResolverUtil;
use Ergonode\IntegrationShopware\Util\IsoCodeConverter;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shopware\Core\Framework\Context;

use function array_key_exists;
use function array_merge_recursive;
use function in_array;
use function is_array;
use function sprintf;

class ProductTransformer implements ProductDataTransformerInterface
{
    private string $defaultLocale;

    private const TRANSLATABLE_KEYS = [
        'name',
        'description',
        'metaDescription',
        'keywords',
        'metaTitle',
        'packUnit',
        'packUnitPlural',
        'customSearchKeywords',
    ];

    private AttributeMappingProvider $attributeMappingProvider;

    private LanguageProvider $languageProvider;

    private AttributeTypeValidator $attributeTypeValidator;

    private LoggerInterface $logger;

    public function __construct(
        AttributeMappingProvider $attributeMappingProvider,
        LanguageProvider $languageProvider,
        AttributeTypeValidator $attributeTypeValidator,
        LoggerInterface $ergonodeSyncLogger
    ) {
        $this->attributeMappingProvider = $attributeMappingProvider;
        $this->languageProvider = $languageProvider;
        $this->attributeTypeValidator = $attributeTypeValidator;
        $this->logger = $ergonodeSyncLogger;
    }

    /**
     * @throws MissingRequiredProductMappingException
     */
    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $ergonodeData = $productData->getErgonodeData();
        if (false === is_array($ergonodeData['attributeList']['edges'] ?? null)) {
            throw new RuntimeException('Invalid data format');
        }

        $this->defaultLocale = IsoCodeConverter::shopwareToErgonodeIso(
            $this->languageProvider->getDefaultLanguageLocale($context)
        );

        $result = [];

        foreach ($ergonodeData['attributeList']['edges'] as $edge) {
            $code = $edge['node']['attribute']['code'];
            $mappingKeys = $this->attributeMappingProvider->provideByErgonodeKey($code, $context);

            $this->filterWrongMappings($edge['node']['attribute'], $mappingKeys, ['sku' => $ergonodeData['sku']]);

            if (0 === $mappingKeys->count()) {
                continue;
            }

            $translatedValues = $this->getTranslatedValues($edge['node']['valueTranslations']);

            if (false === array_key_exists($this->defaultLocale, $translatedValues)) {
                throw new RuntimeException(
                    sprintf('Default locale %s not found in product data', $this->defaultLocale)
                );
            }

            $result = array_merge_recursive(
                $result,
                $this->getTransformedResult($mappingKeys, $translatedValues)
            );
        }

        if ($productData->isUpdate()) {
            $result['id'] = $productData->getSwProduct()->getId();
        }

        $productData->setShopwareData(
            ArrayUnfoldUtil::unfoldArray($result)
        );

        return $productData;
    }

    private function getTranslatedValues(array $valueTranslations): array
    {
        $translatedValues = [];
        foreach ($valueTranslations as $valueTranslation) {
            $valueKey = ErgonodeApiValueKeyResolverUtil::resolve($valueTranslation['__typename']);
            $translatedValues[$valueTranslation['language']] = $valueTranslation[$valueKey];
        }

        return $translatedValues;
    }

    private function getTransformedResult(
        ErgonodeAttributeMappingCollection $mappingKeys,
        array $translatedValues
    ): array {
        $result = [];
        foreach ($mappingKeys as $ergonodeAttributeMappingEntity) {
            $swKey = $ergonodeAttributeMappingEntity->getShopwareKey();
            $result[$swKey] = $translatedValues[$this->defaultLocale];
            $result = $this->getTranslations($translatedValues, $swKey, $result);
        }

        return $result;
    }

    private function getTranslations(array $translatedValues, string $swKey, array $result): array
    {
        foreach ($translatedValues as $locale => $value) {
            if (null === $value || false === in_array($swKey, self::TRANSLATABLE_KEYS)) {
                continue;
            }

            $swLocale = IsoCodeConverter::ergonodeToShopwareIso($locale);
            $result['translations'][$swLocale][$swKey] = $value;
        }

        return $result;
    }

    private function filterWrongMappings(
        array $attribute,
        ErgonodeAttributeMappingCollection $mappingKeys,
        array $logContext = []
    ): void {
        if (empty($attribute)) {
            return;
        }

        foreach ($mappingKeys as $key => $mappingKey) {
            try {
                $this->attributeTypeValidator->validate(
                    $attribute,
                    $mappingKey,
                    true
                );
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
}