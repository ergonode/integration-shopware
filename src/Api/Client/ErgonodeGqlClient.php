<?php

declare(strict_types=1);

namespace Strix\Ergonode\Api\Client;

use GraphQL\Query;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;

class ErgonodeGqlClient
{
    private const GRAPHQL_ENDPOINT = 'api/graphql/';

    private Client $httpClient;

    public function __construct(
        Client $httpClient
    ) {
        $this->httpClient = $httpClient;
    }

    public function query(Query $query): ?ResponseInterface
    {
        try {
            return $this->httpClient->request(
                Request::METHOD_GET,
                self::GRAPHQL_ENDPOINT,
                $this->buildRequestBody($query)
            );
        } catch (GuzzleException $e) {
            // TODO log
            dump($e);
        }

        return null;
    }

    private function buildRequestBody(Query $query): array
    {
        return [
            'json' => [
                'query' => strval($query),
            ],
        ];
    }
}