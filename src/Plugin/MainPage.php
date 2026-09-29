<?php

namespace LicenseExample\Plugin;

class MainPage
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'licenseExamplePanel']);
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

    public function licenseBridgePage(): void
    {
        $slug = plugin_basename(LB_EXAMPLE_PLUGIN_FILE);
        $bridge = \lb_example_license();
        $purchaseLink = $bridge ? esc_url($bridge->purchase_link($slug)) : '#';
        $hasLicense = $bridge && $bridge->license_exists($slug);
        $isActive = $bridge && $bridge->is_license_active($slug);

        echo '<div class="wrap">';
        echo '<h2>Plugin licensing with License Bridge</h2>';
        echo '<div>';
        echo '<h4>SDK integration</h4>';
        echo '<p>This example uses the official <code>license-bridge/wordpress-sdk</code> package. Checkout redirect, premium upgrade, and update checks are handled by the SDK.</p>';
        echo '<p>For imported licenses, set <code>LB_PROVISIONING_KEY</code> in <code>plugin.php</code>. OAuth credentials are created automatically on first connection when only a license key is stored locally.</p>';

        echo '<h4>License status</h4>';
        echo '<ul>';
        echo '<li>OAuth credentials stored: <strong>' . ($hasLicense ? 'yes' : 'no') . '</strong></li>';
        echo '<li>License active on License Bridge: <strong>' . ($isActive ? 'yes' : 'no') . '</strong></li>';
        echo '</ul>';

        echo '<h4>Purchase link</h4>';
        echo '<p>Customers are redirected to the License Bridge landing page to buy a license.</p>';
        echo '<a href="' . $purchaseLink . '" class="button button-primary">Buy Premium version</a>';
        echo '<p><code>' . esc_html($purchaseLink) . '</code></p>';

        echo '<h4>Documentation</h4>';
        echo '<p><a href="https://licensebridge.com/docs/sdk" target="_blank" rel="noopener noreferrer">License Bridge SDK docs</a></p>';
        echo '</div>';
        echo '</div>';
    }
}
