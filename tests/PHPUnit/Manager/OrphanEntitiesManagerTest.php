<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests\PHPUnit\Manager;

use Ergonode\IntegrationShopware\Api\AttributeDeletedStreamResultsProxy;
use Ergonode\IntegrationShopware\Entity\ErgonodeCursor\ErgonodeCursorEntity;
use Ergonode\IntegrationShopware\Manager\ErgonodeCursorManager;
use Ergonode\IntegrationShopware\Manager\OrphanEntitiesManager;
use Ergonode\IntegrationShopware\Persistor\CustomFieldPersistor;
use Ergonode\IntegrationShopware\Persistor\PropertyGroupPersistor;
use Ergonode\IntegrationShopware\Provider\ErgonodeAttributeProvider;
use Ergonode\IntegrationShopware\Tests\PHPUnit\Fixture\GqlAttributeResponse;
use Ergonode\IntegrationShopware\Tests\PHPUnit\Util\DataConverter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;

class OrphanEntitiesManagerTest extends TestCase
{
    private OrphanEntitiesManager $manager;

    /**
     * @var MockObject|ErgonodeCursorManager
     */
    private ErgonodeCursorManager $ergonodeCursorManagerMock;

    /**
     * @var MockObject|ErgonodeAttributeProvider
     */
    private ErgonodeAttributeProvider $ergonodeAttributeProviderMock;

    /**
     * @var MockObject|PropertyGroupPersistor
     */
    private PropertyGroupPersistor $propertyGroupPersistorMock;

    /**
     * @var MockObject|CustomFieldPersistor
     */
    private CustomFieldPersistor $customFieldPersistorMock;

    /**
     * @var MockObject|Context
     */
    private Context $contextMock;

    protected function setUp(): void
    {
        $this->ergonodeCursorManagerMock = $this->createMock(ErgonodeCursorManager::class);
        $this->ergonodeAttributeProviderMock = $this->createMock(ErgonodeAttributeProvider::class);
        $this->propertyGroupPersistorMock = $this->createMock(PropertyGroupPersistor::class);
        $this->customFieldPersistorMock = $this->createMock(CustomFieldPersistor::class);

        $this->contextMock = $this->createMock(Context::class);

        $this->manager = new OrphanEntitiesManager(
            $this->ergonodeCursorManagerMock,
            $this->ergonodeAttributeProviderMock,
            $this->propertyGroupPersistorMock,
            $this->customFieldPersistorMock
        );
    }

    /**
     * @dataProvider cleanPropertyGroupsDataProvider
     */
    public function testCleanPropertyGroupsMethod(
        array $results,
        string $cursorToPersist,
        bool $mockDeleted,
        array $expectedOutput
    ) {
        $this->mockErgonodeCursorProvider(AttributeDeletedStreamResultsProxy::MAIN_FIELD, 'last_ergonode_cursor');
        $this->mockErgonodeAttributeProvider(
            'last_ergonode_cursor',
            $results
        );
        $this->mockCustomFieldPersistor($results, $mockDeleted);
        $this->mockPropertyGroupPersistor($results, $mockDeleted);
        $this->mockErgonodeCursorPersistor($cursorToPersist, AttributeDeletedStreamResultsProxy::MAIN_FIELD);

        $output = $this->manager->cleanAttributes($this->contextMock);

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
                        'map' => ['some_id_0'],
                    ]),
                ],
                'new_ergonode_cursor',
                true,
                ['some_id_0', 'some_id_0'],
            ],
            '2 pages' => [
                [
                    $this->createConfiguredMock(AttributeDeletedStreamResultsProxy::class, [
                        'getEdges' => GqlAttributeResponse::attributeDeletedStreamResponse()['data']['attributeDeletedStream']['edges'],
                        'hasNextPage' => true,
                        'getEndCursor' => 'new_ergonode_cursor',
                        'hasEndCursor' => true,
                        'map' => ['some_id_0', 'some_id_1'],
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
                ['some_id_0', 'some_id_1', 'some_id_0', 'some_id_1'],
            ],
            'no deleted property groups' => [
                [
                    $this->createConfiguredMock(AttributeDeletedStreamResultsProxy::class, [
                        'getEdges' => GqlAttributeResponse::attributeDeletedStreamResponse()['data']['attributeDeletedStream']['edges'],
                        'hasNextPage' => false,
                        'getEndCursor' => 'new_ergonode_cursor',
                        'hasEndCursor' => true,
                        'map' => [],
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
        $this->ergonodeCursorManagerMock
            ->expects($this->once())
            ->method('getCursorEntity')
            ->with($argument, $this->contextMock)
            ->willReturn(
                $this->createConfiguredMock(ErgonodeCursorEntity::class, [
                    'getCursor' => $returnValue,
                ])
            );
    }

    private function mockErgonodeAttributeProvider(?string $endCursor = null, array $returnValues = [])
    {
        $this->ergonodeAttributeProviderMock
            ->expects($this->once())
            ->method('provideDeletedAttributes')
            ->with($endCursor)
            ->willReturn(DataConverter::arrayAsGenerator($returnValues));
    }

    private function mockCustomFieldPersistor(array $results, bool $mockDeleted): void
    {
        $returns = [];

        if ($mockDeleted) {
            foreach ($results as $key => $value) {
                $returns[] = "some_id_$key";
            }
        }

        $this->customFieldPersistorMock
            ->expects($this->once())
            ->method('removeByCodes')
            ->willReturn($returns);;
    }

    private function mockPropertyGroupPersistor(array $results, bool $mockDeleted)
    {
        $returns = [];

        if ($mockDeleted) {
            foreach ($results as $key => $value) {
                $returns[] = "some_id_$key";
            }
        }

        $this->propertyGroupPersistorMock
            ->expects($this->once())
            ->method('removeByCodes')
            ->willReturn($returns);
    }

    private function mockErgonodeCursorPersistor(string $cursor, string $query)
    {
        $this->ergonodeCursorManagerMock
            ->expects($this->once())
            ->method('persist')
            ->with($cursor, $query, $this->contextMock)
            ->willReturn($this->createMock(EntityWrittenContainerEvent::class));
    }
}
