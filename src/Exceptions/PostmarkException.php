<?php

namespace JeffersonGoncalves\Postmark\Exceptions;

use Illuminate\Http\Client\Response;
use RuntimeException;

class PostmarkException extends RuntimeException
{
    /** @var array<string, mixed> */
    protected array $errorBody = [];

    public static function fromResponse(Response $response): self
    {
        $body = (array) ($response->json() ?? []);

        $message = $body['Message'] ?? "Postmark API error (HTTP {$response->status()}).";

        // Postmark answers 422 with its own ErrorCode; keep it as the exception
        // code so callers can branch on it, falling back to the HTTP status.
        $exception = new self($message, (int) ($body['ErrorCode'] ?? $response->status()));
        $exception->errorBody = $body;

        return $exception;
    }

    /** @return array<string, mixed> */
    public function errorBody(): array
    {
        return $this->errorBody;
    }
}
