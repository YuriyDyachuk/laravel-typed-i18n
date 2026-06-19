<?php

declare(strict_types=1);

namespace YuriiDiachuk\LaravelTypedI18n\Locale;

final class LocaleDiff
{
    /**
     * @param  string[]  $missing  Keys present in the reference locale but absent here.
     * @param  string[]  $extra  Keys present here but absent in the reference locale.
     */
    public function __construct(
        public readonly string $locale,
        public readonly array $missing,
        public readonly array $extra,
    ) {}
}
