<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Unit\Manager;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Strix\Ergonode\Manager\OrphanEntitiesManager;
use Strix\Ergonode\Modules\Attribute\Api\AttributeDeletedStreamResultsProxy;
use Strix\Ergonode\Modules\Attribute\Provider\ErgonodeAttributeProvider;
use Strix\Ergonode\Persistor\ErgonodeCursorPersistor;
use Strix\Ergonode\Persistor\PropertyGroupPersistor;
use Strix\Ergonode\Provider\ErgonodeCursorProvider;
use Strix\Ergonode\Tests\Fixture\GqlAttributeResponse;
use Strix\Ergonode\Tests\Util\DataConverter;

class OrphanEntitiesManagerTest extends TestCase
{
    private OrphanEntitiesManager $manager;

    /**
     * @var MockObject|ErgonodeCursorProvider
     */
    private ErgonodeCursorProvider $ergonodeCursorProviderMock;

    /**
     * @var MockObject|ErgonodeCursorPersistor
     */
    private ErgonodeCursorPersistor $ergonodeCursorPersistorMock;

    /**
     * @var MockObject|ErgonodeAttributeProvider
     */
    private ErgonodeAttributeProvider $ergonodeAttributeProviderMock;

    /**
     * @var MockObject|PropertyGroupPersistor
     */
    private PropertyGroupPersistor $propertyGroupPersistorMock;

    /**
     * @var MockObject|Context
     */
    private Context $contextMock;

    protected function setUp(): void
    {
        $this->ergonodeCursorProviderMock = $this->createMock(ErgonodeCursorProvider::class);
        $this->ergonodeCursorPersistorMock = $this->createMock(ErgonodeCursorPersistor::class);
        $this->ergonodeAttributeProviderMock = $this->createMock(ErgonodeAttributeProvider::class);
        $this->propertyGroupPersistorMock = $this->createMock(PropertyGroupPersistor::class);

        $this->contextMock = $this->createMock(Context::class);

        $this->manager = new OrphanEntitiesManager(
            $this->ergonodeCursorProviderMock,
            $this->ergonodeCursorPersistorMock,
            $this->ergonodeAttributeProviderMock,
            $this->propertyGroupPersistorMock
        );
    }

    /**
     * @dataProvider cleanPropertyGroupsDataProvider
     */
    public function testCleanPropertyGroupsMethod(
        array $results,
        string $cursorToPersist,
        bool $mockDeletedPropertyGroups,
        array $expectedOutput
    ) {
        $this->mockErgonodeCursorProvider(AttributeDeletedStreamResultsProxy::MAIN_FIELD, 'last_ergonode_cursor');
        $this->mockErgonodeAttributeProvider(
            'last_ergonode_cursor',
            $results
        );
        $this->mockPropertyGroupPersistor($results, $mockDeletedPropertyGroups);
        $this->mockErgonodeCursorPersistor($cursorToPersist, AttributeDeletedStreamResultsProxy::MAIN_FIELD);

        $output = $this->manager->cleanPropertyGroups($this->contextMock);

        $this->assertSame($expectedOutput, $output);
    }

    public function cleanPropertyGroupsDataProvider(): array
    {
        return [
            '1 page' => [
                [
                    $this->createConfiguredMock(AttributeDeletedStreamResultsProxy::class, [
                        'getEdges' => GqlAttributeResponse::attributeDeletedStreamResponse()['data']['attributeDeletedStream']['edges'],
                        'hasNextPage' => false,
                        'getEndCursor' => 'new_ergonode_cursor',
                        'hasEndCursor' => true,
                    ]),
                ],
                'new_ergonode_cursor',
                true,
                [
                    PropertyGroupDefinition::ENTITY_NAME => ['some_id_0'],
                    PropertyGroupOptionDefinition::ENTITY_NAME => ['some_id_0'],
                ],
            ],
            '2 pages' => [
                [
                    $this->createConfiguredMock(AttributeDeletedStreamResultsProxy::class, [
                        'getEdges' => GqlAttributeResponse::attributeDeletedStreamResponse()['data']['attributeDeletedStream']['edges'],
                        'hasNextPage' => true,
                        'getEndCursor' => 'new_ergonode_cursor',
                        'hasEndCursor' => true,
                    ]),
                    $this->createConfiguredMock(AttributeDeletedStreamResultsProxy::class, [
                        'getEdges' => GqlAttributeResponse::attributeDeletedStreamResponse()['data']['attributeDeletedStream']['edges'],
                        'hasNextPage' => false,
                        'getEndCursor' => 'new_ergonode_cursor2',
                        'hasEndCursor' => true,
                    ]),
                ],
                'new_ergonode_cursor2',
                true,
                [
                    PropertyGroupDefinition::ENTITY_NAME => ['some_id_0', 'some_id_1'],
                    PropertyGroupOptionDefinition::ENTITY_NAME => ['some_id_0', 'some_id_1'],
                ],
            ],
            'no deleted property groups' => [
                [
                    $this->createConfiguredMock(AttributeDeletedStreamResultsProxy::class, [
                        'getEdges' => GqlAttributeResponse::attributeDeletedStreamResponse()['data']['attributeDeletedStream']['edges'],
                        'hasNextPage' => false,
                        'getEndCursor' => 'new_ergonode_cursor',
                        'hasEndCursor' => true,
                    ]),
                ],
                'new_ergonode_cursor',
                false,
                [],
            ],
        ];
    }

    private function mockErgonodeCursorProvider(string $argument = 'cursor', string $returnValue = 'some_ergonode_cursor')
    {
        $this->ergonodeCursorProviderMock
            ->expects($this->once())
            ->method('get')
            ->with($argument, $this->contextMock)
            ->willReturn($returnValue);
    }

    private function mockErgonodeAttributeProvider(?string $endCursor = null, array $returnValues = [])
    {
        $this->ergonodeAttributeProviderMock
            ->expects($this->once())
            ->method('provideDeletedBindingAttributes')
            ->with($endCursor)
            ->willReturn(DataConverter::arrayAsGenerator($returnValues));
    }

    private function mockPropertyGroupPersistor(array $results, bool $mockDeletedPropertyGroups = true)
    {
        $returns = [];

        foreach ($results as $key => $value) {
            $returns[] = $mockDeletedPropertyGroups ? [
                PropertyGroupDefinition::ENTITY_NAME => ["some_id_$key"],
                PropertyGroupOptionDefinition::ENTITY_NAME => ["some_id_$key"],
            ] : [];
        }

        $this->propertyGroupPersistorMock
            ->expects($this->exactly(count($results)))
            ->method('remove')
            ->withConsecutive(...array_map(fn($result) => [$result, $this->contextMock], $results))
            ->willReturnOnConsecutiveCalls(...$returns);
    }

    private function mockErgonodeCursorPersistor(string $cursor, string $query)
    {
        $this->ergonodeCursorPersistorMock
            ->expects($this->once())
            ->method('save')
            ->with($cursor, $query, $this->contextMock)
            ->willReturn($this->createMock(EntityWrittenContainerEvent::class));
    }
}
