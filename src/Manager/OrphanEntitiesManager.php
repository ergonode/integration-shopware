<?php

declare(strict_types=1);

namespace Strix\Ergonode\Manager;

use Shopware\Core\Framework\Context;
use Strix\Ergonode\Api\AttributeDeletedStreamResultsProxy;
use Strix\Ergonode\Provider\ErgonodeAttributeProvider;
use Strix\Ergonode\Persistor\PropertyGroupPersistor;

class OrphanEntitiesManager
{
    private ErgonodeCursorManager $ergonodeCursorManager;

    private ErgonodeAttributeProvider $ergonodeAttributeProvider;

    private PropertyGroupPersistor $propertyGroupPersistor;

    public function __construct(
        ErgonodeCursorManager $ergonodeCursorManager,
        ErgonodeAttributeProvider $ergonodeAttributeProvider,
        PropertyGroupPersistor $propertyGroupPersistor
    ) {
        $this->ergonodeCursorManager = $ergonodeCursorManager;
        $this->ergonodeAttributeProvider = $ergonodeAttributeProvider;
        $this->propertyGroupPersistor = $propertyGroupPersistor;
    }

    public function cleanPropertyGroups(Context $context): array
    {
        $entities = [];

        $lastCursor = $this->ergonodeCursorManager->getCursorEntity(AttributeDeletedStreamResultsProxy::MAIN_FIELD, $context);
        $lastCursor = null !== $lastCursor ? $lastCursor->getCursor() : null;
        $generator = $this->ergonodeAttributeProvider->provideDeletedAttributes($lastCursor);

        foreach ($generator as $deletedAttributes) {
            $entities = array_merge_recursive(
                $entities,
                $this->propertyGroupPersistor->remove($deletedAttributes, $context)
            );
        }

        if (isset($deletedAttributes) && $deletedAttributes->hasEndCursor()) {
            $this->ergonodeCursorManager->persist(
                $deletedAttributes->getEndCursor(),
                AttributeDeletedStreamResultsProxy::MAIN_FIELD,
                $context
            );
        }

        return $entities;
    }
}
