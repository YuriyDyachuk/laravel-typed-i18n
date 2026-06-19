<?php

declare(strict_types=1);

namespace YuriiDiachuk\LaravelTypedI18n\Source;

use YuriiDiachuk\LaravelTypedI18n\Parser\LangFileParser;

/**
 * Reads keys from a Laravel lang directory.
 *
 * Thin wrapper around LangFileParser that also knows which locales live in the
 * directory, so the command does not have to scan it by hand.
 */
final class LaravelLangSource implements TranslationSource
{
    public function __construct(
        public readonly string $langPath,
        private readonly bool $includeVendor = true,
        private readonly LangFileParser $parser = new LangFileParser,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function keys(string $locale): array
    {
        return $this->parser->parse($this->langPath, $locale, $this->includeVendor);
    }

    /**
     * Locales found as {locale}/ directories and {locale}.json files.
     * The vendor directory is not a locale, so it is skipped.
     *
     * @return string[]
     */
    public function availableLocales(): array
    {
        $locales = [];

        foreach (glob($this->langPath.'/*', GLOB_ONLYDIR) ?: [] as $dir) {
            $name = basename($dir);

            if ($name !== 'vendor') {
                $locales[] = $name;
            }
        }

        foreach (glob($this->langPath.'/*.json') ?: [] as $json) {
            $locales[] = basename($json, '.json');
        }

        return array_values(array_unique($locales));
    }
}
