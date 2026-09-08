<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Postmark\Facades\Postmark;

it('gets the outbound overview', function () {
    Http::fake(['*/stats/outbound*' => Http::response(['Sent' => 10])]);

    expect(Postmark::stats()->overview()['Sent'])->toBe(10);

    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/stats/outbound'));
});

it('hits one endpoint per metric', function (string $method, string $path) {
    Http::fake(['*/stats/outbound*' => Http::response([])]);

    Postmark::stats()->{$method}('welcome', '2026-01-01', '2026-01-31');

    Http::assertSent(fn ($request) => str_contains($request->url(), $path)
        && str_contains($request->url(), 'tag=welcome')
        && str_contains($request->url(), 'fromdate=2026-01-01')
        && str_contains($request->url(), 'todate=2026-01-31'));
})->with([
    ['sends', '/stats/outbound/sends'],
    ['bounces', '/stats/outbound/bounces'],
    ['opens', '/stats/outbound/opens'],
    ['clicks', '/stats/outbound/clicks'],
    ['spam', '/stats/outbound/spam'],
]);
