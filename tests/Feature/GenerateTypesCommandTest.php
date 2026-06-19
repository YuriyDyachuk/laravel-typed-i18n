<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->lang = sys_get_temp_dir().'/ti18n-lang-'.uniqid();
    $this->output = sys_get_temp_dir().'/ti18n-out-'.uniqid().'/translations.d.ts';

    File::ensureDirectoryExists($this->lang.'/en');

    config()->set('typed-i18n.lang_path', $this->lang);
    config()->set('typed-i18n.output', $this->output);
    config()->set('typed-i18n.default_locale', 'en');
});

afterEach(function () {
    File::deleteDirectory($this->lang);
    File::deleteDirectory(dirname($this->output));
});

it('generates a declaration file from translations', function () {
    File::put($this->lang.'/en/messages.php', '<?php return ["welcome" => "Hi :name"];');

    $this->artisan('typed-i18n:generate')->assertSuccessful();

    expect(File::exists($this->output))->toBeTrue()
        ->and(File::get($this->output))
        ->toContain("'messages.welcome': { name: string | number };")
        ->toContain('export type TranslationKey = keyof Translations;');
});

it('fails when the lang path does not exist', function () {
    config()->set('typed-i18n.lang_path', '/nonexistent/path');

    $this->artisan('typed-i18n:generate')->assertFailed();
});

it('warns about locale key drift', function () {
    File::ensureDirectoryExists($this->lang.'/de');
    File::put($this->lang.'/en/messages.php', '<?php return ["a" => "A", "b" => "B"];');
    File::put($this->lang.'/de/messages.php', '<?php return ["a" => "A"];');

    $this->artisan('typed-i18n:generate')
        ->expectsOutputToContain('messages.b')
        ->assertSuccessful();
});

it('respects the --locale and --output options', function () {
    File::ensureDirectoryExists($this->lang.'/fr');
    File::put($this->lang.'/fr/messages.php', '<?php return ["bonjour" => "Bonjour"];');

    $altOutput = sys_get_temp_dir().'/ti18n-alt-'.uniqid().'/out.d.ts';

    $this->artisan('typed-i18n:generate', ['--locale' => 'fr', '--output' => $altOutput])
        ->assertSuccessful();

    expect(File::get($altOutput))->toContain("'messages.bonjour': {};");

    File::deleteDirectory(dirname($altOutput));
});

it('passes --check when the generated file is up to date', function () {
    File::put($this->lang.'/en/messages.php', '<?php return ["welcome" => "Hi :name"];');

    $this->artisan('typed-i18n:generate')->assertSuccessful();

    $this->artisan('typed-i18n:generate', ['--check' => true])
        ->expectsOutputToContain('up to date')
        ->assertSuccessful();
});

it('fails --check when the generated file is out of date', function () {
    File::put($this->lang.'/en/messages.php', '<?php return ["welcome" => "Hi :name"];');
    $this->artisan('typed-i18n:generate')->assertSuccessful();

    $before = File::get($this->output);

    File::put($this->lang.'/en/messages.php', '<?php return ["welcome" => "Hi :name", "bye" => "Bye"];');

    $this->artisan('typed-i18n:generate', ['--check' => true])->assertFailed();

    expect(File::get($this->output))->toBe($before);
});

it('fails --check when the generated file does not exist', function () {
    File::put($this->lang.'/en/messages.php', '<?php return ["welcome" => "Hi :name"];');

    $this->artisan('typed-i18n:generate', ['--check' => true])->assertFailed();

    expect(File::exists($this->output))->toBeFalse();
});
