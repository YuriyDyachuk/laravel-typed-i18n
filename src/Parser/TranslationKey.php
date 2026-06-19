<?php

declare(strict_types=1);

namespace YuriiDiachuk\LaravelTypedI18n\Parser;

final class TranslationKey
{
    /**
     * @param  string[]  $placeholders  Normalized (lowercased, deduplicated) :placeholder names,
     *                                  excluding `count` when $isPlural is true.
     * @param  bool  $isPlural  True when the string is a Laravel choice/plural string and
     *                          therefore requires a numeric `count` argument.
     */
    public function __construct(
        public readonly string $key,
        public readonly array $placeholders,
        public readonly bool $isPlural,
    ) {}
}
