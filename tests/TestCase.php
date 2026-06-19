<?php

namespace YuriiDiachuk\LaravelTypedI18n\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use YuriiDiachuk\LaravelTypedI18n\LaravelTypedI18nServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelTypedI18nServiceProvider::class,
        ];
    }
}
