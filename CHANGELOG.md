# Changelog

All notable changes to `laravel-typed-i18n` will be documented in this file.

## 1.0.1 - 2026-06-19

- Add discoverability keywords (typescript, i18n, translations, types, codegen) for Packagist.
- Add a `tests-passed` rollup job to CI so branch protection can require a single stable check.

## 1.0.0 - 2026-06-19

First public release.

- `typed-i18n:generate` command that turns Laravel translation files into a TypeScript declaration file.
- Parses `{locale}/*.php` groups, `{locale}.json` phrases, and `vendor/{package}/{locale}/*.php` files.
- Required params per key, with a `count: number` on pluralized strings and case-normalized placeholders.
- Locale drift detection that reports missing and extra keys against the reference locale.
- `TranslationSource` interface so the parser can be swapped for other backends later.
