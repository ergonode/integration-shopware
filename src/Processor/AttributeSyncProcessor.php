<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor;

use Ergonode\IntegrationShopware\Api\AttributeStreamResultsProxy;
use Ergonode\IntegrationShopware\DTO\SyncCounterDTO;
use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Ergonode\IntegrationShopware\Manager\ErgonodeCursorManager;
use Ergonode\IntegrationShopware\Persistor\CustomFieldPersistor;
use Ergonode\IntegrationShopware\Persistor\PropertyGroupPersistor;
use Ergonode\IntegrationShopware\Provider\ErgonodeAttributeProvider;
use Ergonode\IntegrationShopware\Service\ConfigService;
use Shopware\Core\Framework\Context;

use function count;

class AttributeSyncProcessor
{
    private ErgonodeAttributeProvider $ergonodeAttributeProvider;

    private PropertyGroupPersistor $propertyGroupPersistor;

    private CustomFieldPersistor $customFieldManager;

    private ConfigService $configService;

    private ErgonodeCursorManager $cursorManager;

    public function __construct(
        ErgonodeAttributeProvider $ergonodeAttributeProvider,
        PropertyGroupPersistor $propertyGroupPersistor,
        CustomFieldPersistor $customFieldManager,
        ConfigService $configService,
        ErgonodeCursorManager $cursorManager
    ) {
        $this->ergonodeAttributeProvider = $ergonodeAttributeProvider;
        $this->propertyGroupPersistor = $propertyGroupPersistor;
        $this->customFieldManager = $customFieldManager;
        $this->configService = $configService;
        $this->cursorManager = $cursorManager;
    }

    public function process(Context $context): SyncCounterDTO
    {
        $cursorEntity = $this->cursorManager->getCursorEntity(AttributeStreamResultsProxy::MAIN_FIELD, $context);
        $cursor = $cursorEntity?->getCursor();

        $generator = $this->ergonodeAttributeProvider->provideProductAttributes($cursor);

        $counter = new SyncCounterDTO();

        foreach ($generator as $attributeStream) {
            $counter->incrProcessedEntityCount(
                $this->persistSelectAttributes($attributeStream, $context)
            );
            $counter->incrProcessedEntityCount(
                $this->persistCustomFields($attributeStream, $context)
            );

            $endCursor = $attributeStream->getEndCursor();
            if (null === $endCursor) {
                throw new \RuntimeException('Could not retrieve end cursor from the response.');
            }

            $this->cursorManager->persist($endCursor, AttributeStreamResultsProxy::MAIN_FIELD, $context);
        }

        return $counter;
    }

    private function persistSelectAttributes(AttributeStreamResultsProxy $attributes, Context $context): int
    {
        $selectAttributes = $attributes->filterByAttributeTypes([
            AttributeTypesEnum::SELECT,
            AttributeTypesEnum::MULTISELECT,
        ]);

        if ($selectAttributes instanceof AttributeStreamResultsProxy) {
            return count($this->propertyGroupPersistor->persistStream($selectAttributes, $context));
        }

        return 0;
    }

    private function persistCustomFields(AttributeStreamResultsProxy $attributes, Context $context): int
    {
        $customFields = $attributes->filterByCodes(
            $this->configService->getErgonodeCustomFieldKeys()
        );

        if ($customFields instanceof AttributeStreamResultsProxy) {
            return count($this->customFieldManager->persistStream($customFields, $context));
        }

        return 0;
    }
}
