<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Postmark\Facades\Postmark;

it('lists outbound messages', function () {
    Http::fake(['*/messages/outbound?*' => Http::response(['TotalCount' => 1, 'Messages' => [['MessageID' => 'a']]])]);

    expect(Postmark::messages()->outbound(['tag' => 'welcome'])['TotalCount'])->toBe(1);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'tag=welcome'));
});

it('lists inbound messages with a custom count', function () {
    Http::fake(['*/messages/inbound?*' => Http::response(['TotalCount' => 0, 'InboundMessages' => []])]);

    Postmark::messages()->inbound(count: 10, offset: 20);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'count=10')
        && str_contains($request->url(), 'offset=20'));
});

it('gets outbound message details', function () {
    Http::fake(['*/messages/outbound/a/details*' => Http::response(['MessageID' => 'a', 'Status' => 'Sent'])]);

    expect(Postmark::messages()->get('a')['Status'])->toBe('Sent');
});

it('gets inbound message details', function () {
    Http::fake(['*/messages/inbound/b/details*' => Http::response(['MessageID' => 'b'])]);

    expect(Postmark::messages()->inboundDetails('b')['MessageID'])->toBe('b');
});
