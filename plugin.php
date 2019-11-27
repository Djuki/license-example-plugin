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
use LicenseExample\Update\PremiumBuy;
use LicenseExample\Update\PremiumUpdate;

$slug = plugin_basename(__FILE__);
if (is_admin()) {
    if (!function_exists('get_plugin_data')) {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    $plugin_data = get_plugin_data(__FILE__);
}

define("LICENSE_CHECK_PLUGIN_NAME", $slug);
define("LICENSE_PLUGIN_VERSION", $plugin_data['Version'] ?? '1.0');
define("STORE_VALUES_URI", 'license-store-values');
define("LB_URL", "https://15a2d51c.ngrok.io");
define("PLUGIN_LANDING_PAGE", "http://starter.test/product/my-first-product/stripe/basic");

include "vendor/autoload.php";

$page = new MainPage;

$buy = new PremiumBuy;

// Turn the premium autoupdate only in premium plugin version, on separate branch ex:premium
$premiumUpdate = new PremiumUpdate;