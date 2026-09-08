<?php

namespace JeffersonGoncalves\Postmark\Tests;

use JeffersonGoncalves\Postmark\PostmarkServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            PostmarkServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('postmark.api_url', 'https://api.postmarkapp.com');
        $app['config']->set('postmark.token', 'test-server-token');
        $app['config']->set('postmark.message_stream', 'outbound');
        $app['config']->set('postmark.default_count', 50);
    }
}
