<?php

declare(strict_types=1);

use YuriiDiachuk\LaravelTypedI18n\Parser\LangFileParser;

function createTempLang(array $files): string
{
    $dir = sys_get_temp_dir().'/typed-i18n-'.uniqid();
    mkdir($dir, recursive: true);

    foreach ($files as $path => $content) {
        $fullPath = $dir.'/'.$path;
        $dirName = dirname($fullPath);

        if (! is_dir($dirName)) {
            mkdir($dirName, recursive: true);
        }

        file_put_contents($fullPath, $content);
    }

    return $dir;
}

function cleanupTempLang(string $dir): void
{
    if (! is_dir($dir)) {
        return;
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $file) {
        $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    }

    rmdir($dir);
}

function parseLang(array $files, string $locale = 'en'): array
{
    $dir = createTempLang($files);

    try {
        return (new LangFileParser)->parse($dir, $locale);
    } finally {
        cleanupTempLang($dir);
    }
}

it('parses simple php translation file', function () {
    $keys = parseLang([
        'en/messages.php' => '<?php return ["welcome" => "Welcome"];',
    ]);

    expect($keys)->toHaveCount(1)
        ->and($keys[0]->key)->toBe('messages.welcome')
        ->and($keys[0]->placeholders)->toBe([])
        ->and($keys[0]->isPlural)->toBeFalse();
});

it('flattens nested keys with dot notation', function () {
    $keys = parseLang([
        'en/messages.php' => '<?php return [
            "auth" => [
                "login" => "Login",
                "logout" => "Logout",
            ],
        ];',
    ]);

    expect($keys)->toHaveCount(2)
        ->and($keys[0]->key)->toBe('messages.auth.login')
        ->and($keys[1]->key)->toBe('messages.auth.logout');
});

it('extracts placeholders from translation string', function () {
    $keys = parseLang([
        'en/messages.php' => '<?php return ["welcome" => "Hello :name, your role is :role"];',
    ]);

    expect($keys[0]->placeholders)->toBe(['name', 'role']);
});

it('deduplicates placeholders', function () {
    $keys = parseLang([
        'en/messages.php' => '<?php return ["msg" => ":name said hi to :name"];',
    ]);

    expect($keys[0]->placeholders)->toBe(['name']);
});

it('normalizes placeholder case to a single param', function (string $value) {
    $keys = parseLang([
        'en/messages.php' => '<?php return ["msg" => "'.$value.'"];',
    ]);

    expect($keys[0]->placeholders)->toBe(['name']);
})->with([
    'lower then ucfirst' => ['Hi :name, bye :Name'],
    'lower then upper' => ['Hi :name, bye :NAME'],
    'all three variants' => [':name :Name :NAME'],
]);

it('does not treat colons in free text as placeholders', function (string $value) {
    $keys = parseLang([
        'en/messages.php' => '<?php return ["msg" => "'.$value.'"];',
    ]);

    expect($keys[0]->placeholders)->toBe([]);
})->with([
    'time' => ['Meeting at 12:30'],
    'url' => ['See https://example.com'],
]);

it('detects pluralization and requires a count param', function () {
    $keys = parseLang([
        'en/messages.php' => '<?php return [
            "items" => "{1} :count item|[2,*] :count items",
        ];',
    ]);

    expect($keys[0]->isPlural)->toBeTrue()
        ->and($keys[0]->placeholders)->toBe([]);
});

it('treats a simple two-segment string as plural', function () {
    $keys = parseLang([
        'en/messages.php' => '<?php return ["apples" => "apple|apples"];',
    ]);

    expect($keys[0]->isPlural)->toBeTrue();
});

it('keeps non-count placeholders on plural strings', function () {
    $keys = parseLang([
        'en/messages.php' => '<?php return [
            "shipped" => "{1} :count order to :city|[2,*] :count orders to :city",
        ];',
    ]);

    expect($keys[0]->isPlural)->toBeTrue()
        ->and($keys[0]->placeholders)->toBe(['city']);
});

it('does not treat a single segment as plural', function () {
    $keys = parseLang([
        'en/messages.php' => '<?php return ["msg" => "no pipes here"];',
    ]);

    expect($keys[0]->isPlural)->toBeFalse();
});

it('parses json translation file', function () {
    $keys = parseLang([
        'en.json' => json_encode([
            'Save' => 'Save',
            'Hello :name' => 'Hello :name',
        ]),
    ]);

    expect($keys)->toHaveCount(2)
        ->and($keys[0]->key)->toBe('Save')
        ->and($keys[1]->key)->toBe('Hello :name')
        ->and($keys[1]->placeholders)->toBe(['name']);
});

it('parses vendor namespaced php files', function () {
    $keys = parseLang([
        'vendor/acme/en/messages.php' => '<?php return ["welcome" => "Hi :name"];',
    ]);

    expect($keys)->toHaveCount(1)
        ->and($keys[0]->key)->toBe('acme::messages.welcome')
        ->and($keys[0]->placeholders)->toBe(['name']);
});

it('defaults the locale to en', function () {
    $keys = parseLang([
        'en/messages.php' => '<?php return ["welcome" => "Welcome"];',
    ]);

    expect($keys[0]->key)->toBe('messages.welcome');
});

it('returns empty array when locale directory does not exist', function () {
    $keys = parseLang([], 'fr');

    expect($keys)->toBe([]);
});

it('skips non-string values', function () {
    $keys = parseLang([
        'en/messages.php' => '<?php return [
            "title" => "Hello",
            "count" => 42,
            "flag"  => true,
        ];',
    ]);

    expect($keys)->toHaveCount(1)
        ->and($keys[0]->key)->toBe('messages.title');
});
