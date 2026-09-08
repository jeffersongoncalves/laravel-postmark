<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Postmark\Exceptions\PostmarkException;
use JeffersonGoncalves\Postmark\Facades\Postmark;

it('gets the server', function () {
    Http::fake(['*/server' => Http::response(['ID' => 1, 'Name' => 'My Server'])]);

    expect(Postmark::server()['Name'])->toBe('My Server');

    Http::assertSent(fn ($request) => $request->hasHeader('X-Postmark-Server-Token', 'test-server-token'));
});

it('sends an email', function () {
    Http::fake(['*/email' => Http::response(['MessageID' => 'abc', 'ErrorCode' => 0])]);

    $result = Postmark::sendEmail('from@example.com', 'to@example.com', 'Hi', htmlBody: '<p>Hi</p>');

    expect($result['MessageID'])->toBe('abc');
    Http::assertSent(fn ($request) => $request['From'] === 'from@example.com'
        && $request['To'] === 'to@example.com'
        && $request['HtmlBody'] === '<p>Hi</p>'
        && $request['MessageStream'] === 'outbound');
});

it('joins multiple recipients with a comma', function () {
    Http::fake(['*/email' => Http::response(['MessageID' => 'abc'])]);

    Postmark::sendEmail('from@example.com', ['a@example.com', 'b@example.com'], 'Hi', textBody: 'Hi');

    Http::assertSent(fn ($request) => $request['To'] === 'a@example.com,b@example.com');
});

it('requires a html or text body', function () {
    Postmark::sendEmail('from@example.com', 'to@example.com', 'Hi');
})->throws(InvalidArgumentException::class, 'Either "htmlBody" or "textBody" is required.');

it('sends with a template alias', function () {
    Http::fake(['*/email/withTemplate' => Http::response(['MessageID' => 'abc'])]);

    Postmark::sendEmailWithTemplate('from@example.com', 'to@example.com', 'welcome', ['name' => 'Ada']);

    Http::assertSent(fn ($request) => $request['TemplateAlias'] === 'welcome'
        && (array) $request['TemplateModel'] === ['name' => 'Ada']
        && ! isset($request['TemplateId']));
});

it('sends with a numeric template id', function () {
    Http::fake(['*/email/withTemplate' => Http::response(['MessageID' => 'abc'])]);

    Postmark::sendEmailWithTemplate('from@example.com', 'to@example.com', 1234);

    Http::assertSent(fn ($request) => $request['TemplateId'] === 1234 && ! isset($request['TemplateAlias']));
});

it('sends a batch, defaulting the message stream on each message', function () {
    Http::fake(['*/email/batch' => Http::response([['MessageID' => 'a'], ['MessageID' => 'b']])]);

    $result = Postmark::sendBatch([
        ['From' => 'from@example.com', 'To' => 'a@example.com', 'Subject' => 'Hi', 'TextBody' => 'Hi'],
        ['From' => 'from@example.com', 'To' => 'b@example.com', 'Subject' => 'Hi', 'TextBody' => 'Hi', 'MessageStream' => 'broadcast'],
    ]);

    expect($result)->toHaveCount(2);
    Http::assertSent(fn ($request) => $request[0]['MessageStream'] === 'outbound'
        && $request[1]['MessageStream'] === 'broadcast');
});

it('rejects an empty batch', function () {
    Postmark::sendBatch([]);
})->throws(InvalidArgumentException::class, 'At least one message is required.');

it('sends a batch with templates', function () {
    Http::fake(['*/email/batchWithTemplates' => Http::response([['MessageID' => 'a']])]);

    Postmark::sendBatchWithTemplates([
        ['From' => 'from@example.com', 'To' => 'a@example.com', 'TemplateAlias' => 'welcome'],
    ]);

    Http::assertSent(fn ($request) => $request['Messages'][0]['TemplateAlias'] === 'welcome');
});

it('throws a PostmarkException on a failed response', function () {
    Http::fake(['*/server' => Http::response(['ErrorCode' => 10, 'Message' => 'Bad or missing API token'], 401)]);

    Postmark::server();
})->throws(PostmarkException::class, 'Bad or missing API token');
