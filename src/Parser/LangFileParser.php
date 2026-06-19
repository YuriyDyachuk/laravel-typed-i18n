<?php

declare(strict_types=1);

namespace YuriiDiachuk\LaravelTypedI18n\Parser;

class LangFileParser
{
    /**
     * Scan a lang directory for a single locale and return a flat list of translation keys.
     *
     * Reads, in order:
     *  - {locale}/*.php group files       keys prefixed with the group name (messages.auth.login)
     *  - {locale}.json                    keys used verbatim (the phrase is the key)
     *  - vendor/{package}/{locale}/*.php  keys prefixed package::group (acme::messages.welcome)
     *
     * @return TranslationKey[]
     */
    public function parse(string $langPath, string $locale = 'en', bool $includeVendor = true): array
    {
        $langPath = rtrim($langPath, '/');
        $keys = [];

        $this->parseGroupFiles($langPath.'/'.$locale, '', $keys);
        $this->parseJsonFile($langPath.'/'.$locale.'.json', $keys);

        if ($includeVendor) {
            $this->parseVendorFiles($langPath.'/vendor', $locale, $keys);
        }

        return $keys;
    }

    /**
     * @param  TranslationKey[]  $keys
     */
    private function parseGroupFiles(string $localePath, string $namespace, array &$keys): void
    {
        if (! is_dir($localePath)) {
            return;
        }

        foreach (glob($localePath.'/*.php') ?: [] as $file) {
            $group = basename($file, '.php');
            $prefix = $namespace !== ''
                ? $namespace.'::'.$group
                : $group;

            $translations = require $file;

            if (is_array($translations)) {
                $this->flatten($translations, $prefix, $keys);
            }
        }
    }

    /**
     * @param  TranslationKey[]  $keys
     */
    private function parseJsonFile(string $jsonFile, array &$keys): void
    {
        if (! file_exists($jsonFile)) {
            return;
        }

        $translations = json_decode((string) file_get_contents($jsonFile), associative: true);

        if (is_array($translations)) {
            $this->flatten($translations, '', $keys);
        }
    }

    /**
     * @param  TranslationKey[]  $keys
     */
    private function parseVendorFiles(string $vendorPath, string $locale, array &$keys): void
    {
        if (! is_dir($vendorPath)) {
            return;
        }

        foreach (glob($vendorPath.'/*', GLOB_ONLYDIR) ?: [] as $packageDir) {
            $package = basename($packageDir);
            $this->parseGroupFiles($packageDir.'/'.$locale, $package, $keys);
        }
    }

    /**
     * @param  array<array-key, mixed>  $translations
     * @param  TranslationKey[]  $keys
     */
    private function flatten(array $translations, string $prefix, array &$keys): void
    {
        foreach ($translations as $key => $value) {
            $fullKey = $prefix !== ''
                ? $prefix.'.'.$key
                : (string) $key;

            if (is_array($value)) {
                $this->flatten($value, $fullKey, $keys);

                continue;
            }

            if (is_string($value)) {
                $isPlural = $this->isPlural($value);

                $keys[] = new TranslationKey(
                    key: $fullKey,
                    placeholders: $this->extractPlaceholders($value, $isPlural),
                    isPlural: $isPlural,
                );
            }
        }
    }

    /**
     * Extract :placeholder names, case-normalized and deduplicated.
     *
     * Laravel replaces :name/:Name/:NAME from a single data key, so these collapse to one
     * canonical lowercase name. The leading-letter rule keeps time-like text (12:30) out.
     * `count` is dropped for plural strings because it is represented by the numeric count arg.
     *
     * @return string[]
     */
    private function extractPlaceholders(string $value, bool $isPlural): array
    {
        preg_match_all('/:(\p{L}[\p{L}\p{N}_]*)/u', $value, $matches);

        $names = array_map('strtolower', $matches[1]);

        if ($isPlural) {
            $names = array_filter($names, static fn (string $name): bool => $name !== 'count');
        }

        return array_values(array_unique($names));
    }

    /**
     * Decide whether a string is a Laravel choice/plural string (needs a count argument).
     *
     * Explicit condition markers ({0}, [2,*]) are high-confidence plural. A bare `|` with
     * more than one segment is treated as a simple plural (apple|apples) per Laravel's
     * delimiter convention; literal pipes in prose are a rare, accepted false positive.
     */
    private function isPlural(string $value): bool
    {
        $segments = explode('|', $value);

        foreach ($segments as $segment) {
            if (preg_match('/^\s*[\{\[][^\[\]{}]*[\]}]/', $segment) === 1) {
                return true;
            }
        }

        return count($segments) > 1;
    }
}
