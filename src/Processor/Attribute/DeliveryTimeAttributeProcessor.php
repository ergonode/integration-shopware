<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor\Attribute;

use Ergonode\IntegrationShopware\Entity\ErgonodeMappingExtension\ErgonodeMappingExtensionEntity;
use Ergonode\IntegrationShopware\Provider\AttributeMappingProvider;
use Ergonode\IntegrationShopware\Util\IsoCodeConverter;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\System\DeliveryTime\DeliveryTimeDefinition;
use Shopware\Core\System\DeliveryTime\DeliveryTimeEntity;

class DeliveryTimeAttributeProcessor implements AttributeCustomProcessorInterface
{
    private const SHOPWARE_KEY = 'deliveryTime';
    public const  MAPPING_TYPE = 'deliveryTime';
    protected const DELIVERY_TIME_PATTERN = '/[\s-]+/';
    protected const DELIVERY_TIME_NUMBER_PATTERN = '/\d+/';
    private const AVAILABLE_UNITS = ['hour', 'day', 'week', 'month', 'year'];
    const DEFAULT_FALLBACK_MIN = 0;
    const DEFAULT_FALLBACK_MAX = 999;
    const DEFAULT_FALLBACK_UNIT = 'day';

    private AttributeMappingProvider $attributeMappingProvider;

    private EntityRepository $deliveryTimeRepository;

    private EntityRepository $mappingExtensionRepository;

    private LoggerInterface $ergonodeSyncLogger;

    public function __construct(
        AttributeMappingProvider $attributeMappingProvider,
        EntityRepository $deliveryTimeRepository,
        EntityRepository $mappingExtensionRepository,
        LoggerInterface $ergonodeSyncLogger,
    ) {
        $this->attributeMappingProvider = $attributeMappingProvider;
        $this->deliveryTimeRepository = $deliveryTimeRepository;
        $this->mappingExtensionRepository = $mappingExtensionRepository;
        $this->ergonodeSyncLogger = $ergonodeSyncLogger;
    }

    public function isSupported(array $node, Context $context): bool
    {
        if (isset($node['code'])) {
            $mappings = $this->attributeMappingProvider->provideByErgonodeKey($node['code'], $context);
            foreach ($mappings as $mapping) {
                if ($mapping->getShopwareKey() === self::SHOPWARE_KEY) {
                    return true;
                }
            }
        }

        return false;
    }

    public function process(array $node, Context $context): void
    {
        $deliveryTimeIds = [];
        foreach ($node['optionList']['edges'] ?? [] as $edge) {
            $option = $edge['node'];
            $code = $option['code'];

            $translations = [];
            foreach ($option['name'] as $nameRow) {
                $translations[IsoCodeConverter::ergonodeToShopwareIso($nameRow['language'])] = [
                    'name' => $nameRow['value'] ?? $option['code'],
                ];
            }

            $pieces = $this->getDeliveryTimePieces($code);
            if (!$pieces) {
                $deliveryTimeIds[] = $this->createFallback($code, $translations, $context);
                $this->ergonodeSyncLogger->error(
                    sprintf('Created fallback deliveryTime for code %s. Please verify content. You can change min/max/unit values', $code)
                );
                continue;
            }

            $deliveryTimeIds[] = $this->createStandard($pieces, $code, $translations, $context);
        }

        $this->removeLegacyDeliveryTimes($deliveryTimeIds, $context);
    }

    private function removeLegacyDeliveryTimes(array $processedIds, Context $context): void
    {
        $criteria = new Criteria();
        $processedIds = array_filter($processedIds);
        $criteria->addFilter(
            new NotFilter(
                MultiFilter::CONNECTION_AND,
                [new EqualsAnyFilter('id', $processedIds)]
            )
        );
        $criteria->addFilter(new EqualsFilter('type', self::MAPPING_TYPE));
        $existingIds = $this->mappingExtensionRepository->searchIds($criteria, $context);
        $ids = array_map(fn($id) => ['id' => $id], $existingIds->getIds());
        $this->deliveryTimeRepository->delete($ids, $context);
        $this->mappingExtensionRepository->delete($ids, $context);
    }

    private function getExistingDeliveryTimeEntity(
        int $min,
        int $max,
        string $unit,
        string $code,
        Context $context
    ): ?DeliveryTimeEntity {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('code', $code));
        $criteria->addFilter(new EqualsFilter('type', self::MAPPING_TYPE));
        $result = $this->mappingExtensionRepository->search($criteria, $context);
        $mappingEntity = $result->getEntities()->first();
        if ($mappingEntity instanceof ErgonodeMappingExtensionEntity) {
            $timeCriteria = new Criteria();
            $timeCriteria->addFilter(new EqualsFilter('id', $mappingEntity->getId()));
            $timeResult = $this->deliveryTimeRepository->search($timeCriteria, $context);

            return $timeResult->getEntities()->first();
        }

