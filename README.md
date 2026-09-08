<div class="filament-hidden">

![Laravel Postmark](https://raw.githubusercontent.com/jeffersongoncalves/laravel-postmark/main/art/jeffersongoncalves-laravel-postmark.png)

</div>

# Laravel Postmark

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/laravel-postmark.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-postmark)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-postmark/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jeffersongoncalves/laravel-postmark/actions?query=workflow%3ATests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-postmark/pint.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/jeffersongoncalves/laravel-postmark/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/laravel-postmark.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-postmark)
[![License](https://img.shields.io/packagist/l/jeffersongoncalves/laravel-postmark.svg?style=flat-square)](LICENSE.md)

A PHP/Laravel client for the [Postmark](https://postmarkapp.com/) REST API. Covers transactional email (single, templated and batch), templates, bounces, message search, outbound statistics, server details and suppressions through a simple, typed API built on Laravel's `Http` client.

## Features

- Transactional email: send a single message, a templated message, or a batch of either
- Templates: list, get, create, update, delete
- Bounces: list with filters, get, raw dump, reactivate, delivery stats
- Messages: search outbound and inbound, get message details
- Stats: outbound overview, sends, bounces, opens, clicks, spam
- Server: get the authenticated server's details
- Suppressions: dump, create and delete per message stream
- Throws `PostmarkException` (carrying Postmark's `ErrorCode` and error body) on any non-2xx response
- Throws `InvalidArgumentException` before hitting the API when a required field is missing

## Installation

You can install the package via composer:

```bash
composer require jeffersongoncalves/laravel-postmark
```

Publish the config file:

```bash
php artisan vendor:publish --tag=postmark-config
```

Set your Postmark server token in `.env`:

```env
POSTMARK_TOKEN=your-server-token
```

Find it under **Servers > (your server) > API Tokens** in your Postmark account.

## Configuration

```php
// config/postmark.php
return [
    'api_url' => env('POSTMARK_API_URL', 'https://api.postmarkapp.com'),
    'token' => env('POSTMARK_TOKEN', ''),
    'message_stream' => env('POSTMARK_MESSAGE_STREAM', 'outbound'),
    'default_count' => env('POSTMARK_DEFAULT_COUNT', 50),
];
```

`message_stream` is applied to every send and suppression call that does not pass its own, and `default_count` is the page size used by the list endpoints.

## Usage

The package is resolved via the `Postmark` facade or by injecting `JeffersonGoncalves\Postmark\Postmark`. Each API group is exposed as a method returning a dedicated resource class; sending and the server endpoint live directly on the manager.

### Sending email

```php
use JeffersonGoncalves\Postmark\Facades\Postmark;

Postmark::sendEmail(
    from: 'sender@example.com',
    to: 'recipient@example.com',
    subject: 'Welcome aboard',
    htmlBody: '<p>Hello!</p>',
);

// Several recipients (Postmark accepts up to 50 per message):
Postmark::sendEmail('sender@example.com', ['a@example.com', 'b@example.com'], 'Hi', textBody: 'Hi');

// Any other Postmark field goes in $options:
Postmark::sendEmail(
    from: 'sender@example.com',
    to: 'recipient@example.com',
    subject: 'Welcome aboard',
    htmlBody: '<p>Hello!</p>',
    options: [
        'Cc' => 'cc@example.com',
        'ReplyTo' => 'reply@example.com',
        'Tag' => 'welcome',
        'TrackOpens' => true,
        'TrackLinks' => 'HtmlAndText',
        'MessageStream' => 'broadcast',
    ],
);
```

### Sending with a template

An integer is sent as `TemplateId`, a string as `TemplateAlias`:

```php
Postmark::sendEmailWithTemplate('sender@example.com', 'recipient@example.com', 'welcome', [
    'name' => 'Ada',
    'product_url' => 'https://example.com',
]);

Postmark::sendEmailWithTemplate('sender@example.com', 'recipient@example.com', 1234567);
```

### Sending a batch

Batches take raw Postmark message payloads; the configured message stream is applied to any message that does not set its own.

```php
Postmark::sendBatch([
    ['From' => 'sender@example.com', 'To' => 'a@example.com', 'Subject' => 'Hi', 'TextBody' => 'Hi'],
    ['From' => 'sender@example.com', 'To' => 'b@example.com', 'Subject' => 'Hi', 'TextBody' => 'Hi'],
]);

Postmark::sendBatchWithTemplates([
    ['From' => 'sender@example.com', 'To' => 'a@example.com', 'TemplateAlias' => 'welcome', 'TemplateModel' => ['name' => 'Ada']],
]);
```

### Templates

```php
Postmark::templates()->list();
Postmark::templates()->list(count: 10, offset: 0, type: 'Standard');
Postmark::templates()->get('welcome');
Postmark::templates()->create('Welcome', 'Welcome aboard', [
    'Alias' => 'welcome',
    'HtmlBody' => '<p>Hello {{name}}</p>',
    'TextBody' => 'Hello {{name}}',
]);
Postmark::templates()->update('welcome', ['Subject' => 'Welcome!']);
Postmark::templates()->delete('welcome');
```

### Bounces

```php
Postmark::bounces()->list(['type' => 'HardBounce', 'inactive' => true]);
Postmark::bounces()->get(692560173);
Postmark::bounces()->dump(692560173);   // raw bounce source
Postmark::bounces()->activate(692560173); // reactivate the address
Postmark::bounces()->deliveryStats();
```

### Messages

```php
Postmark::messages()->outbound(['tag' => 'welcome', 'status' => 'sent']);
Postmark::messages()->inbound(['recipient' => 'inbox@example.com']);
Postmark::messages()->get('0a129aee-e1cd-480d-b08d-4f48548ff48d');
Postmark::messages()->inboundDetails('e2ecbbfc-fe12-463d-b933-9fe22915106d');
```

### Stats

```php
Postmark::stats()->overview();
Postmark::stats()->sends(tag: 'welcome', fromDate: '2026-01-01', toDate: '2026-01-31');
Postmark::stats()->bounces();
Postmark::stats()->opens();
Postmark::stats()->clicks();
Postmark::stats()->spam();
```

### Server

```php
Postmark::server();
```

### Suppressions

```php
Postmark::suppressions()->list();                    // configured stream
Postmark::suppressions()->list('broadcast');
Postmark::suppressions()->create('bounced@example.com');
Postmark::suppressions()->create(['a@example.com', 'b@example.com'], 'broadcast');
Postmark::suppressions()->delete('a@example.com');
```

### Error handling

```php
use JeffersonGoncalves\Postmark\Exceptions\PostmarkException;

try {
    Postmark::sendEmail('sender@example.com', 'recipient@example.com', 'Hi', textBody: 'Hi');
} catch (PostmarkException $e) {
    $e->getMessage();  // Postmark's "Message"
    $e->getCode();     // Postmark's "ErrorCode", or the HTTP status when absent
    $e->errorBody();   // the full decoded error body
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Jefferson Gonçalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
