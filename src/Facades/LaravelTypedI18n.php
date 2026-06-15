<?php

namespace YuriiDiachuk\LaravelTypedI18n\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \YuriiDiachuk\LaravelTypedI18n\LaravelTypedI18n
 */
class LaravelTypedI18n extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \YuriiDiachuk\LaravelTypedI18n\LaravelTypedI18n::class;
    }
}
