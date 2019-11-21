<?php

namespace LicenseExample\Update;

class PremiumUpdate
{
    private $licenseServer;


    public function __construct()
    {
        $this->init_hooks();
        $this->licenseServer = new LicenseServer;
    }

    private function init_hooks()
    {
        add_filter('plugins_api', [$this, 'plugin_popup_info'], 20, 3);
        add_filter('site_transient_update_plugins', [$this, 'license_plugin_update']);
    }
    
    /*
    * $res contains information for plugins with custom update server 
    * $action 'plugin_information'
    * $args stdClass Object ( [slug] => woocommerce [is_ssl] => [fields] => Array ( [banners] => 1 [reviews] => 1 [downloaded] => [active_installs] => 1 ) [per_page] => 24 [locale] => en_US )
    */
    public function plugin_popup_info($res, $action, $args)
    {

        // do nothing if this is not about getting plugin information
        if ($action !== 'plugin_information')
            return false;

        // do nothing if it is not our plugin	
        if (LICENSE_CHECK_PLUGIN_NAME !== $args->slug)
            return $res;

        if ($remote = $this->licenseServer->fetchPluginDetails()) {
            
            $res = new \stdClass();
            $res->name = $remote->name;
            $res->slug = LICENSE_CHECK_PLUGIN_NAME;
            $res->version = $remote->version;
            $res->tested = $remote->tested;
            $res->requires = $remote->requires;
            $res->author = '<a href="https://rudrastyh.com">Misha Rudrastyh</a>'; // I decided to write it directly in the plugin
            $res->author_profile = 'https://profiles.wordpress.org/rudrastyh'; // WordPress.org profile
            $res->download_link = $remote->download_url;
            $res->trunk = $remote->download_url;
            $res->last_updated = $remote->last_updated;
            $res->sections = array(
                'description' => $remote->sections->description ?? '', // description tab
                'installation' => $remote->sections->installation ?? '', // installation tab
                'changelog' => $remote->sections->changelog?? '', // changelog tab
                // you can add your custom sections (tabs) here 
            );
            // in case you want the screenshots tab, use the following HTML format for its content:
            // <ol><li><a href="IMG_URL" target="_blank" rel="noopener noreferrer"><img src="IMG_URL" alt="CAPTION" /></a><p>CAPTION</p></li></ol>
            if (!empty($remote->sections->screenshots)) {
                $res->sections['screenshots'] = $remote->sections->screenshots;
            }

            $res->banners = array(
                'low' => 'https://YOUR_WEBSITE/banner-772x250.jpg',
                'high' => 'https://YOUR_WEBSITE/banner-1544x500.jpg'
            );
            return $res;
        }

        return false;
    }


    public function license_plugin_update($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }
        $plugin_info = get_plugins('/' . explode('/', plugin_basename(__FILE__))[0]);

        $nonce = wp_create_nonce("license_key_nonce");
        $urlProtected = admin_url('admin-ajax.php?action=save_license_key&_nonce='.$nonce);
        
        if ($remote = $this->licenseServer->fetchPluginDetails()) {

            // your installed plugin version should be on the line below! You can obtain it dynamically of course 
            if ($remote && version_compare('1.0', $remote->version, '<') && version_compare($remote->requires, get_bloginfo('version'), '<')) {
                $res = new \stdClass();
                $res->slug = LICENSE_CHECK_PLUGIN_NAME;
                $res->plugin = 'license-example/plugin.php'; // it could be just YOUR_PLUGIN_SLUG.php if your plugin doesn't have its own directory
                $res->new_version = $remote->version;
                $res->tested = $remote->tested;
                $res->package = $remote->download_url;
                $transient->response[$res->plugin] = $res;
                //$transient->checked[$res->plugin] = $remote->version;
                
            }
        }
        return $transient;
    }

}