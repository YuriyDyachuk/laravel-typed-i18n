<?php

declare(strict_types=1);

namespace YuriiDiachuk\LaravelTypedI18n\Source;

use YuriiDiachuk\LaravelTypedI18n\Parser\TranslationKey;

/**
 * A place to read translation keys from.
 *
 * Laravel lang files are one such place. Keeping this an interface lets the
 * generator stay the same when we add other backends later (plain JSON,
 * i18next, and so on).
 */
interface TranslationSource
{
    /**
     * Translation keys for a single locale.
     *
     * @return TranslationKey[]
     */
    public function keys(string $locale): array;

    /**
     * Locales this source can provide.
     *
     * @return string[]
     */
    public function availableLocales(): array;
}
