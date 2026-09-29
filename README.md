# License Example Plugin — SDK development

Example WordPress plugin that integrates the official [WordPress-SDK](https://github.com/License-Bridge/WordPress-SDK). Use it to test checkout, inline billing, updates, and **imported-license OAuth provisioning**.

## Local layout

```
projects/
├── lb/
│   ├── WordPress-SDK/              ← edit SDK here, push to GitHub
│   └── license-example-plugin/     ← this plugin
└── wordpress/
    └── wp-content/plugins/license-example-plugin/  → symlink to license-example-plugin
```

## Setup

### 1. Composer

For local SDK development, add a path repository in `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "../WordPress-SDK",
        "options": {
            "symlink": true
        }
    }
],
"require": {
    "license-bridge/wordpress-sdk": "^2.0"
}
```

Then install:

```bash
cd license-example-plugin
composer install
```

For production / Packagist-only installs, use `composer require license-bridge/wordpress-sdk` without the path repository.

### 2. WordPress plugin symlink (Docker)

From the host, symlink the plugin into WordPress (use a **relative** path so Docker can resolve it):

```bash
cd wordpress/wp-content/plugins
ln -s ../../../lb/license-example-plugin license-example-plugin
```

Inside the PHP container, `api.lb.test` must resolve (add `api.lb.test:host-gateway` to `phpfpm` `extra_hosts` in docker-compose).

### 3. Configure `plugin.php`

```php
define('LB_LICENSE_PRODUCT_SLUG', 'your-product-slug');
define('LB_PROVISIONING_KEY', 'your-product-provisioning-key'); // Import page in LB admin
define('LB_MARKET_URL', 'http://market.lb.test');
define('LB_API_URL', 'http://api.lb.test');
```

| Constant | Purpose |
|----------|---------|
| `LB_LICENSE_PRODUCT_SLUG` | Product slug on License Bridge |
| `LB_PROVISIONING_KEY` | Required for **imported** licenses only |
| `LB_MARKET_URL` | Hosted checkout / market base URL |
| `LB_API_URL` | API base (OAuth, license, updates, provisioning) |

Checkout-only customers do **not** need `LB_PROVISIONING_KEY`.

## Integration

```php
$bridge = license_example_bridge();
$slug   = plugin_basename(__FILE__);

$bridge->purchase_link($slug);
$bridge->license_exists($slug);
$bridge->is_license_active($slug);
$bridge->checkout_button($slug, [
    'plugin-file' => __FILE__,
    'gateway'     => 'paddle',
    'plan-slug'   => 'pro',
    'plan-type'   => 'annual',
]);
```

See **License Bridge Example** in wp-admin for live purchase link, Paddle/Stripe inline checkout, and license status.

## Imported license provisioning (test flow)

1. Import licenses on License Bridge (**Licenses → Import**) with the same keys customers store locally.
2. Set `LB_PROVISIONING_KEY` in `plugin.php`.
3. Store only the license key in WordPress (option `{md5(plugin_basename)}_my_license_key`).
4. Open **Plugins** or **Dashboard → Updates** — SDK calls provisioning on first API connection.

OAuth credentials are saved locally as:

- `{md5(plugin_basename)}_my_license_key`
- `{md5(plugin_basename)}_my_client_id`
- `{md5(plugin_basename)}_my_client_secret`
- `{md5(plugin_basename)}_my_access_token` (after `/oauth/token`)

On License Bridge, the OAuth client is stored in MongoDB (`oauth_clients`, database from `DB_DATABASE`).

### Reset provisioning test

Delete **both** new and legacy option prefixes before re-testing:

```sql
DELETE FROM wp_options
WHERE option_name LIKE '6162604b7b6c33a24bd7107be3290318_my_client%'
   OR option_name LIKE '6162604b7b6c33a24bd7107be3290318_my_access_token'
   OR option_name LIKE 'LP_license-example-plugin/plugin.php_my_client%'
   OR option_name LIKE 'LP_license-example-plugin/plugin.php_my_access_token';
```

`LegacyOptionsMigration` copies old `LP_{slug}_*` options into the SDK prefix on every plugin load. If you only delete the new prefix, stale OAuth credentials come back and provisioning is skipped.

## Change SDK code

1. Edit files in `lb/WordPress-SDK/`
2. Test in WordPress (path repo symlink applies changes immediately)
3. Commit and push from the SDK repo:

```bash
cd lb/WordPress-SDK
git add .
git commit -m "Your SDK changes"
git push origin master
```

4. Bump `version` in `WordPress-SDK/composer.json` when releasing

## Do not

- Commit `vendor/` to this repo (only `composer.json` + `composer.lock`)
- Edit SDK only inside `vendor/` without the path repo — changes are lost on `composer update`
- Commit real `LB_PROVISIONING_KEY` values to git

## See also

- [WordPress-SDK README](../WordPress-SDK/README.md) — full SDK docs and provisioning details
- [License Bridge docs](https://licensebridge.com/docs/sdk)
