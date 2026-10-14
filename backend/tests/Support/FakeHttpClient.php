<?php

namespace Tests\Support;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/** PSR-18 client for the Anthropic SDK in tests: records requests, returns canned responses. */
class FakeHttpClient implements ClientInterface
{
    /** @var list<RequestInterface> */
    public array $requests = [];

    /** @param list<ResponseInterface> $responses */
    public function __construct(private array $responses) {}

    public static function json(array $body, int $status = 200): ResponseInterface
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($body, JSON_UNESCAPED_UNICODE));
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;

        return array_shift($this->responses) ?? self::json(['type' => 'error', 'error' => ['type' => 'api_error', 'message' => 'no more fake responses']], 500);
    }

    /** @return array<string, mixed> */
    public function lastBody(): array
    {
        return json_decode((string) end($this->requests)->getBody(), true);
    }
}
