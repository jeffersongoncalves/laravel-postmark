<?php

namespace JeffersonGoncalves\Postmark\Resources;

use JeffersonGoncalves\Postmark\PostmarkClient;

class Messages
{
    public function __construct(
        protected PostmarkClient $client,
        protected int $defaultCount = 50,
    ) {}

    /**
     * @param  array<string, mixed>  $filters  Any of: recipient, fromEmail, tag, status, todate, fromdate, subject, messageStream.
     * @return array<mixed>
     */
    public function outbound(array $filters = [], ?int $count = null, int $offset = 0): array
    {
        return $this->client->get('/messages/outbound', array_merge([
            'count' => $count ?? $this->defaultCount,
            'offset' => $offset,
        ], $filters));
    }

    /**
     * @param  array<string, mixed>  $filters  Any of: recipient, fromEmail, subject, mailboxHash, tag, status, todate, fromdate.
     * @return array<mixed>
     */
    public function inbound(array $filters = [], ?int $count = null, int $offset = 0): array
    {
        return $this->client->get('/messages/inbound', array_merge([
            'count' => $count ?? $this->defaultCount,
            'offset' => $offset,
        ], $filters));
    }

    /** @return array<mixed> */
    public function get(string $id): array
    {
        return $this->client->get('/messages/outbound/'.$id.'/details');
    }

    /** @return array<mixed> */
    public function inboundDetails(string $id): array
    {
        return $this->client->get('/messages/inbound/'.$id.'/details');
    }
}
