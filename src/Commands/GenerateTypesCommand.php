<?php

declare(strict_types=1);

namespace YuriiDiachuk\LaravelTypedI18n\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use YuriiDiachuk\LaravelTypedI18n\Generator\TypeScriptGenerator;
use YuriiDiachuk\LaravelTypedI18n\Locale\LocaleComparator;
use YuriiDiachuk\LaravelTypedI18n\Source\LaravelLangSource;
use YuriiDiachuk\LaravelTypedI18n\Source\TranslationSource;

class GenerateTypesCommand extends Command
{
    protected $signature = 'typed-i18n:generate
        {--locale= : Reference locale to generate types from (defaults to config)}
        {--output= : Path to write the .d.ts file to (defaults to config)}';

    protected $description = 'Generate TypeScript types from Laravel translation files';

    public function handle(
        TranslationSource $source,
        TypeScriptGenerator $generator,
        LocaleComparator $comparator,
    ): int {
        if ($source instanceof LaravelLangSource && ! is_dir($source->langPath)) {
            $this->components->error("Lang path [{$source->langPath}] does not exist.");
            $this->components->info('Run `php artisan lang:publish` or set typed-i18n.lang_path.');

            return self::FAILURE;
        }

        $reference = $this->referenceLocale();
        $keys = $source->keys($reference);

        if ($keys === []) {
            $this->components->warn("No translation keys found for locale [{$reference}].");
        }

        $output = $this->outputPath();
        File::ensureDirectoryExists(dirname($output));
        File::put($output, $generator->generate($keys));

        $this->components->info('Generated '.count($keys)." keys -> {$output}");

        $this->reportDrift($comparator, $source, $reference);

        return self::SUCCESS;
    }

    private function outputPath(): string
    {
        $option = $this->option('output');

        if (is_string($option) && $option !== '') {
            return $option;
        }

        $configured = config('typed-i18n.output');

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        return resource_path('js/types/translations.d.ts');
    }

    private function referenceLocale(): string
    {
        $option = $this->option('locale');

        if (is_string($option) && $option !== '') {
            return $option;
        }

        $configured = config('typed-i18n.default_locale');

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        return (string) config('app.fallback_locale', 'en');
    }

    /**
     * Locales to check for drift. Falls back to whatever the source can offer.
     *
     * @return string[]
     */
    private function locales(TranslationSource $source): array
    {
        $configured = config('typed-i18n.locales');

        if (is_array($configured) && $configured !== []) {
            return $configured;
        }

        return $source->availableLocales();
    }

    private function reportDrift(
        LocaleComparator $comparator,
        TranslationSource $source,
        string $reference,
    ): void {
        $diffs = $comparator->compare($source, $reference, $this->locales($source));

        if ($diffs === []) {
            return;
        }

        foreach ($diffs as $diff) {
            if ($diff->missing !== []) {
                $this->components->warn(
                    "Locale [{$diff->locale}] is missing ".count($diff->missing)
                    ." key(s) present in [{$reference}]: ".implode(', ', $diff->missing)
                );
            }

            if ($diff->extra !== []) {
                $this->components->warn(
                    "Locale [{$diff->locale}] has ".count($diff->extra)
                    ." key(s) absent from [{$reference}]: ".implode(', ', $diff->extra)
                );
            }
        }
    }
}
