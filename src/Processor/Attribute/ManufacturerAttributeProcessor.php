<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor\Attribute;

use Ergonode\IntegrationShopware\Entity\ErgonodeMappingExtension\ErgonodeMappingExtensionEntity;
use Ergonode\IntegrationShopware\Provider\AttributeMappingProvider;
use Ergonode\IntegrationShopware\Util\IsoCodeConverter;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;

class ManufacturerAttributeProcessor implements AttributeCustomProcessorInterface
{
    private const SHOPWARE_KEY = 'manufacturer';
    public const MAPPING_TYPE = 'manufacturer';

    private AttributeMappingProvider $attributeMappingProvider;

    private EntityRepository $manufacturerRepository;

    private EntityRepository $mappingExtensionRepository;

    public function __construct(
        AttributeMappingProvider $attributeMappingProvider,
        EntityRepository $manufacturerRepository,
        EntityRepository $mappingExtensionRepository
    ) {
        $this->attributeMappingProvider = $attributeMappingProvider;
        $this->manufacturerRepository = $manufacturerRepository;
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
        $manufacturerIds = [];
        foreach ($node['optionList']['edges'] ?? [] as $optionNode) {
            $option = $optionNode['node'];
            $code = $option['code'];
            $manufacturerEntity = $this->getExistingManufacturerEntity($code, $context);

            $translations = [];
            foreach ($option['name'] as $nameRow) {
                $translations[IsoCodeConverter::ergonodeToShopwareIso($nameRow['language'])] = [
                    'name' => $nameRow['value'] ?? $option['code'],
                ];
            }
            $data = [
                'translations' => $translations
            ];

            if ($manufacturerEntity instanceof ProductManufacturerEntity) {
                $data['id'] = $manufacturerEntity->getId();
            }

            $resultEvent = $this->manufacturerRepository->upsert([$data], $context);
            $manufacturerId = $resultEvent->getPrimaryKeys(ProductManufacturerDefinition::ENTITY_NAME)[0];

            $mappingData = [
                'type' => self::MAPPING_TYPE,
                'code' => $code,
                'id' => $manufacturerId,
            ];

            $manufacturerIds[] = $manufacturerId;

            $this->mappingExtensionRepository->upsert([$mappingData], $context);
        }

        $this->removeLegacyManufacturers($manufacturerIds, $context);
    }

    private function removeLegacyManufacturers(array $processedIds, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new NotFilter(
                MultiFilter::CONNECTION_AND,
                [new EqualsAnyFilter('id', $processedIds)]
            )
        );
        $criteria->addFilter(new EqualsFilter('type', self::MAPPING_TYPE));

        $existingIds = $this->mappingExtensionRepository->searchIds($criteria, $context)->getIds();
        if (empty($existingIds)) {
            return;
        }

        $this->manufacturerRepository->delete([$existingIds], $context);
        $this->mappingExtensionRepository->delete([$existingIds], $context);
    }

    private function getExistingManufacturerEntity(string $code, Context $context): ?ProductManufacturerEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('code', $code));
        $criteria->addFilter(new EqualsFilter('type', self::MAPPING_TYPE));
        $result = $this->mappingExtensionRepository->search($criteria, $context);
        $mappingEntity = $result->getEntities()->first();
        if ($mappingEntity instanceof ErgonodeMappingExtensionEntity) {
            $unitCriteria = new Criteria();
            $unitCriteria->addFilter(new EqualsFilter('id', $mappingEntity->getId()));
            $unitResult = $this->manufacturerRepository->search($unitCriteria, $context);
            return $unitResult->getEntities()->first();
        }

        return null;
    }
}
