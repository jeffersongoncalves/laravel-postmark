<?php

namespace JeffersonGoncalves\Postmark\Resources;

use InvalidArgumentException;
use JeffersonGoncalves\Postmark\PostmarkClient;

class Templates
{
    public function __construct(
        protected PostmarkClient $client,
        protected int $defaultCount = 50,
    ) {}

    /** @return array<mixed> */
    public function list(?int $count = null, int $offset = 0, ?string $type = null): array
    {
        return $this->client->get('/templates', [
            'Count' => $count ?? $this->defaultCount,
            'Offset' => $offset,
            'TemplateType' => $type,
        ]);
    }

    /** @return array<mixed> */
    public function get(int|string $identifier): array
    {
        return $this->client->get('/templates/'.$identifier);
    }

    /**
     * @param  array<string, mixed>  $attributes  Extra fields: HtmlBody, TextBody, Alias, TemplateType, LayoutTemplate.
     * @return array<mixed>
     */
    public function create(string $name, string $subject = '', array $attributes = []): array
    {
        if ($name === '') {
            throw new InvalidArgumentException('The "name" argument is required.');
        }

        return $this->client->post('/templates', array_merge([
            'Name' => $name,
            'Subject' => $subject,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<mixed>
     */
    public function update(int|string $identifier, array $attributes): array
    {
        return $this->client->put('/templates/'.$identifier, $attributes);
    }

    /** @return array<mixed> */
    public function delete(int|string $identifier): array
    {
        return $this->client->delete('/templates/'.$identifier);
    }
}
