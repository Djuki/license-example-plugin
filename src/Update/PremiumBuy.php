<?php

namespace LicenseExample\Update;

use Plugin_Upgrader;

class PremiumBuy
{

    public function __construct()
    {
        add_action('admin_menu', [$this, 'licenseStoreValues']);
    }

    public function licenseStoreValues()
    {
        add_menu_page('License Bridge Store', 'License Bridge Store', 'manage_options', STORE_VALUES_URI, [$this, 'saveLicenseKey']);
        remove_menu_page(STORE_VALUES_URI);
    }
    /**
     * After the plugin user purchase the premium plugin version
     * It will be redirected to this method to store his credencials:
     *  - license key
     *  - oauth client id
     *  - oauth clinet secret
     *
     * @return void
     */
    public function saveLicenseKey()
    {
        if (!wp_verify_nonce($_REQUEST['_nonce'], "license_key_nonce")) {
            exit("No naughty business please" . $_REQUEST['_nonce']);
        }

        // Check license key and save it 
        update_option('my_license_key', $_REQUEST['lk']);
        update_option('my_client_id', $_REQUEST['client_id']);
        update_option('my_client_secret', $_REQUEST['client_secret']);
        update_option('my_access_token', false);

        echo apply_filters('before_upgrade_plugin_'.LICENSE_CHECK_PLUGIN_NAME, '');
        $this->upgradePlugin(LICENSE_CHECK_PLUGIN_NAME);
        echo apply_filters('after_upgrade_plugin_' . LICENSE_CHECK_PLUGIN_NAME, '');
    }


    private function upgradePlugin($plugin_slug)
    {
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        wp_cache_flush();

        $upgrader = new Plugin_Upgrader();
        $update = new PremiumUpdate();
        $upgraded = $upgrader->upgrade($plugin_slug);
        activate_plugin($plugin_slug);

        return $upgraded;
    }
}