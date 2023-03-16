<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor\Attribute;

use Ergonode\IntegrationShopware\Entity\ErgonodeMappingExtension\ErgonodeMappingExtensionEntity;
use Ergonode\IntegrationShopware\Provider\AttributeMappingProvider;
use Ergonode\IntegrationShopware\Util\IsoCodeConverter;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\System\Unit\UnitDefinition;
use Shopware\Core\System\Unit\UnitEntity;

class ScaleUnitAttributeProcessor implements AttributeCustomProcessorInterface
{
    private const SHOPWARE_KEY = 'scaleUnit';
    private const MAPPING_TYPE = 'scale_unit_option';

    private AttributeMappingProvider $attributeMappingProvider;

    private EntityRepository $unitRepository;

    private EntityRepository $mappingExtensionRepository;

    public function __construct(
        AttributeMappingProvider $attributeMappingProvider,
        EntityRepository $unitRepository,
        EntityRepository $mappingExtensionRepository
    ) {
        $this->attributeMappingProvider = $attributeMappingProvider;
        $this->unitRepository = $unitRepository;
        $this->mappingExtensionRepository = $mappingExtensionRepository;
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
        $unitIds = [];
        foreach ($node['options'] ?? [] as $option) {
            $code = $option['code'];
            $unitEntity = $this->getExistingUnitEntity($code, $context);

            $data = [
                'shortCode' => $code,
                'name' => $code,
            ];
            $translations = [];
            foreach ($option['name'] as $nameRow) {
                if (empty($nameRow['value'])) {
                    continue;
                }
                $translations[IsoCodeConverter::ergonodeToShopwareIso($nameRow['language'])] = [
                    'name' => $nameRow['value'],
                    'shortCode' => $nameRow['value'],
                ];
            }
            $data['translations'] = $translations;

            if ($unitEntity instanceof UnitEntity) {
                $data['id'] = $unitEntity->getId();
            }

            $resultEvent = $this->unitRepository->upsert([$data], $context);
            $unitId = $resultEvent->getPrimaryKeys(UnitDefinition::ENTITY_NAME)[0];

            $mappingData = [
                'type' => self::MAPPING_TYPE,
                'code' => $code,
                'id' => $unitId,
            ];

            $unitIds[] = $unitId;

            $this->mappingExtensionRepository->upsert([$mappingData], $context);
        }

        $this->removeLegacyUnits($unitIds, $context);
    }

    private function removeLegacyUnits(array $processedIds, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new NotFilter(
                MultiFilter::CONNECTION_AND,
                [new EqualsAnyFilter('id', $processedIds)]
            )
        );
        $criteria->addFilter(new EqualsFilter('type', self::MAPPING_TYPE));
        $existingIds = $this->mappingExtensionRepository->searchIds($criteria, $context);
        $this->unitRepository->delete([$existingIds->getIds()], $context);
        $this->mappingExtensionRepository->delete([$existingIds->getIds()], $context);
    }

    private function getExistingUnitEntity(string $code, Context $context): ?UnitEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('code', $code));
        $criteria->addFilter(new EqualsFilter('type', self::MAPPING_TYPE));
        $result = $this->mappingExtensionRepository->search($criteria, $context);
        $mappingEntity = $result->getEntities()->first();
        if ($mappingEntity instanceof ErgonodeMappingExtensionEntity) {
            $unitCriteria = new Criteria();
            $unitCriteria->addFilter(new EqualsFilter('id', $mappingEntity->getId()));
            $unitResult = $this->unitRepository->search($unitCriteria, $context);
            return $unitResult->getEntities()->first();
        }

        return null;
    }
}
