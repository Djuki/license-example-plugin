<?php

/**
 * License Bridge Example Connection
 *
 * @package           MyPackage
 * @author            Ivan Djurdjevac
 * @copyright         2019 License Bridge
 * @license           GPL-2.0
 *
 * @wordpress-plugin
 * Plugin Name:       License Bridge connection example
 * Plugin URI:        https://example.com/plugin-name
 * Description:       Just an example how to connect to License Bridge
 * Version:           1.4.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Your Name
 * Author URI:        https://example.com
 * Text Domain:       license-bridge-example-connection
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mc-woocommerce
 * Domain Path:       /languages
 * Requires at least: 4.9
 * Tested up to: 5.2.3
 * WC requires at least: 3.5
 * WC tested up to: 3.7
 */

use LicenseExample\Plugin\MainPage;
use LicenseExample\Update\PluginAutoUpdate;
use LicenseExample\Update\PremiumBuy;
use LicenseExample\Update\PremiumUpdate;

$slug = plugin_basename(__FILE__);
define("LICENSE_CHECK_PLUGIN_NAME", $slug);
define("LB_URL", "https://fbfa6f8b.ngrok.io");
define("PLUGIN_LANDING_PAGE", "http://starter.test/product/my-first-product/stripe/basic");

include "vendor/autoload.php";

$page = new MainPage;

$buy = new PremiumBuy;

// Turn the premium autoupdate only in premium plugin version, on separate branch ex:premium
//$premiumUpdate = new PremiumUpdate;

function save_license_key()
{

    if (!isset($_GET['action'])) return;
    if ($_GET['action'] !== 'save_license_key') return;

    $buy = new PremiumBuy;
    $buy->save_license_key();

    echo 'Run cron task here.';
    exit;
}
add_action('init', 'save_license_key');

// If Logged user create nonce for guest user, that nonce doesn't work
// This function create a nonce without token and id, like from wp_create_nonce but for guest user
// Is based from WordPress builtin wp_create_nonce
// Work with wp_verify_nonce and can be used like wp_create_nonce
function wp_create_nonce_guest($action = -1)
{
    $i = wp_nonce_tick();
    return substr(wp_hash($i . '|' . $action . '|0|', 'nonce'), -12, 10);
}

function install_plugin($plugin_zip)
{
    include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    wp_cache_flush();

    $upgrader = new Plugin_Upgrader();
    $installed = $upgrader->install($plugin_zip);

    return $installed;
}

function upgrade_plugin($plugin_slug)
{
    include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    wp_cache_flush();

    $upgrader = new Plugin_Upgrader();
    $upgraded = $upgrader->upgrade($plugin_slug);

    return $upgraded;
}