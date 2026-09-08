<?php

namespace Jeffersongoncalves\Postmark\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Jeffersongoncalves\Postmark\Postmark
 */
class Postmark extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-postmark';
    }
}
