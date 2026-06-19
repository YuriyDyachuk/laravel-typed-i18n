<?php

// config for YuriiDiachuk/LaravelTypedI18n
return [

    /*
     * Directory holding the Laravel translation files. When null, the package
     * resolves the application's lang path via lang_path() (version-agnostic).
     */
    'lang_path' => null,

    /*
     * The reference locale whose keys define the generated types. Other locales
     * are compared against it and drift is reported. When null, the app's
     * fallback locale (config('app.fallback_locale')) is used.
     */
    'default_locale' => null,

    /*
     * Locales to validate against the reference. When null, every locale found
     * in the lang path (directories and *.json files) is checked.
     *
     * @var string[]|null
     */
    'locales' => null,

    /*
     * Where the generated TypeScript declaration file is written. When null, it
     * defaults to resource_path('js/types/translations.d.ts'). Kept null-by-default
     * so the config stays `config:cache`-safe across differing web roots.
     */
    'output' => null,

    /*
     * Whether to include vendor package translations
     * (lang/vendor/{package}/{locale}/*.php → package::group.key).
     */
    'include_vendor' => true,

];
