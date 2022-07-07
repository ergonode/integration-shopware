<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Strix\Ergonode\DTO\PropertyGroupTransformationDTO;
use Strix\Ergonode\Entity\ErgonodeMappingExtension\ErgonodeMappingExtensionEntity;
use Strix\Ergonode\Extension\AbstractErgonodeMappingExtension;
use Strix\Ergonode\Extension\PropertyGroup\PropertyGroupExtension;
use Strix\Ergonode\Extension\PropertyGroupOption\PropertyGroupOptionExtension;
use Strix\Ergonode\Provider\PropertyGroupProvider;
use Strix\Ergonode\Util\Constants;
use Strix\Ergonode\Util\PropertyGroupOptionUtil;

use function array_merge_recursive;

class PropertyGroupTransformer
{
    private PropertyGroupProvider $propertyGroupProvider;

    private TranslationTransformer $translationTransformer;

    public function __construct(
        PropertyGroupProvider $propertyGroupProvider,
        TranslationTransformer $translationTransformer
    ) {
        $this->propertyGroupProvider = $propertyGroupProvider;
        $this->translationTransformer = $translationTransformer;
    }

    public function transformAttributeNode(PropertyGroupTransformationDTO $dto, Context $context): PropertyGroupTransformationDTO
    {
        $node = $dto->getErgonodeData();

        if (Constants::ATTRIBUTE_SCOPE_GLOBAL !== $node['scope'] ?? null) { // properties in Shopware are not translatable
            return $dto;
        }

        $code = $node['code'];

        $translations = [];
        if (!empty($node['label'])) {
            $translations = array_merge_recursive($translations, $this->translationTransformer->transform($node['label'], 'name'));
        }
        if (!empty($node['hint'])) {
            $translations = array_merge_recursive($translations, $this->translationTransformer->transform($node['hint'], 'description'));
        }

        $propertyGroup = $dto->getSwPropertyGroup();

        $options = [];
        if (!empty($node['options'])) {
            foreach ($node['options'] as $option) {
                if (!empty($option['code'])) {
                    $existingOption = $propertyGroup ? $this->getOptionByCode($propertyGroup, $option['code']) : null;

                    $options[] = [
                        'id' => $existingOption ? $existingOption->getId() : null,
                        'name' => $option['code'],
                        'translations' => $this->translationTransformer->transform($option['label'], 'name'),
                        'extensions' => [
                            AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                'id' => $existingOption ? $this->getEntityExtensionId($existingOption) : null,
                                'code' => PropertyGroupOptionUtil::buildOptionCode($code, $option['code']),
                                'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                            ],
                        ],
                    ];
                }
            }
        }

        $dto->setPropertyGroupPayload([
            'id' => $propertyGroup ? $propertyGroup->getId() : null,
            'displayType' => PropertyGroupDefinition::DISPLAY_TYPE_TEXT,
            'sortingType' => PropertyGroupDefinition::SORTING_TYPE_ALPHANUMERIC,
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
                $groupExtension instanceof ErgonodeMappingExtensionEntity &&
                PropertyGroupOptionUtil::buildOptionCode($groupExtension->getCode(), $code) === $extension->getCode()
            ) {
                return $option;
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