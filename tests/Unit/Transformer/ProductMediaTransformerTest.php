<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests\Unit\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Manager\FileManager;
use Ergonode\IntegrationShopware\Provider\ProductMediaProvider;
use Ergonode\IntegrationShopware\Transformer\ProductMediaTransformer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\Aggregate\ProductMedia\ProductMediaCollection;
use Shopware\Core\Content\Product\Aggregate\ProductMedia\ProductMediaDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductMedia\ProductMediaEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;

class ProductMediaTransformerTest extends TestCase
{
    const SW_PRODUCT_ID = 'sw_product_id';

    private ProductMediaTransformer $transformer;

    /**
     * @var MockObject|Context
     */
    private Context $contextMock;

    /**
     * @var MockObject|FileManager
     */
    private FileManager $fileManagerMock;

    /**
     * @var MockObject|ProductMediaProvider
     */
    private ProductMediaProvider $productMediaProviderMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);

        $this->fileManagerMock = $this->createMock(FileManager::class);
        $this->productMediaProviderMock = $this->createMock(ProductMediaProvider::class);

        $this->transformer = new ProductMediaTransformer(
            $this->fileManagerMock,
            $this->productMediaProviderMock
        );
    }

    /**
     * @dataProvider validProductDataProvider
     */
    public function testTransformMethodUsingValidData(array $input, array $expectedOutput)
    {
        $this->mockSuccessfulFileManagerPersist($input);
        $this->mockSuccessfulProductMediaProviderGetProductMedia($input);

        $dto = new ProductTransformationDTO([], $input);
        $dto->setSwProduct($this->getSwProductMock());

        $output = $this->transformer->transform($dto, $this->contextMock);

        $this->assertSame($expectedOutput, $output->getShopwareData());
    }

    /**
     * @dataProvider invalidProductDataProvider
     */
    public function testTransformMethodUsingInvalidData(array $input, array $expectedOutput)
    {
        $dto = new ProductTransformationDTO([], $input);
        $dto->setSwProduct($this->getSwProductMock());

        $output = $this->transformer->transform($dto, $this->contextMock);

        $this->assertSame($expectedOutput, $output->getShopwareData());
    }

    /**
     * @dataProvider validProductDataProvider
     */
    public function testTransformMethodWhenMediaPersistenceFailed(array $input)
    {
        $this->mockFailedFileManagerPersist($input);

        $dto = new ProductTransformationDTO([], $input);
        $dto->setSwProduct($this->getSwProductMock());

        $output = $this->transformer->transform($dto, $this->contextMock);

        $this->assertArrayNotHasKey('media', $output->getShopwareData());
    }

    /**
     * @dataProvider toDeleteProductDataProvider
     */
    public function testIfOrphanMediaAreQueuedToBeUnlinkedFromProduct(
        array $input,
        array $existingProductMediaIds,
        array $expectedIdsToDelete
    ) {
        $this->mockSuccessfulFileManagerPersist($input);
        $this->mockSuccessfulProductMediaProviderGetProductMedia($input);

        $dto = new ProductTransformationDTO([], $input);
        $dto->setSwProduct($this->getSwProductMock([
            'getMedia' => $this->createConfiguredMock(ProductMediaCollection::class, [
                'getIds' => $existingProductMediaIds,
            ]),
        ]));

        $output = $this->transformer->transform($dto, $this->contextMock);

        $this->assertSame($expectedIdsToDelete, $output->getEntitiesToDelete());
    }

    /**
     * @dataProvider allToDeleteProductDataProvider
     */
    public function testIfAllMediaAreConsideredOrphanWhenNewMediaFieldIsEmpty(
        array $input,
        array $existingProductMediaIds,
        array $expectedIdsToDelete
    ) {
        $this->mockFailedFileManagerPersist($input);

        $dto = new ProductTransformationDTO([], $input);
        $dto->setSwProduct($this->getSwProductMock([
            'getMedia' => $this->createConfiguredMock(ProductMediaCollection::class, [
                'getIds' => $existingProductMediaIds,
            ]),
        ]));

        $output = $this->transformer->transform($dto, $this->contextMock);

        $this->assertSame($expectedIdsToDelete, $output->getEntitiesToDelete());
    }

    public function validProductDataProvider(): array
    {
        return [
            [
                [
                    'media' => [
                        [
                            'name' => 'image_1.jpg',
                            'extension' => 'jpg',
                            'mime' => 'image/jpeg',
                            'size' => 12345,
                            'url' => 'https://example.com/api/multimedia/file/d230f52c-1cc8-4d93-b9ab-8735f7d54220.jpg',
                        ],
                    ],
                ],
                [
                    'media' => [
                        [
                            'id' => 'product_media/media/image_1.jpg',
                            'mediaId' => 'media/image_1.jpg',
                        ],
                    ],
                    'cover' => [
                        'id' => 'product_media/media/image_1.jpg',
                        'mediaId' => 'media/image_1.jpg',
                    ],
                ],
            ],
            [
                [
                    'media' => [
                        [
                            'name' => 'image_1.jpg',
                            'extension' => 'jpg',
                            'mime' => 'image/jpeg',
                            'size' => 12345,
                            'url' => 'https://example.com/api/multimedia/file/d230f52c-1cc8-4d93-b9ab-8735f7d54220.jpg',
                        ],
                        [
                            'name' => 'image_2.jpg',
                            'extension' => 'jpg',
                            'mime' => 'image/jpeg',
                            'size' => 23456,
                            'url' => 'https://example.com/api/multimedia/file/d16de9a2-5647-46f3-84df-5c5af1fa6d6d.jpg',
                        ],
                    ],
                ],
                [
                    'media' => [
                        [
                            'id' => 'product_media/media/image_1.jpg',
                            'mediaId' => 'media/image_1.jpg',
                        ],
                        [
                            'id' => 'product_media/media/image_2.jpg',
                            'mediaId' => 'media/image_2.jpg',
                        ],
                    ],
                    'cover' => [
                        'id' => 'product_media/media/image_1.jpg',
                        'mediaId' => 'media/image_1.jpg',
                    ],
                ],
            ],
            [
                [
                    'media' => [
                        [
                            'name' => 'image_2.jpg',
                            'extension' => 'jpg',
                            'mime' => 'image/jpeg',
                            'size' => 23456,
                            'url' => 'https://example.com/api/multimedia/file/d16de9a2-5647-46f3-84df-5c5af1fa6d6d.jpg',
                        ],
                        [
                            'name' => 'image_1.jpg',
                            'extension' => 'jpg',
                            'mime' => 'image/jpeg',
                            'size' => 12345,
                            'url' => 'https://example.com/api/multimedia/file/d230f52c-1cc8-4d93-b9ab-8735f7d54220.jpg',
                        ],
                    ],
                ],
                [
                    'media' => [
                        [
                            'id' => 'product_media/media/image_2.jpg',
                            'mediaId' => 'media/image_2.jpg',
                        ],
                        [
                            'id' => 'product_media/media/image_1.jpg',
                            'mediaId' => 'media/image_1.jpg',
                        ],
                    ],
                    'cover' => [
                        'id' => 'product_media/media/image_2.jpg',
                        'mediaId' => 'media/image_2.jpg',
                    ],
                ],
            ],
        ];
    }

    public function invalidProductDataProvider(): array
    {
        return [
            [
                [
                    'media' => 'not_array',
                ],
                [],
            ],
            [
                [],
                [],
            ],
            [
                [
                    'media' => [],
                ],
                [],
            ],
        ];
    }

    public function toDeleteProductDataProvider(): array
    {
        return [
            [
                [
                    'media' => [
                        [
                            'name' => 'image_1.jpg',
                            'extension' => 'jpg',
                            'mime' => 'image/jpeg',
                            'size' => 12345,
                            'url' => 'https://example.com/api/multimedia/file/d230f52c-1cc8-4d93-b9ab-8735f7d54220.jpg',
                        ],
                        [
                            'name' => 'image_2.jpg',
                            'extension' => 'jpg',
                            'mime' => 'image/jpeg',
                            'size' => 23456,
                            'url' => 'https://example.com/api/multimedia/file/d16de9a2-5647-46f3-84df-5c5af1fa6d6d.jpg',
                        ],
                    ],
                ],
                [],
                [],
            ],
            [
                [
                    'media' => [
                        [
                            'name' => 'image_1.jpg',
                            'extension' => 'jpg',
                            'mime' => 'image/jpeg',
                            'size' => 12345,
                            'url' => 'https://example.com/api/multimedia/file/d230f52c-1cc8-4d93-b9ab-8735f7d54220.jpg',
                        ],
                        [
                            'name' => 'image_2.jpg',
                            'extension' => 'jpg',
                            'mime' => 'image/jpeg',
                            'size' => 23456,
                            'url' => 'https://example.com/api/multimedia/file/d16de9a2-5647-46f3-84df-5c5af1fa6d6d.jpg',
                        ],
                    ],
                ],
                [
                    'not_linked_id_1',
                    'not_linked_id_2',
                    'not_linked_id_3',
                ],
                [
                    ProductMediaDefinition::ENTITY_NAME => [
                        ['id' => 'not_linked_id_1'],
                        ['id' => 'not_linked_id_2'],
                        ['id' => 'not_linked_id_3'],
                    ],
                ],
            ],
            [
                [
                    'media' => [
                        [
                            'name' => 'image_1.jpg',
                            'extension' => 'jpg',
                            'mime' => 'image/jpeg',
                            'size' => 12345,
                            'url' => 'https://example.com/api/multimedia/file/d230f52c-1cc8-4d93-b9ab-8735f7d54220.jpg',
                        ],
                        [
                            'name' => 'image_2.jpg',
                            'extension' => 'jpg',
                            'mime' => 'image/jpeg',
                            'size' => 23456,
                            'url' => 'https://example.com/api/multimedia/file/d16de9a2-5647-46f3-84df-5c5af1fa6d6d.jpg',
                        ],
                    ],
                ],
                [
                    'product_media/media/image_1.jpg',
                    'not_linked_id_2',
                    'not_linked_id_3',
                ],
                [
                    ProductMediaDefinition::ENTITY_NAME => [
                        ['id' => 'not_linked_id_2'],
                        ['id' => 'not_linked_id_3'],
                    ],
                ],
            ],
        ];
    }

    public function allToDeleteProductDataProvider(): array
    {
        return [
            [
                [
                    'media' => [
                        [
                            'name' => 'image_1.jpg',
                            'extension' => 'jpg',
                            'mime' => 'image/jpeg',
                            'size' => 12345,
                            'url' => 'https://example.com/api/multimedia/file/d230f52c-1cc8-4d93-b9ab-8735f7d54220.jpg',
                        ],
                        [
                            'name' => 'image_2.jpg',
                            'extension' => 'jpg',
                            'mime' => 'image/jpeg',
                            'size' => 23456,
                            'url' => 'https://example.com/api/multimedia/file/d16de9a2-5647-46f3-84df-5c5af1fa6d6d.jpg',
                        ],
                    ],
                ],
                [],
                [],
            ],
            [
                [
                    'media' => [
                        [
                            'name' => 'image_1.jpg',
                            'extension' => 'jpg',
                            'mime' => 'image/jpeg',
                            'size' => 12345,
                            'url' => 'https://example.com/api/multimedia/file/d230f52c-1cc8-4d93-b9ab-8735f7d54220.jpg',
                        ],
                        [
                            'name' => 'image_2.jpg',
                            'extension' => 'jpg',
                            'mime' => 'image/jpeg',
                            'size' => 23456,
                            'url' => 'https://example.com/api/multimedia/file/d16de9a2-5647-46f3-84df-5c5af1fa6d6d.jpg',
                        ],
                    ],
                ],
                [
                    'not_linked_id_1',
                    'not_linked_id_2',
                    'not_linked_id_3',
                ],
                [
                    ProductMediaDefinition::ENTITY_NAME => [
                        ['id' => 'not_linked_id_1'],
                        ['id' => 'not_linked_id_2'],
                        ['id' => 'not_linked_id_3'],
                    ],
                ],
            ],
            [
                [
                    'media' => [
                        [
                            'name' => 'image_1.jpg',
                            'extension' => 'jpg',
                            'mime' => 'image/jpeg',
                            'size' => 12345,
                            'url' => 'https://example.com/api/multimedia/file/d230f52c-1cc8-4d93-b9ab-8735f7d54220.jpg',
                        ],
                        [
                            'name' => 'image_2.jpg',
                            'extension' => 'jpg',
                            'mime' => 'image/jpeg',
                            'size' => 23456,
                            'url' => 'https://example.com/api/multimedia/file/d16de9a2-5647-46f3-84df-5c5af1fa6d6d.jpg',
                        ],
                    ],
                ],
                [
                    'product_media/media/image_1.jpg',
                    'product_media/media/image_2.jpg',
                    'not_linked_id_3',
                ],
                [
                    ProductMediaDefinition::ENTITY_NAME => [
                        ['id' => 'product_media/media/image_1.jpg'],
                        ['id' => 'product_media/media/image_2.jpg'],
                        ['id' => 'not_linked_id_3'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return ProductEntity|MockObject
     */
    private function getSwProductMock(array $configuration = []): ProductEntity
    {
        return $this->createConfiguredMock(ProductEntity::class,
            array_merge([
                'getId' => self::SW_PRODUCT_ID,
            ], $configuration)
        );
    }

    private function mockSuccessfulFileManagerPersist(array $input): void
    {
        $this->fileManagerMock
            ->expects($this->exactly(count($input['media'])))
            ->method('persist')
            ->withConsecutive(...array_map(fn(array $media) => [$media, $this->contextMock], $input['media']))
            ->willReturnCallback(function (array $image) {
                return sprintf('%s/%s', 'media', $image['name']);
            });
    }

    private function mockFailedFileManagerPersist(array $input): void
    {
        $this->fileManagerMock
            ->expects($this->exactly(count($input['media'])))
            ->method('persist')
            ->withConsecutive(...array_map(fn(array $media) => [$media, $this->contextMock], $input['media']))
            ->willReturn(null);
    }

    private function mockSuccessfulProductMediaProviderGetProductMedia(array $input): void
    {
        $this->productMediaProviderMock
            ->expects($this->exactly(count($input['media'])))
            ->method('getProductMedia')
            ->withConsecutive(
                ...array_map(fn(array $image) => [
                    sprintf('%s/%s', 'media', $image['name']),
                    self::SW_PRODUCT_ID,
                    $this->contextMock,
                ], $input['media'])
            )
            ->willReturnCallback(function (string $name) {
                return $this->createConfiguredMock(ProductMediaEntity::class, [
                    'getId' => sprintf('%s/%s', 'product_media', $name),
                ]);
            });
    }
}