        $fallbackTimeCriteria = new Criteria();
        $fallbackTimeCriteria->addFilter(new EqualsFilter('min', $min));
        $fallbackTimeCriteria->addFilter(new EqualsFilter('max', $max));
        $fallbackTimeCriteria->addFilter(new EqualsFilter('unit', $unit));
        $fallbackTimeResult = $this->deliveryTimeRepository->search($fallbackTimeCriteria, $context);

        $entity = $fallbackTimeResult->getEntities()->first();

        return $entity instanceof DeliveryTimeEntity ? $entity : null;
    }

    public function createStandard(array $pieces, string $code, array $translations, Context $context): ?string
    {
        [$min, $max, $unit] = $pieces;

        $unit = rtrim($unit, 's');

        if (!in_array($unit, self::AVAILABLE_UNITS)) {
            $this->ergonodeSyncLogger->error(
                sprintf('Invalid syntax for option %s - delivery time unit. Acceptable values: hours, days, weeks, months, years', $code)
            );

            return null;
        }

        $timeEntity = $this->getExistingDeliveryTimeEntity((int)$min, (int)$max, $unit, $code, $context);

        $data = [
            'min' => (int)$min,
            'max' => (int)$max,
            'unit' => $unit,
            'translations' => $translations,
        ];

        if ($timeEntity instanceof DeliveryTimeEntity) {
            $data['id'] = $timeEntity->getId();
        }

        $resultEvent = $this->deliveryTimeRepository->upsert([$data], $context);
        $timeId = $resultEvent->getPrimaryKeys(DeliveryTimeDefinition::ENTITY_NAME)[0];

        $mappingData = [
            'type' => self::MAPPING_TYPE,
            'code' => $code,
            'id' => $timeId,
        ];

        $this->mappingExtensionRepository->upsert([$mappingData], $context);

        return $timeId;
    }

    private function getDeliveryTimeByCode(
        string $code,
        Context $context
    ): ?DeliveryTimeEntity {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('code', $code));
        $criteria->addFilter(new EqualsFilter('type', self::MAPPING_TYPE));
        $result = $this->mappingExtensionRepository->search($criteria, $context);
        $mappingEntity = $result->getEntities()->first();
        if (!$mappingEntity instanceof ErgonodeMappingExtensionEntity) {
            return null;
        }

        $timeCriteria = new Criteria();
        $timeCriteria->addFilter(new EqualsFilter('id', $mappingEntity->getId()));
        $timeResult = $this->deliveryTimeRepository->search($timeCriteria, $context);

        return $timeResult->getEntities()->first();

    }

    public function createFallback(string $code, array $translations, Context $context): ?string
    {
        $timeEntity = $this->getDeliveryTimeByCode($code, $context);

        if ($timeEntity instanceof DeliveryTimeEntity) {
            $mappingData = [
                'type' => self::MAPPING_TYPE,
                'code' => $code,
                'id' => $timeEntity->getId(),
            ];

            $this->mappingExtensionRepository->upsert([$mappingData], $context);


            return $timeEntity->getId();
        }

        $data = [
            'min' => $this->getNumberFromCode($code) ?: self::DEFAULT_FALLBACK_MIN,
            'max' => $this->getNumberFromCode($code) ?: self::DEFAULT_FALLBACK_MAX,
            'unit' => $this->findUnitFromCode($code),
            'translations' => $translations,
        ];

        $resultEvent = $this->deliveryTimeRepository->upsert([$data], $context);
        $timeId = $resultEvent->getPrimaryKeys(DeliveryTimeDefinition::ENTITY_NAME)[0];

        $mappingData = [
            'type' => self::MAPPING_TYPE,
            'code' => $code,
            'id' => $timeId,
        ];

        $this->mappingExtensionRepository->upsert([$mappingData], $context);

        return $timeId;
    }

    private function getDeliveryTimePieces(string $code): ?array
    {
        $pieces = preg_split(self::DELIVERY_TIME_PATTERN, $code);
        if (count($pieces) !== 3) {
            return null;
        }

        return $pieces;
    }

    private function getNumberFromCode(string $code): ?int
    {
        preg_match(self::DELIVERY_TIME_NUMBER_PATTERN, $code, $matches);
        if ($matches === []) {
            return null;
        }

        return (int)$matches[0];
    }

    private function findUnitFromCode(string $code): string
    {
        foreach (self::AVAILABLE_UNITS as $unit) {
            if (str_contains(strtolower($code), $unit)) {
                return $unit;
            }
        }

        return self::DEFAULT_FALLBACK_UNIT;
    }
}
