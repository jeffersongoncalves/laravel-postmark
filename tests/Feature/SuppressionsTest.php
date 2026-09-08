<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Postmark\Facades\Postmark;

it('lists suppressions for the configured stream', function () {
    Http::fake(['*/message-streams/outbound/suppressions/dump' => Http::response(['Suppressions' => []])]);

    Postmark::suppressions()->list();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/message-streams/outbound/suppressions/dump'));
});

it('creates suppressions from a comma separated string or an array', function () {
    Http::fake(['*/message-streams/*/suppressions' => Http::response(['Suppressions' => []])]);

    Postmark::suppressions()->create(['a@example.com', ' b@example.com ']);

    Http::assertSent(fn ($request) => $request['Suppressions'] === [
        ['EmailAddress' => 'a@example.com'],
        ['EmailAddress' => 'b@example.com'],
    ]);
});

it('deletes suppressions on an explicit stream', function () {
    Http::fake(['*/message-streams/broadcast/suppressions/delete' => Http::response(['Suppressions' => []])]);

    Postmark::suppressions()->delete('a@example.com', 'broadcast');

    Http::assertSent(fn ($request) => $request['Suppressions'] === [['EmailAddress' => 'a@example.com']]);
});

it('requires at least one email', function () {
    Postmark::suppressions()->create([]);
})->throws(InvalidArgumentException::class, 'At least one email address is required.');
