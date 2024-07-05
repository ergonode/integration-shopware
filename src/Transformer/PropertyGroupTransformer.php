<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\PropertyGroupTransformationDTO;
use Ergonode\IntegrationShopware\Entity\ErgonodeMappingExtension\ErgonodeMappingExtensionEntity;
use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Ergonode\IntegrationShopware\Extension\PropertyGroup\PropertyGroupExtension;
use Ergonode\IntegrationShopware\Extension\PropertyGroupOption\PropertyGroupOptionExtension;
use Ergonode\IntegrationShopware\Util\CodeBuilderUtil;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Symfony\Component\String\UnicodeString;

class PropertyGroupTransformer
{
    private TranslationTransformer $translationTransformer;

    private LoggerInterface $ergonodeSyncLogger;

    public function __construct(
        TranslationTransformer $translationTransformer,
        LoggerInterface $ergonodeSyncLogger
    ) {
        $this->translationTransformer = $translationTransformer;
        $this->ergonodeSyncLogger = $ergonodeSyncLogger;
    }

    public function transformAttributeNode(PropertyGroupTransformationDTO $dto): PropertyGroupTransformationDTO
    {
        $node = $dto->getErgonodeData();

        if (AttributeTypesEnum::SCOPE_GLOBAL !== ($node['scope'] ?? null)) { // properties in Shopware are not translatable
            return $dto;
        }

        $code = $node['code'];

        $translations = [];
        if (!empty($node['name'])) {
            $translations = $this->translationTransformer->transform($node['name'], 'name');
        }

        $propertyGroup = $dto->getSwPropertyGroup();

        $options = [];
        foreach ($node['optionList']['edges'] ?? [] as $optionNode) {
            $option = $optionNode['node'];
            if (!empty($option['code'])) {
                $optionCode = $option['code'];
                $optionCode = (new UnicodeString($optionCode))->toString();

                $existingOption = $propertyGroup ? $this->getOptionByCode($propertyGroup, $optionCode) : null;
                // avoid duplicate properties with same option code
                if ($existingOption) {
                    foreach ($options as $optionRow) {
                        if ($optionRow['id'] === $existingOption->getId()) {
                            $this->ergonodeSyncLogger->warning(
                                'Option with duplicate code. Skipped',
                                ['option' => $option['code'], 'attribute' => $code]
                            );
                            continue 2;
                        }
                    }
                }

                $options[] = [
                    'id' => $existingOption?->getId(),
                    'name' => $option['code'],
                    'translations' => $this->translationTransformer->transform($option['name'], 'name'),
                    'extensions' => [
                        AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                            'id' => $existingOption ? $this->getEntityExtensionId($existingOption) : null,
                            'code' => CodeBuilderUtil::buildExtended($code, $optionCode),
                            'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                        ],
                    ],
                ];
            }
        }

        $dto->setPropertyGroupPayload([
            'id' => $propertyGroup?->getId(),
            'name' => $code,
            'options' => $options,
            'translations' => $translations,
            'extensions' => [
                AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                    'id' => $propertyGroup ? $this->getEntityExtensionId($propertyGroup) : null,
                    'code' => $code,
                    'type' => PropertyGroupExtension::ERGONODE_TYPE,
                ],
            ],
        ]);

        $dto->setDeletePayloadIds($this->getOptionIdsToRemove($dto, $options));

        return $dto;
    }

    private function getEntityExtensionId(Entity $entity): ?string
    {
        $extension = $entity->getExtension(AbstractErgonodeMappingExtension::EXTENSION_NAME);
        if ($extension instanceof ErgonodeMappingExtensionEntity) {
            return $extension->getId();
        }

        return null;
    }

    private function getOptionByCode(PropertyGroupEntity $propertyGroup, string $code): ?PropertyGroupOptionEntity
    {
        $groupExtension = $propertyGroup->getExtension(AbstractErgonodeMappingExtension::EXTENSION_NAME);
        $options = $propertyGroup->getOptions() ?? [];

        foreach ($options as $option) {
            $extension = $option->getExtension(AbstractErgonodeMappingExtension::EXTENSION_NAME);

            if (
                $extension instanceof ErgonodeMappingExtensionEntity &&
                $groupExtension instanceof ErgonodeMappingExtensionEntity
            ) {
                $asciiExtensionCode = (new UnicodeString($extension->getCode()))->ascii()->toString();

                if (
                    CodeBuilderUtil::build($groupExtension->getCode(), $code) === $extension->getCode()
                    || CodeBuilderUtil::buildExtended($groupExtension->getCode(), $code) === $extension->getCode()
                    || CodeBuilderUtil::buildExtended($groupExtension->getCode(), $code) === $asciiExtensionCode
                ) {
                    return $option;
                }
            }
        }

        return null;
    }

    private function getOptionIdsToRemove(PropertyGroupTransformationDTO $dto, array $newOptions): array
    {
        $propertyGroup = $dto->getSwPropertyGroup();

        if (null === $propertyGroup) {
            return [];
        }

        $idsToDelete = [];
        $newOptionIds = array_filter(
            array_map(fn(array $option) => $option['id'] ?? null, $newOptions)
        );

        foreach ($propertyGroup->getOptions() ?? [] as $option) {
            if (in_array($option->getId(), $newOptionIds)) {
                continue;
            }

            $idsToDelete[] = $option->getId();
        }

        return $idsToDelete;
    }
}
