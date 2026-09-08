<?php

namespace JeffersonGoncalves\Postmark;

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Postmark\Exceptions\PostmarkException;

/**
 * Thin wrapper around Laravel's Http client for the Postmark REST API.
 *
 * ponytail: Http::retry() already covers transient-failure retries if ever
 * needed later — no custom retry/backoff layer built here speculatively.
 */
class PostmarkClient
{
    public function __construct(
        protected string $apiUrl,
        protected string $token,
    ) {}

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>
     */
    public function get(string $path, array $query = []): array
    {
        return $this->request('get', $path, array_filter($query, fn (mixed $value) => $value !== null));
    }

    /**
     * @param  array<mixed>|null  $body
     * @return array<mixed>
     */
    public function post(string $path, ?array $body = null): array
    {
        return $this->request('post', $path, $body);
    }

    /**
     * @param  array<mixed>|null  $body
     * @return array<mixed>
     */
    public function put(string $path, ?array $body = null): array
    {
        return $this->request('put', $path, $body);
    }

    /** @return array<mixed> */
    public function delete(string $path): array
    {
        return $this->request('delete', $path);
    }

    /**
     * @param  array<mixed>|null  $data
     * @return array<mixed>
     */
    protected function request(string $method, string $path, ?array $data = null): array
    {
        $response = Http::withHeaders([
            'X-Postmark-Server-Token' => $this->token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
            ->baseUrl(rtrim($this->apiUrl, '/'))
            ->{$method}($path, $data ?? []);

        if ($response->failed()) {
            throw PostmarkException::fromResponse($response);
        }

        return (array) ($response->json() ?? []);
    }
}
