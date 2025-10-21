# Quick Installation Guide

## Option 1: Local Package (Recommended for Development)

Since this package is in your `packages` directory, follow these steps:

### 1. Update your project's composer.json

Add this to your root `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/escarter/laravel-obfuscator"
        }
    ]
}
```

### 2. Require the package

```bash
composer require escarter/laravel-obfuscator:@dev
```

### 3. Publish configuration

```bash
php artisan vendor:publish --tag=obfuscator-config
```

This will create `config/obfuscator.php`.

### 4. Customize configuration (optional)

Edit `config/obfuscator.php` to match your needs:
- Adjust paths to obfuscate
- Add excluded files
- Configure backup settings
- Set protection levels

### 5. Run obfuscation

```bash
# Dry run first (preview only)
php artisan obfuscate:run --dry-run

# Actual obfuscation
php artisan obfuscate:run
```

## Option 2: Publish to Packagist (For Reuse Across Projects)

### 1. Create GitHub Repository

```bash
cd packages/escarter/laravel-obfuscator
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/escarter/laravel-obfuscator.git
git push -u origin main
```

### 2. Register on Packagist

1. Go to https://packagist.org
2. Click "Submit"
3. Enter your GitHub repository URL
4. Submit

### 3. Install via Composer

```bash
composer require escarter/laravel-obfuscator --dev
```

## Option 3: Manual Copy (Quick & Simple)

If you want to use this package in another Laravel project:

1. Copy the entire `packages/escarter/laravel-obfuscator` directory
2. Place it in the new project's `packages/escarter/` directory
3. Follow Option 1 steps

## Verification

After installation, verify the package is loaded:

```bash
php artisan list obfuscate
```

You should see:
```
obfuscate:run     Obfuscate Laravel application code
```

## First Run

### Important: Create a Backup First

Before running obfuscation for the first time:

```bash
# Create a manual backup
cp -R app app.backup
cp -R database database.backup
cp -R routes routes.backup
```

### Test with Dry Run

```bash
php artisan obfuscate:run --dry-run
```

Review the output to see what will be obfuscated.

### Run Obfuscation

```bash
php artisan obfuscate:run
```

### Test Your Application

```bash
php artisan serve
```

Visit your application and test all functionality.

## Troubleshooting

### Package not found

If you get "Package escarter/laravel-obfuscator not found":

```bash
composer update escarter/laravel-obfuscator
composer dump-autoload
```

### Command not available

If `php artisan obfuscate:run` doesn't exist:

```bash
php artisan clear-compiled
php artisan config:clear
composer dump-autoload
```

### Configuration not published

```bash
php artisan vendor:publish --tag=obfuscator-config --force
```

## Quick Reference

### Commands

```bash
# Dry run (preview only)
php artisan obfuscate:run --dry-run

# Obfuscate with backup
php artisan obfuscate:run

# Skip automatic backup
php artisan obfuscate:run --no-backup

# Skip Blade view cleaning
php artisan obfuscate:run --no-views

# Publish configuration
php artisan vendor:publish --tag=obfuscator-config
```

### Restore from Backup

```bash
# Find backup
ls -la | grep BACKUP_

# Restore
rm -rf app database routes resources
cp -R BACKUP_YYYYMMDDHHIISS/* .
```

## Next Steps

1. ✅ Customize `config/obfuscator.php` for your needs
2. ✅ Test with `--dry-run` first
3. ✅ Run obfuscation on a test environment
4. ✅ Verify application works correctly
5. ✅ Deploy to production

---

For detailed documentation, see [README.md](README.md)

