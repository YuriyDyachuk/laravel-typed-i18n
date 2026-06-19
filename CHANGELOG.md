# Changelog

All notable changes to `laravel-typed-i18n` will be documented in this file.

## 1.0.0 - 2026-06-19

First public release.

- `typed-i18n:generate` command that turns Laravel translation files into a TypeScript declaration file.
- Parses `{locale}/*.php` groups, `{locale}.json` phrases, and `vendor/{package}/{locale}/*.php` files.
- Required params per key, with a `count: number` on pluralized strings and case-normalized placeholders.
- Locale drift detection that reports missing and extra keys against the reference locale.
- `TranslationSource` interface so the parser can be swapped for other backends later.
