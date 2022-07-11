<?php

declare(strict_types=1);

namespace Strix\Ergonode\Processor;

use Shopware\Core\Framework\Context;
use Strix\Ergonode\Enum\AttributeTypesEnum;
use Strix\Ergonode\Api\AttributeStreamResultsProxy;
use Strix\Ergonode\Provider\ErgonodeAttributeProvider;
use Strix\Ergonode\Persistor\CustomFieldPersistor;
use Strix\Ergonode\Persistor\PropertyGroupPersistor;
use Strix\Ergonode\Provider\ConfigProvider;

class AttributeSyncProcessor
{
    private ErgonodeAttributeProvider $ergonodeAttributeProvider;

    private PropertyGroupPersistor $propertyGroupPersistor;

    private CustomFieldPersistor $customFieldManager;

    private ConfigProvider $configProvider;

    public function __construct(
        ErgonodeAttributeProvider $ergonodeAttributeProvider,
        PropertyGroupPersistor $propertyGroupPersistor,
        CustomFieldPersistor $customFieldManager,
        ConfigProvider $configProvider
    ) {
        $this->ergonodeAttributeProvider = $ergonodeAttributeProvider;
        $this->propertyGroupPersistor = $propertyGroupPersistor;
        $this->customFieldManager = $customFieldManager;
        $this->configProvider = $configProvider;
    }

    public function process(Context $context): array
    {
        $generator = $this->ergonodeAttributeProvider->provideProductAttributes();
        $entities = [];

        foreach ($generator as $attributeStream) {
            $entities = array_merge_recursive(
                $entities,
                $this->persistBindingAttributes($attributeStream, $context),
                $this->persistCustomFields($attributeStream, $context)
            );
        }

        return $entities;
    }

    private function persistBindingAttributes(AttributeStreamResultsProxy $attributes, Context $context): array
    {
        $bindingAttributes = $attributes->filterByAttributeTypes([
            AttributeTypesEnum::SELECT,
            AttributeTypesEnum::MULTISELECT,
        ]);

        if ($bindingAttributes instanceof AttributeStreamResultsProxy) {
            return $this->propertyGroupPersistor->persistStream($bindingAttributes, $context);
        }

        return [];
    }

    private function persistCustomFields(AttributeStreamResultsProxy $attributes, Context $context): array
    {
        $customFields = $attributes->filterByCodes(
            $this->configProvider->getErgonodeCustomFieldKeys()
        );

        if ($customFields instanceof AttributeStreamResultsProxy) {
            return $this->customFieldManager->persistStream($customFields, $context);
        }

        return [];
    }
}