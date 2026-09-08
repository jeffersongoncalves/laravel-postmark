<?php

namespace JeffersonGoncalves\Postmark\Resources;

use JeffersonGoncalves\Postmark\PostmarkClient;

class Stats
{
    public function __construct(
        protected PostmarkClient $client,
    ) {}

    /** @return array<mixed> */
    public function overview(?string $tag = null, ?string $fromDate = null, ?string $toDate = null): array
    {
        return $this->query('', $tag, $fromDate, $toDate);
    }

    /** @return array<mixed> */
    public function sends(?string $tag = null, ?string $fromDate = null, ?string $toDate = null): array
    {
        return $this->query('/sends', $tag, $fromDate, $toDate);
    }

    /** @return array<mixed> */
    public function bounces(?string $tag = null, ?string $fromDate = null, ?string $toDate = null): array
    {
        return $this->query('/bounces', $tag, $fromDate, $toDate);
    }

    /** @return array<mixed> */
    public function opens(?string $tag = null, ?string $fromDate = null, ?string $toDate = null): array
    {
        return $this->query('/opens', $tag, $fromDate, $toDate);
    }

    /** @return array<mixed> */
    public function clicks(?string $tag = null, ?string $fromDate = null, ?string $toDate = null): array
    {
        return $this->query('/clicks', $tag, $fromDate, $toDate);
    }

    /** @return array<mixed> */
    public function spam(?string $tag = null, ?string $fromDate = null, ?string $toDate = null): array
    {
        return $this->query('/spam', $tag, $fromDate, $toDate);
    }

    /** @return array<mixed> */
    protected function query(string $suffix, ?string $tag, ?string $fromDate, ?string $toDate): array
    {
        return $this->client->get('/stats/outbound'.$suffix, [
            'tag' => $tag,
            'fromdate' => $fromDate,
            'todate' => $toDate,
        ]);
    }
}
