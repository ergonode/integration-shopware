<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor;

use Ergonode\IntegrationShopware\Api\AttributeStreamResultsProxy;
use Ergonode\IntegrationShopware\DTO\SyncCounterDTO;
use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Ergonode\IntegrationShopware\Persistor\CustomFieldPersistor;
use Ergonode\IntegrationShopware\Persistor\PropertyGroupPersistor;
use Ergonode\IntegrationShopware\Provider\ConfigProvider;
use Ergonode\IntegrationShopware\Provider\ErgonodeAttributeProvider;
use Shopware\Core\Framework\Context;

use function count;

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

    public function process(Context $context): SyncCounterDTO
    {
        $generator = $this->ergonodeAttributeProvider->provideProductAttributes();
        $counter = new SyncCounterDTO();

        foreach ($generator as $attributeStream) {
            $counter->incrProcessedEntityCount(
                $this->persistBindingAttributes($attributeStream, $context)
            );
            $counter->incrProcessedEntityCount(
                $this->persistCustomFields($attributeStream, $context)
            );
        }

        return $counter;
    }

    private function persistBindingAttributes(AttributeStreamResultsProxy $attributes, Context $context): int
    {
        $bindingAttributes = $attributes->filterByAttributeTypes([
            AttributeTypesEnum::SELECT,
            AttributeTypesEnum::MULTISELECT,
        ]);

        if ($bindingAttributes instanceof AttributeStreamResultsProxy) {
            return count($this->propertyGroupPersistor->persistStream($bindingAttributes, $context));
        }

        return 0;
    }

    private function persistCustomFields(AttributeStreamResultsProxy $attributes, Context $context): int
    {
        $customFields = $attributes->filterByCodes(
            $this->configProvider->getErgonodeCustomFieldKeys()
        );

        if ($customFields instanceof AttributeStreamResultsProxy) {
            return count($this->customFieldManager->persistStream($customFields, $context));
        }

        return 0;
    }
}