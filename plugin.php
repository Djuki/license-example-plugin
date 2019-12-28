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
 * Tested up to: 5.2.3
 * Author:            Your Name
 * Author URI:        https://example.com
 * Text Domain:       license-bridge-example-connection
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mc-woocommerce
 * Domain Path:       /languages
 */

use LicenseExample\Plugin\MainPage;
use LicenseBridge\WordPress\Update\PremiumBuy;
use LicenseBridge\WordPress\Update\PremiumUpdate;

$slug = plugin_basename(__FILE__);
if (is_admin()) {
    if (!function_exists('get_plugin_data')) {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    $plugin_data = get_plugin_data(__FILE__);
}

/**
 * Configuration Area
 */

/**
 * License Bridge base URL
 */
define("LB_URL", "https://8eda7344.ngrok.io");

/**
 * Plugin purchase page. On this page customer can purchase the license
 */
//define("PLUGIN_LANDING_PAGE", "http://starter.test/product/my-first-product/stripe/life");
//define("PLUGIN_LANDING_PAGE", "http://starter.test/product/my-first-product/paypal/life");
//define("PLUGIN_LANDING_PAGE", "http://starter.test/product/my-first-product/stripe/basic");
define("PLUGIN_LANDING_PAGE", "http://starter.test/product/my-first-product/paypal/basic");

/**
 * API route to check is new plugin version available.
 */
define("FETCH_PLUGIN_DETAILS_URL", LB_URL . '/api/plugin/details/my-first-product');

/**
 * Cache time for plugin update information. Suggested value is 43200 seconds (12 hours)
 */
define("TRANSIENT_CACHE_TIME", 30);


/**
 * Configuration below is area you will less likely need to chnage.
 */


/**
 * Plugin version we read from the header of this same file
 */
define("LICENSE_PLUGIN_VERSION", $plugin_data['Version'] ?? '1.0');

/**
 * Plugin slug - User friendly and URL valid name of a plugin.
 */
define("LICENSE_CHECK_PLUGIN_NAME", $slug);

/**
 * API route to fetch OAuth2 token
 */
define("FETCH_TOKEN_URL", LB_URL . '/oauth/token');

/**
 * Prefix we use to distinct from other plugins use this same plugin template.
 */
if (!defined("LP_OPTION_PREFIX")) {
    define("LP_OPTION_PREFIX", "LP_" . $slug . '_');
}

/**
 * Update to premium version page slug
 */
define("STORE_VALUES_URI", 'license-store-values-' . $slug);


include "vendor/autoload.php";

$page = new MainPage;

$buy = new PremiumBuy;

// Turn the premium autoupdate only in premium plugin version, on separate branch ex:premium
$premiumUpdate = new PremiumUpdate;