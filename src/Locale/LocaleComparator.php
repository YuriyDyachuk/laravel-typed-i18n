<?php

declare(strict_types=1);

namespace YuriiDiachuk\LaravelTypedI18n\Locale;

use YuriiDiachuk\LaravelTypedI18n\Source\TranslationSource;

class LocaleComparator
{
    /**
     * Compare every locale against the reference and report key drift.
     *
     * The reference locale defines the expected set of keys. Each other locale
     * is checked for keys it is missing and keys it has on top.
     *
     * @param  string[]  $locales  Locales to check. The reference is skipped if listed.
     * @return array<string, LocaleDiff> Keyed by locale. Locales that match the reference are omitted.
     */
    public function compare(TranslationSource $source, string $reference, array $locales): array
    {
        $referenceKeys = $this->keyStrings($source, $reference);

        $diffs = [];

        foreach ($locales as $locale) {
            if ($locale === $reference) {
                continue;
            }

            $localeKeys = $this->keyStrings($source, $locale);

            $missing = array_values(array_diff($referenceKeys, $localeKeys));
            $extra = array_values(array_diff($localeKeys, $referenceKeys));

            if ($missing !== [] || $extra !== []) {
                $diffs[$locale] = new LocaleDiff($locale, $missing, $extra);
            }
        }

        return $diffs;
    }

    /**
     * @return string[]
     */
    private function keyStrings(TranslationSource $source, string $locale): array
    {
        return array_map(
            static fn ($translationKey): string => $translationKey->key,
            $source->keys($locale),
        );
    }
}
