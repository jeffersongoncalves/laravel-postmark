<?php

namespace JeffersonGoncalves\Postmark\Resources;

use JeffersonGoncalves\Postmark\PostmarkClient;

class Bounces
{
    public function __construct(
        protected PostmarkClient $client,
        protected int $defaultCount = 50,
    ) {}

    /**
     * @param  array<string, mixed>  $filters  Any of: type, inactive, emailFilter, tag, messageID, fromdate, todate.
     * @return array<mixed>
     */
    public function list(array $filters = [], ?int $count = null, int $offset = 0): array
    {
        return $this->client->get('/bounces', array_merge([
            'count' => $count ?? $this->defaultCount,
            'offset' => $offset,
        ], $filters));
    }

    /** @return array<mixed> */
    public function get(int|string $id): array
    {
        return $this->client->get('/bounces/'.$id);
    }

    /** @return array<mixed> */
    public function dump(int|string $id): array
    {
        return $this->client->get('/bounces/'.$id.'/dump');
    }

    /**
     * Reactivate a bounced address so Postmark will deliver to it again.
     *
     * @return array<mixed>
     */
    public function activate(int|string $id): array
    {
        return $this->client->put('/bounces/'.$id.'/activate');
    }

    /** @return array<mixed> */
    public function deliveryStats(): array
    {
        return $this->client->get('/deliverystats');
    }
}
