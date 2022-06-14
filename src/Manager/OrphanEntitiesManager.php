<?php

declare(strict_types=1);

namespace Strix\Ergonode\Manager;

use Shopware\Core\Framework\Context;
use Strix\Ergonode\Modules\Attribute\Api\AttributeDeletedStreamResultsProxy;
use Strix\Ergonode\Modules\Attribute\Provider\ErgonodeAttributeProvider;
use Strix\Ergonode\Persistor\ErgonodeCursorPersistor;
use Strix\Ergonode\Persistor\PropertyGroupPersistor;
use Strix\Ergonode\Provider\ErgonodeCursorProvider;

class OrphanEntitiesManager
{
    private ErgonodeCursorProvider $ergonodeCursorProvider;

    private ErgonodeCursorPersistor $ergonodeCursorPersistor;

    private ErgonodeAttributeProvider $ergonodeAttributeProvider;

    private PropertyGroupPersistor $propertyGroupPersistor;

    public function __construct(
        ErgonodeCursorProvider $ergonodeCursorProvider,
        ErgonodeCursorPersistor $ergonodeCursorPersistor,
        ErgonodeAttributeProvider $ergonodeAttributeProvider,
        PropertyGroupPersistor $propertyGroupPersistor
    ) {
        $this->ergonodeCursorProvider = $ergonodeCursorProvider;
        $this->ergonodeCursorPersistor = $ergonodeCursorPersistor;
        $this->ergonodeAttributeProvider = $ergonodeAttributeProvider;
        $this->propertyGroupPersistor = $propertyGroupPersistor;
    }

    public function cleanPropertyGroups(Context $context): array
    {
        $entities = [];

        $lastCursor = $this->ergonodeCursorProvider->get(AttributeDeletedStreamResultsProxy::MAIN_FIELD, $context);
        $generator = $this->ergonodeAttributeProvider->provideDeletedBindingAttributes($lastCursor);

        foreach ($generator as $deletedAttributes) {
            $entities = array_merge_recursive(
                $entities,
                $this->propertyGroupPersistor->remove($deletedAttributes, $context)
            );
        }

        if (isset($deletedAttributes) && $deletedAttributes->hasEndCursor()) {
            $this->ergonodeCursorPersistor->save(
                $deletedAttributes->getEndCursor(),
                AttributeDeletedStreamResultsProxy::MAIN_FIELD,
                $context
            );
        }

        return $entities;
    }
}
