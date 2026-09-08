<?php

namespace Jeffersongoncalves\Postmark;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PostmarkServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-postmark')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations();
    }
}
