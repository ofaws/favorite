<?php

namespace Ofaws\Favorite\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ofaws\Favorite\Favorite
 */
class Favorite extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \ofaws\Favorite\Favorite::class;
    }
}
