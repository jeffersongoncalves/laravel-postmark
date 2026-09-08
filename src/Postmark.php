<?php

namespace JeffersonGoncalves\Postmark;

use InvalidArgumentException;
use JeffersonGoncalves\Postmark\Resources\Bounces;
use JeffersonGoncalves\Postmark\Resources\Messages;
use JeffersonGoncalves\Postmark\Resources\Stats;
use JeffersonGoncalves\Postmark\Resources\Suppressions;
use JeffersonGoncalves\Postmark\Resources\Templates;

/**
 * Entry point exposing one resource per Postmark API group, plus the
 * single-call sending endpoints and the server endpoint.
 */
class Postmark
{
    protected PostmarkClient $client;

    public function __construct(
        string $apiUrl,
        string $token,
        protected string $defaultStream = 'outbound',
        protected int $defaultCount = 50,
    ) {
        $this->client = new PostmarkClient($apiUrl, $token);
    }

    // ponytail: /server is a single parameterless GET — not worth a dedicated
    // resource class, unlike the multi-endpoint groups below.
    /** @return array<mixed> */
    public function server(): array
    {
        return $this->client->get('/server');
    }

    public function templates(): Templates
    {
        return new Templates($this->client, $this->defaultCount);
    }

    public function bounces(): Bounces
    {
        return new Bounces($this->client, $this->defaultCount);
    }

    public function messages(): Messages
    {
        return new Messages($this->client, $this->defaultCount);
    }

    public function stats(): Stats
    {
        return new Stats($this->client);
    }

    public function suppressions(): Suppressions
    {
        return new Suppressions($this->client, $this->defaultStream);
    }

    /**
     * @param  string|array<int, string>  $to  One address, or several (Postmark accepts up to 50, comma separated).
     * @param  array<string, mixed>  $options  Extra fields: Cc, Bcc, ReplyTo, Tag, TrackOpens, TrackLinks, Headers, Attachments, Metadata, MessageStream.
     * @return array<mixed>
     */
    public function sendEmail(
        string $from,
        string|array $to,
        string $subject,
        ?string $htmlBody = null,
        ?string $textBody = null,
        array $options = [],
    ): array {
        if ($htmlBody === null && $textBody === null) {
            throw new InvalidArgumentException('Either "htmlBody" or "textBody" is required.');
        }

        return $this->client->post('/email', array_merge([
            'From' => $from,
            'To' => $this->recipients($to),
            'Subject' => $subject,
            'MessageStream' => $this->defaultStream,
        ], array_filter([
            'HtmlBody' => $htmlBody,
            'TextBody' => $textBody,
        ], fn (mixed $value) => $value !== null), $options));
    }

    /**
     * @param  int|string  $template  Numeric TemplateId or string TemplateAlias.
     * @param  string|array<int, string>  $to
     * @param  array<string, mixed>  $model
     * @param  array<string, mixed>  $options
     * @return array<mixed>
     */
    public function sendEmailWithTemplate(
        string $from,
        string|array $to,
        int|string $template,
        array $model = [],
        array $options = [],
    ): array {
        $payload = [
            'From' => $from,
            'To' => $this->recipients($to),
            // An empty model must serialize as {} rather than [], hence the cast.
            'TemplateModel' => (object) $model,
            'MessageStream' => $this->defaultStream,
        ];

        $payload[is_int($template) ? 'TemplateId' : 'TemplateAlias'] = $template;

        return $this->client->post('/email/withTemplate', array_merge($payload, $options));
    }

    /**
     * @param  array<int, array<string, mixed>>  $messages  Raw Postmark message payloads.
     * @return array<mixed>
     */
    public function sendBatch(array $messages): array
    {
        if ($messages === []) {
            throw new InvalidArgumentException('At least one message is required.');
        }

        return $this->client->post('/email/batch', array_map(
            fn (array $message) => array_merge(['MessageStream' => $this->defaultStream], $message),
            array_values($messages),
        ));
    }

    /**
     * @param  array<int, array<string, mixed>>  $messages  Raw Postmark template message payloads.
     * @return array<mixed>
     */
    public function sendBatchWithTemplates(array $messages): array
    {
        if ($messages === []) {
            throw new InvalidArgumentException('At least one message is required.');
        }

        return $this->client->post('/email/batchWithTemplates', [
            'Messages' => array_map(
                fn (array $message) => array_merge(['MessageStream' => $this->defaultStream], $message),
                array_values($messages),
            ),
        ]);
    }

    /** @param string|array<int, string> $to */
    protected function recipients(string|array $to): string
    {
        return is_array($to) ? implode(',', $to) : $to;
    }
}
