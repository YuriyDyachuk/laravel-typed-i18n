<?php

namespace YuriiDiachuk\LaravelTypedI18n;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use YuriiDiachuk\LaravelTypedI18n\Commands\LaravelTypedI18nCommand;

class LaravelTypedI18nServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-typed-i18n')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_typed_i18n_table')
            ->hasCommand(LaravelTypedI18nCommand::class);
    }
}
