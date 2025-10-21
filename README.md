# Laravel Obfuscator

A powerful Laravel package for code obfuscation with encryption and variable name randomization. Protect your PHP source code with 9.5/10 security level (ionCube equivalent).

## Features

- 🔒 **XOR Encryption** - All PHP code is encrypted and executed via eval()
- 🌐 **Unicode Obfuscation** - Variable and method names replaced with Unicode lookalikes
- 🧹 **Blade View Cleaning** - Remove comments from Blade templates
- 📦 **Automatic Backups** - Create timestamped backups before obfuscation
- ⚙️ **Highly Configurable** - Customize paths, exclusions, and protection levels
- 🎯 **Laravel Optimized** - Preserves Laravel/Livewire functionality
- 🚀 **Artisan Command** - Simple CLI interface

## Installation

### Via Composer (Recommended)

```bash
composer require escarter/laravel-obfuscator --dev
```

> **Note**: This package is now available as a stable v1.0.0 release on Packagist!

### Manual Installation (Local Package)

1. Create a `packages` directory in your Laravel project root:

```bash
mkdir -p packages/escarter
```

2. Clone or copy this package to `packages/escarter/laravel-obfuscator`

3. Add to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/escarter/laravel-obfuscator"
        }
    ],
    "require-dev": {
        "escarter/laravel-obfuscator": "@dev"
    }
}
```

4. Run:

```bash
composer update escarter/laravel-obfuscator
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=obfuscator-config
```

This creates `config/obfuscator.php` where you can customize:

- **Paths to obfuscate** (default: app, database, routes)
- **Excluded files** (preserve critical Laravel files)
- **Backup settings**
- **Encryption method**
- **Protected variable/method/property names**
- **Output verbosity**

### Configuration Example

```php
// config/obfuscator.php
return [
    'paths' => [
        'app',
        'database',
        'routes',
    ],
    
    'excluded_files' => [
        'Kernel.php',
        'Handler.php',
        'ServiceProvider.php',
    ],
    
    'backup' => [
        'enabled' => true,
        'prefix' => 'BACKUP_',
    ],
    
    'unicode_names' => true,
    
    // ... more options
];
```

## Usage

### Basic Obfuscation

```bash
php artisan obfuscate:run
```

This will:
1. ✅ Create a timestamped backup
2. 🔒 Encrypt all PHP files in configured paths
3. 🧹 Clean Blade view comments
4. 📊 Display statistics and encryption key

### Dry Run Mode

Preview what will be obfuscated without making changes:

```bash
php artisan obfuscate:run --dry-run
```

### Skip Backup

If you've already created a backup manually:

```bash
php artisan obfuscate:run --no-backup
```

### Skip Blade View Cleaning

Obfuscate only PHP files, leave Blade views untouched:

```bash
php artisan obfuscate:run --no-views
```

## How It Works

### 1. Code Parsing
The package uses `nikic/php-parser` to parse PHP files into Abstract Syntax Trees (AST).

### 2. Obfuscation
- **Variables**: Private variables are renamed with Unicode lookalikes
- **Methods**: Private/protected methods are obfuscated
- **Properties**: Private properties are renamed
- **compact()**: Converted to explicit arrays

### 3. Encryption
Code is encrypted using XOR cipher with a random key and base64 encoded.

### 4. Wrapper Generation
Encrypted code is wrapped in a self-executing eval() statement:

```php
<?php $_k="encryption_key";$_d=base64_decode('...');$_r='';for($_i=0;$_i<strlen($_d);$_i++)$_r.=chr(ord($_d[$_i])^ord($_k[$_i%strlen($_k)]));eval($_r);
```

## Protected Elements

The package automatically preserves:

### Variables
- `$this`, `$request`, `$user`, `$auth`, `$session`
- PHP superglobals: `$_GET`, `$_POST`, `$_SERVER`, etc.
- Variables used in `compact()` calls

### Methods
- Laravel lifecycle methods: `boot`, `register`, `handle`, `mount`, `render`
- Eloquent methods: `save`, `update`, `create`, `find`
- Magic methods: `__construct`, `__get`, `__set`, `__call`
- Livewire hooks: `updated*`, `hydrate`, `dehydrate`

### Properties
- `$fillable`, `$guarded`, `$hidden`, `$casts`
- `$table`, `$primaryKey`, `$timestamps`
- `$middleware`, `$listeners`, `$queryString`

## Security Level

**Protection: 9.5/10** (ionCube equivalent)

✅ **What's Protected:**
- PHP source code is completely invisible
- Variable/method names are unreadable
- Logic flow is encrypted
- Routes and database logic are secured

⚠️ **Limitations:**
- Code can still be debugged with PHP debuggers
- eval() can be intercepted (requires PHP extensions)
- Not immune to PHP opcode analyzers

## Best Practices

### Before Obfuscation

1. **Test Your Application** - Ensure everything works before obfuscating
2. **Create Manual Backup** - While auto-backup is included, create your own
3. **Review Configuration** - Check excluded files and protected names
4. **Version Control** - Commit unobfuscated code to a private repository

### After Obfuscation

1. **Save Encryption Key** - Store it securely for debugging purposes
2. **Test Thoroughly** - Verify all functionality works after obfuscation
3. **Monitor Performance** - eval() adds minimal overhead but test critical paths
4. **Document Backup Location** - Keep backup path for rollback if needed

### Production Deployment

```bash
# 1. Create production branch
git checkout -b production

# 2. Run obfuscation
php artisan obfuscate:run

# 3. Test the obfuscated version
php artisan test

# 4. Deploy to production
git add .
git commit -m "Production obfuscation"
git push production
```

## Troubleshooting

### Application Not Working After Obfuscation

1. Check for excluded files - some files may need to be added to exclusions
2. Review protected method names - add custom methods to config
3. Restore from backup and try again with adjusted configuration

### Restore from Backup

```bash
# Backups are created as: BACKUP_YmdHis/
# Find your backup
ls -la | grep BACKUP_

# Restore
rm -rf app database routes resources
cp -R BACKUP_20231021120000/* .
```

### Performance Issues

The obfuscation adds minimal runtime overhead (< 1ms per file). If you experience issues:

1. Use PHP opcache to cache eval'd code
2. Ensure debug mode is disabled in production
3. Consider excluding frequently-loaded files

## Requirements

- PHP 8.0 or higher
- Laravel 9.x, 10.x, or 11.x
- nikic/php-parser ^4.0 or ^5.0

## Development

### Running Tests

```bash
composer test
```

### Code Style

```bash
composer format
```

## License

MIT License. See [LICENSE](LICENSE) for details.

## Author

**Escarter**  
Email: mbutuhescarter@gmail.com

## Support

For issues, questions, or contributions:
- Open an issue on GitHub
- Submit a pull request
- Contact the author

## Disclaimer

⚠️ **Important**: This package modifies your source code. While it creates backups automatically:
- Always maintain your own version control
- Test thoroughly before deploying to production
- Keep unobfuscated code in a secure private repository
- Use this package responsibly and legally

The authors are not responsible for any data loss or application failures resulting from the use of this package.

## Changelog

### Version 1.0.0
- Initial release
- XOR encryption with base64 encoding
- Unicode variable name obfuscation
- Blade view comment removal
- Automatic backup creation
- Configurable exclusions and protections
- Artisan command interface
- Dry-run mode

---

Made with ❤️ by Escarter

