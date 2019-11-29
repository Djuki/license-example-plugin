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
        $callback = admin_url('admin.php?page='.STORE_VALUES_URI.'&_nonce=' . $nonce);
        $sellPage = PLUGIN_LANDING_PAGE.'?callback_url='.urlencode($callback);

        echo '<div class="wrap"><div id="icon-options-general" class="icon32"><br></div>
            <h2>Plugin licensing with License Bridge</h2>
                <div>
                    <h4>Free and Premium plugin versions</h4>
                    <p>You can keep you free plugin version code in WordPress subversion repository, which is a good practice because that way your plugin gets a free listing and more people can see what you can offer.</p>
                    <p>Premium version, on the other hand, should stay in private repositories like Bitbucket or GitHub.</p>
                    <p>Your premium version will be available only for users who purchased a license.</p>
        

                    <h4>Licensing process</h4>
                    <p>Free plugin users will be redirected to the external License Bridge page to buy a license after they click on "purchase link".</p>

                    <p>After a successful purchase, they will be redirected back, and they will get the latest premium plugin version. The latest premium plugin version should always be higher than the free version to make auto-update from free to premium version possible.</p>

                    <h4>Purchase Link</h4>
                    <p>This is just an example of how a Purchase button can look like</p>
                    <a href="'.$sellPage.'" class="button button-primary">Buy Premium version</a>

                    <p>This is the URL of the License Bridge sell page</p>
                    <p><code>'. $sellPage.'</code></p>

                    <h4>For more information</h4>
                    <p>Check out our documentation at <a  href="https://licensebridge.com" target="_blank">https://licensebridge.com</a></p>
                </div>
            </div>';
    }

}
