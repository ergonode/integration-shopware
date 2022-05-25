<?php

declare(strict_types=1);

namespace Strix\Ergonode\Struct;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class GqlResponse
{
    private ResponseInterface $httpResponse;

    private array $body = [];

    public function __construct(ResponseInterface $response)
    {
        $this->httpResponse = $response;

        $this->parseResponseBody();
    }

    /**
     * @return ResponseInterface
     */
    public function getHttpResponse(): ResponseInterface
    {
        return $this->httpResponse;
    }

    public function isOk(): bool
    {
        return Response::HTTP_OK === $this->httpResponse->getStatusCode();
    }

    public function getData(): array
    {
        return $this->body['data'] ?? [];
    }

    private function parseResponseBody(): void
    {
        $contents = $this->httpResponse->getBody()->getContents();

        if (!empty($contents)) {
            $this->body = json_decode($contents, true);
        }
    }
}