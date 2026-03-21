# Setup

## First-time installation

After cloning the repository, run the following from the plugin directory:

```bash
composer install
```

This installs CMB2 and PhpSpreadsheet into the `vendor/` directory.
PHP 8.3 or higher, WordPress 6.9.4 or higher, and Composer must be available on your system.

## Why vendor is tracked in git

CMB2 and PhpSpreadsheet are bundled dependencies — they are shipped as part of the plugin and must be present for the plugin to function. They are explicitly unignored in `.gitignore`.
