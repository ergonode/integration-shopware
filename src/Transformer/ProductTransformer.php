<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Shopware\Core\Framework\Context;
use Strix\Ergonode\Exception\MissingRequiredProductMappingException;
use Strix\Ergonode\Modules\Attribute\Provider\AttributeMappingProvider;
use Strix\Ergonode\Util\ArrayUnfoldUtil;

/**
 * Transforms Ergonode product data array into Shopware's product repository compatible array using attribute mapping
 */
class ProductTransformer
{
    private const DEFAULT_LOCALE = 'en_US';

    private const REQUIRED_KEYS = [
        'name',
        'price.net',
        'price.gross',
        'stock',
        //TODO
        //'tax.rate'
    ];

    private AttributeMappingProvider $attributeMappingProvider;

    private ArrayUnfoldUtil $arrayUnfoldUtil;

    public function __construct(
        AttributeMappingProvider $attributeMappingProvider,
        ArrayUnfoldUtil $arrayUnfoldUtil)
    {
        $this->attributeMappingProvider = $attributeMappingProvider;
        $this->arrayUnfoldUtil = $arrayUnfoldUtil;
    }

    /**
     * @throws MissingRequiredProductMappingException
     */
    public function transform(array $productData, Context $context): array
    {
        $result = [];
        foreach ($productData['attributeList']['edges'] as $edge) {
            $code = $edge['node']['attribute']['code'];
            $mappingKeys = $this->attributeMappingProvider->provideByErgonodeKey($code, $context);

            $translatedValues = [];
            foreach ($edge['node']['valueTranslations'] as $valueTranslation) {
                $translatedValues[$valueTranslation['language']] = $valueTranslation[$this->resolveValueKey($valueTranslation['__typename'])];
                //$valueTranslation['inherited'];
                //$valueTranslation['language'];
                //$valueTranslation['__typename']; // StringAttributeValue
                //$valueTranslation['value_string'];
            }

            foreach ($mappingKeys as $ergonodeAttributeMappingEntity) {
                $result[$ergonodeAttributeMappingEntity->getShopwareKey()] = $translatedValues[self::DEFAULT_LOCALE];
            }
        }

        $this->validateResult($result);

        return $this->arrayUnfoldUtil->unfoldArray($result);
    }

    /**
     * @throws MissingRequiredProductMappingException
     */
    private function validateResult(array $result): void
    {
        $missingAttributes = \array_diff(self::REQUIRED_KEYS, \array_keys($result));

        if (\count($missingAttributes) > 0) {
            throw new MissingRequiredProductMappingException($missingAttributes);
        }
    }

    private function resolveValueKey(string $typename): string
    {
        switch ($typename) {
            case 'StringAttributeValue':
                return 'value_string';
            case 'NumericAttributeValue':
                return 'value_numeric';
            case 'StringArrayAttributeValue':
                return 'value_array';
            case 'MultimediaAttributeValue':
                return 'value_multimedia';
            case 'MultimediaArrayAttributeValue':
                return 'value_multimedia_array';
            case 'ProductArrayAttributeValue':
                return 'value_product_array';
            default:
                throw new \RuntimeException(\sprintf('Unknown value typename: %s', $typename));
        }
    }
}