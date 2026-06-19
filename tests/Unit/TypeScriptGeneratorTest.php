<?php

declare(strict_types=1);

use YuriiDiachuk\LaravelTypedI18n\Generator\TypeScriptGenerator;
use YuriiDiachuk\LaravelTypedI18n\Parser\TranslationKey;

it('matches the golden declaration file', function () {
    $keys = [
        new TranslationKey('messages.welcome', ['name'], false),
        new TranslationKey('messages.profile.updated', [], false),
        new TranslationKey('cart.items', [], true),
        new TranslationKey('orders.shipped', ['city'], true),
        new TranslationKey('Save', [], false),
        new TranslationKey('Hello :name', ['name'], false),
    ];

    $output = (new TypeScriptGenerator)->generate($keys);

    expect($output)->toBe(file_get_contents(__DIR__.'/../fixtures/translations.d.ts'));
});

it('sorts keys deterministically regardless of input order', function () {
    $a = (new TypeScriptGenerator)->generate([
        new TranslationKey('b.key', [], false),
        new TranslationKey('a.key', [], false),
    ]);

    $b = (new TypeScriptGenerator)->generate([
        new TranslationKey('a.key', [], false),
        new TranslationKey('b.key', [], false),
    ]);

    expect($a)->toBe($b)
        ->and(strpos($a, "'a.key'"))->toBeLessThan(strpos($a, "'b.key'"));
});

it('deduplicates keys defined more than once', function () {
    $output = (new TypeScriptGenerator)->generate([
        new TranslationKey('dup', [], false),
        new TranslationKey('dup', ['name'], false),
    ]);

    expect(substr_count($output, "'dup'"))->toBe(1)
        ->and($output)->toContain("'dup': { name: string | number };");
});

it('escapes single quotes in phrase keys', function () {
    $output = (new TypeScriptGenerator)->generate([
        new TranslationKey("It's here", [], false),
    ]);

    expect($output)->toContain("'It\\'s here': {};");
});

it('escapes control characters in phrase keys', function () {
    $output = (new TypeScriptGenerator)->generate([
        new TranslationKey("Line1\nLine2", [], false),
    ]);

    expect($output)->toContain("'Line1\\nLine2': {};");
});
