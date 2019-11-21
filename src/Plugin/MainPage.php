<?php

namespace LicenseExample\Plugin;

class MainPage
{
    public function __construct()
    {
        $this->init_hooks();
    }

    public function init_hooks()
    {
        add_action('admin_menu', [$this, 'licenseExamplePanel']);        
    }


    public function licenseExamplePanel()
    {
        add_menu_page('License Bridge Example', 'License Bridge Example', 'manage_options', 'license-example', [$this, 'licenseBridgePage']);
    }

    function licenseBridgePage()
    {
        $nonce = wp_create_nonce("license_key_nonce");
        $callback = admin_url('admin-ajax.php?action=save_license_key&_nonce=' . $nonce);
        $sellPage = PLUGIN_LANDING_PAGE.'?callback_url='.$callback;

        echo '<div class="wrap"><div id="icon-options-general" class="icon32"><br></div>
            <h2>License Bridge Example</h2>
                <div>
                    <h4>Free plugin version</h4>
                    <p>You could keep free versiom on master branch, and reserve other branch for premium version.</p>
                    <a href="'.$sellPage.'" class="button button-primary">Buy Premium version</a>
                </div>
            </div>';
    }

}
