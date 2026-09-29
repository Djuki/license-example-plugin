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
 * Description:       Example integration with the License Bridge WordPress SDK
 * Version:           2.0.0
 * Requires at least: 6.4
 * Requires PHP:      7.2
 * Tested up to:      6.7
 * Author:            Your Name
 * Author URI:        https://example.com
 * Text Domain:       license-bridge-example-connection
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

use LicenseExample\Plugin\LegacyOptionsMigration;
use LicenseExample\Plugin\MainPage;

define('LICENSE_EXAMPLE_PLUGIN_FILE', __FILE__);

/**
 * License Bridge product slug (dashboard → Products).
 */
define('LB_LICENSE_PRODUCT_SLUG', 'mojprojekat');

/**
 * Provisioning key for imported licenses (dashboard → Licenses → Import).
 * Not required when every customer purchases through License Bridge checkout.
 */
define('LB_PROVISIONING_KEY', 'your-product-provisioning-key');

/**
 * Local / staging overrides (adjust for your environment).
 */
define('LB_MARKET_URL', 'http://market.lb.test');
define('LB_API_URL', 'http://api.lb.test');

if (!function_exists('license_example_bridge')) {
    /**
     * Access the License Bridge SDK instance for this plugin.
     *
     * @return \LicenseBridge\WordPressSDK\LicenseBridgeSDK|null
     */
    function license_example_bridge()
    {
        global $license_example_bridge;

        if ($license_example_bridge) {
            return $license_example_bridge;
        }

        if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
            return null;
        }

        require_once __DIR__ . '/vendor/autoload.php';
        include __DIR__ . '/vendor/license-bridge/wordpress-sdk/src/Boot/bootstrap.php';

        LegacyOptionsMigration::migrate(__FILE__);

        $config = [
            'plugin-slug'            => plugin_basename(__FILE__),
            'license-product-slug'   => LB_LICENSE_PRODUCT_SLUG,
            'license-bridge-url'     => LB_MARKET_URL,
            'license-bridge-api-url' => LB_API_URL,
        ];

        if (defined('LB_PROVISIONING_KEY') && LB_PROVISIONING_KEY !== '' && LB_PROVISIONING_KEY !== 'your-product-provisioning-key') {
            $config['provisioning-key'] = LB_PROVISIONING_KEY;
        }

        $license_example_bridge = \LicenseBridge\WordPressSDK\Boot\Loader::register(__FILE__, $config);

        return $license_example_bridge;
    }

    license_example_bridge();
}

add_action('init', function () {
    load_plugin_textdomain(
        'license-bridge-example-connection',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
});

add_action('plugins_loaded', function () {
    if (!license_example_bridge()) {
        return;
    }

    new MainPage();
});
