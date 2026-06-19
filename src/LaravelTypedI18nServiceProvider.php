<?php

declare(strict_types=1);

namespace YuriiDiachuk\LaravelTypedI18n;

use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use YuriiDiachuk\LaravelTypedI18n\Commands\GenerateTypesCommand;
use YuriiDiachuk\LaravelTypedI18n\Source\LaravelLangSource;
use YuriiDiachuk\LaravelTypedI18n\Source\TranslationSource;

class LaravelTypedI18nServiceProvider extends PackageServiceProvider
{
    public function packageRegistered(): void
    {
        // Default source is the Laravel lang directory. Bind it to the
        // interface so the command stays backend agnostic and so anyone can
        // swap in their own source.
        $this->app->bind(TranslationSource::class, function (): LaravelLangSource {
            $configured = config('typed-i18n.lang_path');

            $langPath = is_string($configured) && $configured !== ''
                ? rtrim($configured, '/')
                : lang_path();

            return new LaravelLangSource($langPath, (bool) config('typed-i18n.include_vendor', true));
        });
    }

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
            ->hasCommand(GenerateTypesCommand::class)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('yuriidiachuk/laravel-typed-i18n');
            });
    }
}
