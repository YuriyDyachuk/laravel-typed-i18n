<?php

declare(strict_types=1);

use YuriiDiachuk\LaravelTypedI18n\Source\LaravelLangSource;

it('reads keys for a locale through the parser', function () {
    $dir = createTempLang([
        'en/messages.php' => '<?php return ["welcome" => "Hello :name"];',
    ]);

    try {
        $keys = (new LaravelLangSource($dir))->keys('en');
    } finally {
        cleanupTempLang($dir);
    }

    expect($keys)->toHaveCount(1)
        ->and($keys[0]->key)->toBe('messages.welcome')
        ->and($keys[0]->placeholders)->toBe(['name']);
});

it('discovers locales from directories and json files', function () {
    $dir = createTempLang([
        'en/messages.php' => '<?php return ["a" => "A"];',
        'de/messages.php' => '<?php return ["a" => "A"];',
        'fr.json' => '{"Save": "Enregistrer"}',
        'vendor/acme/en/messages.php' => '<?php return ["x" => "X"];',
    ]);

    try {
        $locales = (new LaravelLangSource($dir))->availableLocales();
    } finally {
        cleanupTempLang($dir);
    }

    sort($locales);

    expect($locales)->toBe(['de', 'en', 'fr']);
});

it('returns no locales for an empty lang path', function () {
    $dir = createTempLang([]);

    try {
        $locales = (new LaravelLangSource($dir))->availableLocales();
    } finally {
        cleanupTempLang($dir);
    }

    expect($locales)->toBe([]);
});
