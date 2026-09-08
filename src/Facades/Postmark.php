<?php

namespace JeffersonGoncalves\Postmark\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JeffersonGoncalves\Postmark\Postmark
 */
class Postmark extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \JeffersonGoncalves\Postmark\Postmark::class;
    }
}
