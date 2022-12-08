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
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class PropertyGroupTransformer
{
    private TranslationTransformer $translationTransformer;

    public function __construct(
        TranslationTransformer $translationTransformer
    ) {
        $this->translationTransformer = $translationTransformer;
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
        if (!empty($node['options'])) {
            foreach ($node['options'] as $option) {
                if (!empty($option['code'])) {
                    $existingOption = $propertyGroup ? $this->getOptionByCode($propertyGroup, $option['code']) : null;

                    $options[] = [
                        'id' => $existingOption ? $existingOption->getId() : null,
                        'name' => $option['code'],
                        'translations' => $this->translationTransformer->transform($option['name'], 'name'),
                        'extensions' => [
                            AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                                'id' => $existingOption ? $this->getEntityExtensionId($existingOption) : null,
                                'code' => CodeBuilderUtil::build($code, $option['code']),
                                'type' => PropertyGroupOptionExtension::ERGONODE_TYPE,
                            ],
                        ],
                    ];
                }
            }
        }

        $dto->setPropertyGroupPayload([
            'id' => $propertyGroup ? $propertyGroup->getId() : null,
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
                CodeBuilderUtil::build($groupExtension->getCode(), $code) === $extension->getCode()
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
