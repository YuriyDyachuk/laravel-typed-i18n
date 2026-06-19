# Changelog

All notable changes to `laravel-typed-i18n` will be documented in this file.

## v1.1.0 - 2026-06-19

### Added

- `typed-i18n:generate --check`: compares the committed declaration file against freshly generated types and exits non-zero **without writing** when they differ or the file is missing. Use it in CI to fail the build when translations changed but the `.d.ts` was not regenerated.
  
  ```bash
  php artisan typed-i18n:generate --check
  
  ```

No breaking changes.

## v1.0.1 - 2026-06-19

Maintenance release.

- Add discoverability keywords (`typescript`, `i18n`, `translations`, `types`, `codegen`) so the package surfaces in Packagist search.
- Add a `tests-passed` rollup CI job so branch protection can require a single stable check instead of every matrix leg.
- Tidy the changelog into a single coherent release section.

No functional changes to the generator.

## v1.0.0 - 2026-06-19

First public release of **laravel-typed-i18n**.

Turn your Laravel translation files into a TypeScript declaration file, so the frontend gets autocomplete on keys and type-checked params.

### Highlights

- `typed-i18n:generate` command: `lang files -> TranslationSource -> TranslationKey[] -> .ts`
- Parses `{locale}/*.php` groups, `{locale}.json` phrases and `vendor/{package}/{locale}/*.php` files
- Required params per key, with `count: number` on pluralized strings and case-normalized placeholders
- Locale drift detection against the reference locale
- `TranslationSource` seam for future non-Laravel backends

### Compatibility

- PHP 8.2+
- Laravel 11 / 12
