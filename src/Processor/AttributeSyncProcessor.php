<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor;

use Ergonode\IntegrationShopware\Api\AttributeStreamResultsProxy;
use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Ergonode\IntegrationShopware\Persistor\CustomFieldPersistor;
use Ergonode\IntegrationShopware\Persistor\PropertyGroupPersistor;
use Ergonode\IntegrationShopware\Provider\ConfigProvider;
use Ergonode\IntegrationShopware\Provider\ErgonodeAttributeProvider;
use Shopware\Core\Framework\Context;

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