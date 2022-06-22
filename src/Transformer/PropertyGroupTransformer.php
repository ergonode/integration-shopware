<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Strix\Ergonode\Entity\ErgonodeMappingExtension\ErgonodeMappingExtensionEntity;
use Strix\Ergonode\Extension\AbstractErgonodeMappingExtension;
use Strix\Ergonode\Extension\PropertyGroup\PropertyGroupExtension;
use Strix\Ergonode\Extension\PropertyGroupOption\PropertyGroupOptionExtension;
use Strix\Ergonode\Provider\PropertyGroupProvider;

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

    public function transformAttributeNode(array $node, Context $context): array
    {
        $code = $node['code'];

        $translations = [];
        if (!empty($node['label'])) {
            $translations = array_merge_recursive($translations, $this->translationTransformer->transform($node['label'], 'name'));
        }
        if (!empty($node['hint'])) {
            $translations = array_merge_recursive($translations, $this->translationTransformer->transform($node['hint'], 'description'));
        }

        $propertyGroup = $this->propertyGroupProvider->getPropertyGroupByMapping($code, $context);

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
                                'code' => $this->buildOptionCode($code, $option['code']),
                                'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                            ],
                        ],
                    ];
                }
            }
        }

        return [
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
        ];
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
                $this->buildOptionCode($groupExtension->getCode(), $code) === $extension->getCode()
            ) {
                return $option;
            }
        }

        return null;
    }

    private function buildOptionCode(string $prefix, string $suffix): string
    {
        return sprintf('%s_%s', $prefix, $suffix);
    }
}