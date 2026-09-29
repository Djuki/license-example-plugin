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
 * Author:            Your Name
 * Author URI:        https://example.com
 * Text Domain:       license-bridge-example-connection
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

use LicenseExample\Plugin\LegacyOptionsMigration;
use LicenseExample\Plugin\MainPage;

/**
 * License Bridge product slug (dashboard → Products).
 */
define('LB_EXAMPLE_PLUGIN_FILE', __FILE__);

define('LB_LICENSE_PRODUCT_SLUG', 'mojprojekat');

/**
 * Provisioning key for imported licenses (dashboard → Licenses → Import).
 * Not required when every customer purchases through License Bridge checkout.
 */
define('LB_PROVISIONING_KEY', 'z0gwYMCcYDs6h4NOIFcsFxHIUsOjOp9DBcnGBfYl0pcvIs3LlZ1wqQyQoRCl1eD1');

/**
 * Optional overrides for local / staging (uncomment and adjust).
 */
define('LB_API_URL', 'http://api.lb.test');
// define('LB_MARKET_URL', 'http://starter.test');

if (!function_exists('lb_example_license')) {
    function lb_example_license()
    {
        global $lb_example_license;

        if ($lb_example_license) {
            return $lb_example_license;
        }

        if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
            return null;
        }

        require_once __DIR__ . '/vendor/autoload.php';
        include __DIR__ . '/vendor/license-bridge/wordpress-sdk/src/Boot/bootstrap.php';

        LegacyOptionsMigration::migrate(__FILE__);

        $config = [
            'plugin-slug'          => plugin_basename(__FILE__),
            'license-product-slug' => LB_LICENSE_PRODUCT_SLUG,
        ];

        if (defined('LB_PROVISIONING_KEY') && LB_PROVISIONING_KEY !== '' && LB_PROVISIONING_KEY !== 'your-product-provisioning-key') {
            $config['provisioning-key'] = LB_PROVISIONING_KEY;
        }

        if (defined('LB_API_URL')) {
            $config['license-bridge-api-url'] = LB_API_URL;
        }

        if (defined('LB_MARKET_URL')) {
            $config['license-bridge-url'] = LB_MARKET_URL;
        }

        $lb_example_license = \LicenseBridge\WordPressSDK\Boot\Loader::register(__FILE__, $config);

        return $lb_example_license;
    }

    lb_example_license();
}

if (is_admin()) {
    new MainPage();
}
