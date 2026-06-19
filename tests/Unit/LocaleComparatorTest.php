<?php

declare(strict_types=1);

use YuriiDiachuk\LaravelTypedI18n\Locale\LocaleComparator;
use YuriiDiachuk\LaravelTypedI18n\Source\LaravelLangSource;

it('reports keys missing from a non-reference locale', function () {
    $dir = createTempLang([
        'en/messages.php' => '<?php return ["a" => "A", "b" => "B", "c" => "C"];',
        'de/messages.php' => '<?php return ["a" => "A"];',
    ]);

    try {
        $diffs = (new LocaleComparator)->compare(new LaravelLangSource($dir), 'en', ['en', 'de']);
    } finally {
        cleanupTempLang($dir);
    }

    expect($diffs)->toHaveKey('de')
        ->and($diffs['de']->missing)->toBe(['messages.b', 'messages.c'])
        ->and($diffs['de']->extra)->toBe([]);
});

it('reports keys that exist only in a non-reference locale', function () {
    $dir = createTempLang([
        'en/messages.php' => '<?php return ["a" => "A"];',
        'de/messages.php' => '<?php return ["a" => "A", "z" => "Z"];',
    ]);

    try {
        $diffs = (new LocaleComparator)->compare(new LaravelLangSource($dir), 'en', ['en', 'de']);
    } finally {
        cleanupTempLang($dir);
    }

    expect($diffs['de']->extra)->toBe(['messages.z'])
        ->and($diffs['de']->missing)->toBe([]);
});

it('ignores vendor keys when includeVendor is false', function () {
    $dir = createTempLang([
        'en/messages.php' => '<?php return ["a" => "A"];',
        'de/messages.php' => '<?php return ["a" => "A"];',
        'vendor/acme/en/messages.php' => '<?php return ["x" => "X"];',
    ]);

    try {
        $withVendor = (new LocaleComparator)->compare(
            new LaravelLangSource($dir, includeVendor: true), 'en', ['en', 'de']
        );
        $withoutVendor = (new LocaleComparator)->compare(
            new LaravelLangSource($dir, includeVendor: false), 'en', ['en', 'de']
        );
    } finally {
        cleanupTempLang($dir);
    }

    expect($withVendor)->toHaveKey('de')
        ->and($withVendor['de']->missing)->toBe(['acme::messages.x'])
        ->and($withoutVendor)->toBe([]);
});

it('returns no diffs when locales are in sync', function () {
    $dir = createTempLang([
        'en/messages.php' => '<?php return ["a" => "A"];',
        'de/messages.php' => '<?php return ["a" => "A"];',
    ]);

    try {
        $diffs = (new LocaleComparator)->compare(new LaravelLangSource($dir), 'en', ['en', 'de']);
    } finally {
        cleanupTempLang($dir);
    }

    expect($diffs)->toBe([]);
});
