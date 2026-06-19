# Changelog

All notable changes to `laravel-typed-i18n` will be documented in this file.

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

🤖 Generated with [Claude Code](https://claude.com/claude-code)

## 1.0.0 - 2026-06-19

First public release.

- `typed-i18n:generate` command that turns Laravel translation files into a TypeScript declaration file.
- Parses `{locale}/*.php` groups, `{locale}.json` phrases, and `vendor/{package}/{locale}/*.php` files.
- Required params per key, with a `count: number` on pluralized strings and case-normalized placeholders.
- Locale drift detection that reports missing and extra keys against the reference locale.
- `TranslationSource` interface so the parser can be swapped for other backends later.
