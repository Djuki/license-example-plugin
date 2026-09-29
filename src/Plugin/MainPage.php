<?php

namespace LicenseExample\Plugin;

class MainPage
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'licenseExamplePanel']);
        add_action('admin_notices', [$this, 'licenseAdminNotices']);
    }

    public function licenseExamplePanel(): void
    {
        add_menu_page(
            'License Bridge Example',
            'License Bridge Example',
            'manage_options',
            'license-example',
            [$this, 'licenseBridgePage']
        );
    }

    public function licenseAdminNotices(): void
    {
        if (!isset($_GET['license_upgraded']) && !isset($_GET['license_saved'])) {
            return;
        }

        if (isset($_GET['license_upgraded'])) {
            echo '<div class="notice notice-success is-dismissible"><p>'
                . esc_html__('Premium license activated and plugin updated.', 'license-bridge-example-connection')
                . '</p></div>';
            return;
        }

        echo '<div class="notice notice-info is-dismissible"><p>'
            . esc_html__('License saved. No newer plugin version was available to install.', 'license-bridge-example-connection')
            . '</p></div>';
    }

    public function licenseBridgePage(): void
    {
        $slug = plugin_basename(LICENSE_EXAMPLE_PLUGIN_FILE);
        $bridge = license_example_bridge();
        $sellPage = $bridge ? $bridge->purchase_link($slug) : '#';
        $hasLicense = $bridge && $bridge->license_exists($slug);
        $isActive = $bridge && $bridge->is_license_active($slug);
        $paddleCheckout = $bridge ? $bridge->checkout_button($slug, [
            'plugin-file' => LICENSE_EXAMPLE_PLUGIN_FILE,
            'gateway'     => 'paddle',
            'plan-slug'   => 'proba-osnovni',
            'plan-type'   => 'annual',
            'label'       => 'Upgrade to Pro - Paddle',
        ]) : '';

        $stripeCheckout = $bridge ? $bridge->checkout_button($slug, [
            'plugin-file' => LICENSE_EXAMPLE_PLUGIN_FILE,
            'gateway'     => 'stripe',
            'plan-slug'   => 'proba-life',
            'plan-type'   => 'life',
            'label'       => 'Upgrade to Pro - Stripe',
        ]) : '';

        echo '<div class="wrap"><div id="icon-options-general" class="icon32"><br></div>
            <h2>Plugin licensing with License Bridge</h2>
                <div>
                    <h4>Free and Premium plugin versions</h4>
                    <p>You can keep you free plugin version code in WordPress subversion repository, which is a good practice because that way your plugin gets a free listing and more people can see what you can offer.</p>
                    <p>Premium version, on the other hand, should stay in private repositories like Bitbucket or GitHub.</p>
                    <p>Your premium version will be available only for users who purchased a license.</p>

                    <h4>Imported licenses</h4>
                    <p>For Freemius or other migrations, set <code>LB_PROVISIONING_KEY</code> in <code>plugin.php</code>. OAuth credentials are provisioned automatically on first API connection when only a license key is stored locally.</p>

                    <h4>Licensing process</h4>
                    <p>Free plugin users will be redirected to the external License Bridge page to buy a license after they click on "purchase link".</p>

                    <p>After a successful purchase, they will be redirected back, and they will get the latest premium plugin version. The latest premium plugin version should always be higher than the free version to make auto-update from free to premium version possible.</p>

                    <h4>License status</h4>
                    <p><strong>OAuth credentials stored:</strong> ' . ($hasLicense ? 'Yes' : 'No') . '</p>
                    <p><strong>License active on License Bridge:</strong> ' . ($isActive ? 'Yes' : 'No') . '</p>

                    <h4>Purchase Link</h4>
                    <p>This is just an example of how a Purchase button can look like</p>
                    <a href="' . esc_url($sellPage) . '" class="button button-primary">Buy Premium version</a>

                    <p>This is the URL of the License Bridge sell page</p>
                    <p><code>' . esc_html($sellPage) . '</code></p>

                    <h4>License Bridge Inline Paddle Checkout</h4>
                    <p>This is an example of how a License Bridge Inline Paddle Checkout can look like</p>
                    <div class="license-bridge-inline-paddle-checkout">
                        ' . $paddleCheckout . '
                    </div>

                    <h4>License Bridge Inline Stripe Checkout</h4>
                    <p>This is an example of how a License Bridge Inline Stripe Checkout can look like</p>
                    <div class="license-bridge-inline-stripe-checkout">
                        ' . $stripeCheckout . '
                    </div>

                    <h4>For more information</h4>
                    <p>Check out our documentation at <a href="https://licensebridge.com/docs/sdk" target="_blank" rel="noopener noreferrer">License Bridge SDK docs</a></p>
                </div>
            </div>';
    }
}
