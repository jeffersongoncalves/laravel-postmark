<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Postmark\Facades\Postmark;

it('lists templates with the default count', function () {
    Http::fake(['*/templates*' => Http::response(['TotalCount' => 1, 'Templates' => [['TemplateId' => 1]]])]);

    expect(Postmark::templates()->list()['TotalCount'])->toBe(1);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'Count=50') && str_contains($request->url(), 'Offset=0'));
});

it('omits null filters from the query string', function () {
    Http::fake(['*/templates*' => Http::response([])]);

    Postmark::templates()->list();

    Http::assertSent(fn ($request) => ! str_contains($request->url(), 'TemplateType'));
});

it('gets a template', function () {
    Http::fake(['*/templates/1' => Http::response(['TemplateId' => 1, 'Alias' => 'welcome'])]);

    expect(Postmark::templates()->get(1)['Alias'])->toBe('welcome');
});

it('creates a template', function () {
    Http::fake(['*/templates' => Http::response(['TemplateId' => 2], 200)]);

    Postmark::templates()->create('Welcome', 'Hello', ['Alias' => 'welcome', 'HtmlBody' => '<p>Hi</p>']);

    Http::assertSent(fn ($request) => $request['Name'] === 'Welcome'
        && $request['Subject'] === 'Hello'
        && $request['Alias'] === 'welcome');
});

it('requires a name to create a template', function () {
    Postmark::templates()->create('');
})->throws(InvalidArgumentException::class, 'The "name" argument is required.');

it('updates and deletes a template', function () {
    Http::fake(['*/templates/1' => Http::response(['TemplateId' => 1])]);

    Postmark::templates()->update(1, ['Name' => 'Renamed']);
    Postmark::templates()->delete(1);

    Http::assertSentCount(2);
    Http::assertSent(fn ($request) => $request->method() === 'DELETE');
});
