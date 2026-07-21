# Soflyy Addon Installer SDK

A lightweight SDK for Soflyy Pro plugins to install and manage free plugin dependencies from WordPress.org.

## Features

* **Auto Install**: Installs required free addons from WordPress.org
* **Version Check**: Verifies and enforces minimum version
* **Auto Activation**: Activates addons after install
* **Deactivation Lock**: Optional protection against accidental deactivation
* **Import Blocking**: Prevents imports when dependencies are missing (WP All Import hooks)
* **CLI Support**: WP-CLI compatibility with immediate dependency resolution
* **Multisite Support**: Handles network installs and permissions
* **Auto-Update Sync**: Copies update settings from pro to free plugins
* **Silent Operation**: Non-intrusive install process
* **Flexible Detection**: Supports version checks via constants or plugin headers

## Installation

This SDK is distributed as a private Composer package with per-plugin namespace isolation via [Strauss](https://github.com/BrianHenryIE/strauss).

### 1. Add to your plugin's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:soflyy/pmxi-addon-installer-sdk.git"
        }
    ],
    "require": {
        "soflyy/addon-installer-sdk": "^1.1"
    },
    "require-dev": {
        "brianhenryie/strauss": "^0.26"
    },
    "autoload": {
        "classmap": [
            "vendor-prefixed/"
        ]
    },
    "extra": {
        "strauss": {
            "target_directory": "vendor-prefixed/",
            "namespace_prefix": "YOUR_PREFIX_Vendor\\",
            "classmap_prefix": "YOUR_PREFIX_Vendor_",
            "packages": [
                "soflyy/addon-installer-sdk"
            ]
        }
    },
    "scripts": {
        "strauss": "vendor/bin/strauss",
        "post-install-cmd": "@strauss",
        "post-update-cmd": "@strauss"
    }
}
```

Replace `YOUR_PREFIX` with your plugin's constant prefix (e.g., `PMAE`, `PMAI`, `PMXI`, `PMXE`).

### 2. Run Composer:

```bash
composer install
composer dump-autoload
```

Strauss runs automatically, creating `vendor-prefixed/` with the namespace-isolated SDK.

### 3. Commit `vendor-prefixed/` to your repo.

## Basic Usage

```php
use YOUR_PREFIX_Vendor\Soflyy\AddonInstaller\AddonInstaller;

$addon_installer = new AddonInstaller([
    'addon_name'           => 'WP All Import - ACF Add-On Free',
    'addon_slug'           => 'csv-xml-import-for-acf',
    'addon_author'         => 'Soflyy',
    'minimum_version'      => '1.0.4',
    'pro_plugin_name'      => 'WP All Import - ACF Add-On Pro',
    'pro_plugin_file'      => __FILE__,
    'textdomain'           => 'wp_all_import_acf_add_on',
    'version_constant'     => 'PMAI_VERSION',
    'edition_constant'     => 'PMAI_EDITION',
    'expected_edition'     => 'free',
    'disable_deactivation' => true,
]);

$addon_installer->install_addon_from_repository();
```

## Namespace Prefix Convention

Each consuming plugin uses its own prefix to avoid class collisions when multiple plugins are active:

| Plugin | Prefix |
|--------|--------|
| WP All Import Pro | `PMXI_Vendor\` |
| WP All Export Pro | `PMXE_Vendor\` |
| ACF Import Add-On Pro | `PMAI_Vendor\` |
| ACF Export Add-On Pro | `PMAE_Vendor\` |
| WooCommerce Import Add-On Pro | `PMWI_Vendor\` |

## Configuration Options

| Option | Type | Required | Description |
|--------|------|----------|-------------|
| addon_name | string | Yes | Display name of the free addon (e.g., 'ACF Add-On Free') |
| addon_slug | string | Yes | WordPress.org plugin slug (e.g., 'csv-xml-import-for-acf') |
| addon_author | string | Yes | Plugin author name for identification (e.g., 'Soflyy') |
| minimum_version | string | Yes | Minimum required version (e.g., '1.0.4') |
| pro_plugin_name | string | Yes | Name of your pro plugin (e.g., 'WP All Import - ACF Add-On Pro') |
| pro_plugin_file | string | Yes | Path to your pro plugin's main file (usually __FILE__) |
| textdomain | string | Yes | Text domain for translations |
| version_constant | string | No | Constant name that holds the addon version (e.g., 'PMAI_VERSION') |
| edition_constant | string | No | Constant name that holds the edition type (e.g., 'PMAI_EDITION') |
| expected_edition | string | No | Expected edition value (default: 'free') |
| free_plugin_file | string | No | Free plugin filename if different from plugin.php |
| disable_deactivation | bool | No | Whether to prevent deactivation of the free plugin (default: false) |
| send_email_alert | bool | No | Whether to send email alerts for failures (default: true) |

## Advanced Usage

### Import Blocking

The SDK includes hooks for `pmxi_before_xml_import` and `pmxi_before_post_import` that block WP All Import operations when dependencies are missing. These hooks are harmless for non-import plugins (they simply never fire).

### CLI Support

Full WP-CLI compatibility with automatic dependency resolution before operations.

### Check Addon Status

```php
if (!$addon_installer->is_addon_up_to_date()) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>Please update the required addon.</p></div>';
    });
}
```

### Version Detection Methods

**Option 1: Constants (recommended)**

```php
'version_constant' => 'PMAI_VERSION',
'edition_constant' => 'PMAI_EDITION',
'expected_edition' => 'free'
```

**Option 2: Plugin headers**
Omit constants and detection will fall back to plugin headers automatically.

### Prevent Deactivation

```php
'disable_deactivation' => true
```

Disables the deactivate link in plugin list and prevents CLI deactivation.

### Email Notifications

```php
'send_email_alert' => true  // Default
```

Sends admin email alerts when installation fails.

### Multisite Support

* Detects network activation
* Applies addon activation site-wide
* Adds appropriate notices and checks

### Auto-Update Sync

Inherits auto-update settings from the pro plugin to its addon.

## Updating the SDK

When a fix is made to this repo:

1. Commit and tag a new version (e.g., `git tag v1.2.0`)
2. In each consuming plugin: `composer update soflyy/addon-installer-sdk`
3. Strauss runs automatically, regenerating `vendor-prefixed/`
4. Commit the updated `vendor-prefixed/` and `composer.lock`
5. Test the plugin

## Requirements

* PHP 7.4+
* Composer autoloader
* `install_plugins` and `activate_plugins` capabilities for automatic installation

## Troubleshooting

### Addon not installing

* Check slug and version on WordPress.org
* Confirm write permissions
* Verify user has install_plugins capability

### Version not detected

* Verify constants or plugin header info
* Ensure addon is active
* Check if constants are defined correctly

### Import operations blocked

* Confirm dependency is properly installed and active
