<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Obfuscation Paths
    |--------------------------------------------------------------------------
    |
    | Define which directories should be obfuscated. These paths are relative
    | to your Laravel project root directory.
    |
    */
    'paths' => [
        'app',
        'database',
        'routes',
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Files
    |--------------------------------------------------------------------------
    |
    | Files containing these strings in their basename will be skipped during
    | obfuscation. This is useful for preserving critical Laravel files.
    |
    */
    'excluded_files' => [
        'Kernel.php',
        'Handler.php',
        'Helpers.php',
        'ServiceProvider.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | Blade View Cleaning
    |--------------------------------------------------------------------------
    |
    | Enable or disable comment removal from Blade views. When enabled,
    | HTML and Blade comments will be removed from all .blade.php files.
    |
    */
    'clean_blade_views' => true,

    /*
    |--------------------------------------------------------------------------
    | Blade Views Path
    |--------------------------------------------------------------------------
    |
    | Path to Blade views directory (relative to project root)
    |
    */
    'views_path' => 'resources/views',

    /*
    |--------------------------------------------------------------------------
    | Backup Settings
    |--------------------------------------------------------------------------
    |
    | Configure automatic backup creation before obfuscation
    |
    */
    'backup' => [
        'enabled' => true,
        'prefix' => 'BACKUP_',
        'timestamp_format' => 'YmdHis',
        'paths' => [
            'app',
            'database',
            'routes',
            'resources',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption Settings
    |--------------------------------------------------------------------------
    |
    | Configure the encryption method and key generation
    |
    */
    'encryption' => [
        'method' => 'xor', // XOR encryption with base64 encoding
        'key_length' => 16, // bytes
    ],

    /*
    |--------------------------------------------------------------------------
    | Variable Name Obfuscation
    |--------------------------------------------------------------------------
    |
    | Enable Unicode character replacement for variable and method names
    |
    */
    'unicode_names' => true,

    /*
    |--------------------------------------------------------------------------
    | Protected Variable Names
    |--------------------------------------------------------------------------
    |
    | Variable names that should NOT be obfuscated
    |
    */
    'protected_variables' => [
        'this',
        'request',
        'user',
        'auth',
        'session',
        '_GET',
        '_POST',
        '_SERVER',
        '_ENV',
        '_FILES',
        '_COOKIE',
        '_REQUEST',
    ],

    /*
    |--------------------------------------------------------------------------
    | Protected Method Names
    |--------------------------------------------------------------------------
    |
    | Method names that should NOT be obfuscated (Laravel & PHP magic methods)
    |
    */
    'protected_methods' => [
        'boot',
        'register',
        'handle',
        'mount',
        'hydrate',
        'dehydrate',
        'render',
        'updated',
        'updating',
        'validate',
        'redirect',
        'save',
        'update',
        'create',
        'find',
        'findOrFail',
        'up',
        'down',
        '__construct',
        '__get',
        '__set',
        '__call',
        '__toString',
        '__invoke',
    ],

    /*
    |--------------------------------------------------------------------------
    | Protected Property Names
    |--------------------------------------------------------------------------
    |
    | Property names that should NOT be obfuscated (Laravel conventions)
    |
    */
    'protected_properties' => [
        'middleware',
        'middlewareGroups',
        'middlewareAliases',
        'fillable',
        'guarded',
        'hidden',
        'casts',
        'table',
        'primaryKey',
        'timestamps',
        'listeners',
        'queryString',
        'paginationTheme',
        'rules',
        'dates',
        'appends',
        'with',
        'perPage',
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Disabling
    |--------------------------------------------------------------------------
    |
    | Configure debug disabling features to prevent debugging of obfuscated code
    |
    */
    'debug_disabling' => [
        'enabled' => true,
        'disable_error_reporting' => true,
        'disable_xdebug' => true,
        'disable_debug_backtrace' => true,
        'disable_var_dump' => true,
        'disable_print_r' => true,
        'disable_die_exit' => true,
        'inject_anti_debug_code' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Output Settings
    |--------------------------------------------------------------------------
    |
    | Configure the output format and verbosity
    |
    */
    'output' => [
        'verbose' => true,
        'progress_interval' => 20, // Show progress every N files
        'show_encryption_key' => true, // Display the encryption key after completion
    ],
];

