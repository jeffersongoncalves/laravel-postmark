<?php

namespace JeffersonGoncalves\Postmark;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PostmarkServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('postmark')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Postmark::class, function () {
            return new Postmark(
                (string) config('postmark.api_url'),
                (string) config('postmark.token'),
                (string) config('postmark.message_stream', 'outbound'),
                (int) config('postmark.default_count', 50),
            );
        });
    }
}
