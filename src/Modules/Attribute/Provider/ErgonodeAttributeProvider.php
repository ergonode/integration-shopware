<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Attribute\Provider;

use Strix\Ergonode\Api\Client\ErgonodeGqlClient;
use Strix\Ergonode\Modules\Attribute\QueryBuilder\AttributeQueryBuilder;

class ErgonodeAttributeProvider
{
    private const MAX_ATTRIBUTES_PER_PAGE = 200;

    private AttributeQueryBuilder $attributeQueryBuilder;

    private ErgonodeGqlClient $ergonodeGqlClient;

    public function __construct(
        AttributeQueryBuilder $attributeQueryBuilder,
        ErgonodeGqlClient $ergonodeGqlClient
    ) {
        $this->attributeQueryBuilder = $attributeQueryBuilder;
        $this->ergonodeGqlClient = $ergonodeGqlClient;
    }

    public function provideProductAttributes(): array
    {
        $endCursor = null;
        $attributes = [];

        do {
            $query = $this->attributeQueryBuilder->build(self::MAX_ATTRIBUTES_PER_PAGE, $endCursor);
            $response = $this->ergonodeGqlClient->query($query);

            if (false === $response->isOk()) {
                continue;
            }

            $data = $response->getData();
            $attributes = array_merge($attributes, $data['attributeStream']['edges']);
            $endCursor = $data['attributeStream']['pageInfo']['endCursor'];
        } while ($data['attributeStream']['pageInfo']['hasNextPage'] ?? false);

        return $attributes;
    }
}