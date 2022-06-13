<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Strix\Ergonode\Resolver\NodeTransformerResolver;
use Strix\Ergonode\Struct\ErgonodeEntityStreamCollection;

/**
 * @deprecated ?
 */
class StreamResponseTransformer
{
    private NodeTransformerResolver $nodeTransformerResolver;

    public function __construct(NodeTransformerResolver $nodeTransformerResolver)
    {
        $this->nodeTransformerResolver = $nodeTransformerResolver;
    }

    public function transformResponse(string $class, array $response): ErgonodeEntityStreamCollection
    {
        $streamCollection = new ErgonodeEntityStreamCollection();
        $streamCollection->setExpectedClass($class);
        $streamCollection->setTotalCount($response['totalCount'] ?? null);
        $streamCollection->setHasNextPage($response['pageInfo']['hasNextPage'] ?? false);
        $streamCollection->setEndCursor($response['pageInfo']['endCursor'] ?? null);

        $nodeTransformer = $this->nodeTransformerResolver->resolve($class);
        if (null === $nodeTransformer) {
            return $streamCollection;
        }

        if (!isset($response['edges']) && isset($response['product'])) {
            $response['edges'][] = $response['product'];
        }

        foreach ($response['edges'] as $edge) {
            $node = isset($edge['node']) ?
                (is_array($edge['node']) ?
                    $edge['node'] :
                    ['__value__' => $edge['node']])
                : $edge;
            $entity = $nodeTransformer->transformNode($node);
            if (null === $entity) {
                continue;
            }

            $entity->setCursor($edge['cursor'] ?? null);

            $streamCollection->set($entity->getCode(), $entity);
        }

        return $streamCollection;
    }
}