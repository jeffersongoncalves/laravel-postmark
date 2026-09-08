<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Postmark\Facades\Postmark;

it('lists bounces with filters', function () {
    Http::fake(['*/bounces*' => Http::response(['TotalCount' => 1, 'Bounces' => [['ID' => 9]]])]);

    expect(Postmark::bounces()->list(['type' => 'HardBounce'])['TotalCount'])->toBe(1);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'type=HardBounce')
        && str_contains($request->url(), 'count=50'));
});

it('gets a bounce and its dump', function () {
    Http::fake([
        '*/bounces/9' => Http::response(['ID' => 9, 'Type' => 'HardBounce']),
        '*/bounces/9/dump' => Http::response(['Body' => 'raw']),
    ]);

    expect(Postmark::bounces()->get(9)['Type'])->toBe('HardBounce')
        ->and(Postmark::bounces()->dump(9)['Body'])->toBe('raw');
});

it('activates a bounce', function () {
    Http::fake(['*/bounces/9/activate' => Http::response(['Message' => 'OK'])]);

    Postmark::bounces()->activate(9);

    Http::assertSent(fn ($request) => $request->method() === 'PUT');
});

it('gets delivery stats', function () {
    Http::fake(['*/deliverystats' => Http::response(['InactiveMails' => 0])]);

    expect(Postmark::bounces()->deliveryStats()['InactiveMails'])->toBe(0);
});
