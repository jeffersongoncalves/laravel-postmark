<?php

namespace JeffersonGoncalves\Postmark\Resources;

use InvalidArgumentException;
use JeffersonGoncalves\Postmark\PostmarkClient;

class Suppressions
{
    public function __construct(
        protected PostmarkClient $client,
        protected string $defaultStream = 'outbound',
    ) {}

    /** @return array<mixed> */
    public function list(?string $stream = null): array
    {
        return $this->client->get('/message-streams/'.($stream ?? $this->defaultStream).'/suppressions/dump');
    }

    /**
     * @param  array<int, string>|string  $emails
     * @return array<mixed>
     */
    public function create(array|string $emails, ?string $stream = null): array
    {
        return $this->client->post(
            '/message-streams/'.($stream ?? $this->defaultStream).'/suppressions',
            ['Suppressions' => $this->payload($emails)],
        );
    }

    /**
     * @param  array<int, string>|string  $emails
     * @return array<mixed>
     */
    public function delete(array|string $emails, ?string $stream = null): array
    {
        return $this->client->post(
            '/message-streams/'.($stream ?? $this->defaultStream).'/suppressions/delete',
            ['Suppressions' => $this->payload($emails)],
        );
    }

    /**
     * @param  array<int, string>|string  $emails
     * @return array<int, array<string, string>>
     */
    protected function payload(array|string $emails): array
    {
        $emails = array_values(array_filter(array_map('trim', (array) $emails)));

        if ($emails === []) {
            throw new InvalidArgumentException('At least one email address is required.');
        }

        return array_map(fn (string $email) => ['EmailAddress' => $email], $emails);
    }
}
