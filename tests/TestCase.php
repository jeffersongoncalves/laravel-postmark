<?php

namespace Jeffersongoncalves\Postmark\Tests;

use Jeffersongoncalves\Postmark\PostmarkServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            PostmarkServiceProvider::class,
        ];
    }
}
