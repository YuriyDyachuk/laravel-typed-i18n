# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this package is

A Laravel package (`yuriidiachuk/laravel-typed-i18n`) that **generates a TypeScript declaration file from Laravel translation files**. Built on [spatie/laravel-package-tools](https://github.com/spatie/laravel-package-tools). The DB/view/facade skeleton has been removed, so this is a pure, zero-runtime code generator.

## Commands

```bash
composer test                       # full Pest suite
vendor/bin/pest tests/Unit          # only the pure-PHP unit tests (parser, generator, comparator)
vendor/bin/pest --filter "golden"   # tests matching a description
vendor/bin/pest --dirty             # only tests for files changed in git (TDD inner loop)
composer test-coverage              # Pest with coverage
composer analyse                    # PHPStan level 5 (scans src/ config/)
composer format                     # Laravel Pint (default preset, formats in place)
```

Run `composer format` after any `.php` change. `phpstan-baseline.neon` is empty, so fix new PHPStan errors instead of baselining them. CI: `.github/workflows/run-tests.yml` runs a Laravel 11/12 x PHP 8.2-8.4 matrix.

## Architecture

One-way pipeline: `lang files -> TranslationSource -> TranslationKey[] -> .ts`.

1. **`Parser\LangFileParser`** is `parse(langPath, locale = 'en', includeVendor = true)` and returns a flat `TranslationKey[]`. Scans `{locale}/*.php` groups (group name prefixes the key), `{locale}.json` (phrase is the key, verbatim), and `vendor/{package}/{locale}/*.php` (`package::group.key`). Nested arrays flatten with dot notation; non-string leaves are skipped.
2. **`Parser\TranslationKey`** is an immutable VO: `key`, `placeholders` (string[]), `isPlural` (bool).
   - Placeholders are extracted with `/:(\p{L}[\p{L}\p{N}_]*)/u`, **lowercased and deduplicated**, so `:name`/`:Name`/`:NAME` collapse to one param, and the leading-letter rule keeps `12:30` out.
   - `isPlural` is **condition-aware**, not `str_contains('|')`: true if any `|`-segment starts with a `{...}`/`[...]` marker, or there is more than one segment. `count` is dropped from `placeholders` on plural keys (it becomes the numeric `count` arg).
3. **`Generator\TypeScriptGenerator`** is `generate(TranslationKey[]): string`. Emits `interface Translations` (key to params object), `type TranslationKey = keyof Translations`, and `type EmptyParamKeys`. Keys are `ksort`ed (SORT_STRING) for stable, diff-friendly output; plural keys get `count: number`, other placeholders `name: string | number`; param-less keys get `{}`. Keys are quoted and single-quote-escaped so JSON phrase keys work verbatim.
4. **`Source\TranslationSource`** is a backend-agnostic interface (`keys(locale): TranslationKey[]`, `availableLocales(): string[]`) that decouples the generator and command from where keys come from. **`Source\LaravelLangSource`** is the only implementation: it wraps `LangFileParser` (holding `langPath`/`includeVendor`) and discovers locales from `{locale}/` dirs and `{locale}.json` files. The provider binds the interface to it in `packageRegistered()`, resolving `lang_path`/`include_vendor` from config. This is the seam for future non-Laravel backends.
5. **`Locale\LocaleComparator`** / **`Locale\LocaleDiff`**: `compare(TranslationSource, reference, locales)` checks each locale's key set against the reference and returns missing/extra keys (the drift-warning feature).
6. **`Commands\GenerateTypesCommand`** (`typed-i18n:generate`) resolves a `TranslationSource` from the container, reads keys for the reference locale, writes the `.ts`, then reports drift. Options `--locale`, `--output`, and `--check` (compares the existing file against freshly generated types and fails with exit 1 if they differ or the file is missing, without writing). When the source is a `LaravelLangSource` whose `langPath` is missing, it fails (exit 1).

`config/typed-i18n.php` is deliberately all-`null`-by-default so it stays `config:cache`-safe across differing web roots. Every key resolves at runtime:

| Key | `null` resolves to |
| --- | --- |
| `lang_path` | `lang_path()` |
| `default_locale` | `config('app.fallback_locale')` |
| `locales` | every locale found in the lang path (dirs + `*.json`) |
| `output` | `resource_path('js/types/translations.d.ts')` |
| `include_vendor` | `true` |

The generated `.ts` contract is the spec, see `tests/fixtures/translations.d.ts`. When changing generator output, update that fixture; `tests/Unit/TypeScriptGeneratorTest.php` is a golden-file test.

## Testing notes

- **Pest 3** over Orchestra Testbench. `tests/Pest.php` binds `TestCase` **only to `tests/Feature`**, so unit tests (`tests/Unit`) are pure PHP and don't boot Laravel.
- `tests/Unit/ParseTest.php` defines global `createTempLang()`/`cleanupTempLang()`/`parseLang()` helpers (also used by `LocaleComparatorTest`); they build temp lang dirs on disk.
- `tests/Feature/GenerateTypesCommandTest.php` drives the command via `$this->artisan(...)`, pointing config at a temp lang dir and asserting on the written file + exit codes. Output is written with the `File` facade (not `Storage`), so tests use real temp dirs.
- `tests/ArchTest.php` forbids `dd`/`dump`/`ray` in `src/`.

## Conventions

- PHP 8.2+; domain classes use `declare(strict_types=1)`, constructor property promotion, `readonly`/`final`.
- Namespace root: `YuriiDiachuk\LaravelTypedI18n\` maps to `src/`.
- Supports Laravel 11/12 (`illuminate/contracts ^11|^12`, testbench `^9|^10`).
- The lang path is always resolved via `lang_path()`, never hardcode `resource_path('lang')` or `base_path('lang')`.

## Not yet built (intended next steps)

- Optional generated runtime helper (`trans()` with typed overloads) behind a config flag. Currently users wire the `Translations` interface to their own helper (see README).
- Optional `declare module` augmentation output for i18next / vue-i18n.
