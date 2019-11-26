<?php

namespace LicenseExample\Update;

use Plugin_Upgrader;

class PremiumBuy
{

    public function __construct()
    {
        $this->init_hooks();
    }

    private function init_hooks()
    {
        add_action('wp_ajax_nopriv_save_license_key', [$this, 'save_license_key']);
        add_action('wp_ajax_save_license_key', [$this, 'save_license_key']);
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
    public function save_license_key()
    {
        if (!wp_verify_nonce($_REQUEST['_nonce'], "license_key_nonce")) {
            exit("No naughty business please" . $_REQUEST['_nonce']);
        }

        // Check license key and save it 
        update_option('my_license_key', $_REQUEST['lk']);
        update_option('my_client_id', $_REQUEST['client_id']);
        update_option('my_client_secret', $_REQUEST['client_secret']);

        $this->upgrade_plugin(LICENSE_CHECK_PLUGIN_NAME);

        exit(wp_redirect(admin_url('admin.php?page=license-example')));
    }


    private function upgrade_plugin($plugin_slug)
    {
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        wp_cache_flush();

        ob_start();
        $upgrader = new Plugin_Upgrader();
        $update = new PremiumUpdate();
        $upgraded = $upgrader->upgrade($plugin_slug);
        ob_end_clean();
        
        return $upgraded;
    }
}