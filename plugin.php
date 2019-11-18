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
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Your Name
 * Author URI:        https://example.com
 * Text Domain:       license-bridge-example-connection
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mc-woocommerce
 * Domain Path:       /languages
 * Requires at least: 4.9
 * Tested up to: 5.2.3
 * WC requires at least: 3.5
 * WC tested up to: 3.7
 */

define("LICENSE_CHECK_PLUGIN_NAME", "license-bridge-example-connection");
define("LB_URL", "https://c52c55d7.ngrok.io");
include "vendor/autoload.php";

add_filter('plugins_api', 'plugin_popup_info', 20, 3);
/*
 * $res contains information for plugins with custom update server 
 * $action 'plugin_information'
 * $args stdClass Object ( [slug] => woocommerce [is_ssl] => [fields] => Array ( [banners] => 1 [reviews] => 1 [downloaded] => [active_installs] => 1 ) [per_page] => 24 [locale] => en_US )
 */
function plugin_popup_info($res, $action, $args)
{

    // do nothing if this is not about getting plugin information
    if ($action !== 'plugin_information')
        return false;

    // do nothing if it is not our plugin	
    if (LICENSE_CHECK_PLUGIN_NAME !== $args->slug)
        return $res;

    if ($remote = fetchPluginDetails()) {

        $remote = json_decode($remote['body']);
        $res = new stdClass();
        $res->name = $remote->name;
        $res->slug = 'license-bridge-example-connection';
        $res->version = $remote->version;
        $res->tested = $remote->tested;
        $res->requires = $remote->requires;
        $res->author = '<a href="https://rudrastyh.com">Misha Rudrastyh</a>'; // I decided to write it directly in the plugin
        $res->author_profile = 'https://profiles.wordpress.org/rudrastyh'; // WordPress.org profile
        $res->download_link = $remote->download_url;
        $res->trunk = $remote->download_url;
        $res->last_updated = $remote->last_updated;
        $res->sections = array(
            'description' => $remote->sections->description, // description tab
            'installation' => $remote->sections->installation, // installation tab
            'changelog' => $remote->sections->changelog, // changelog tab
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


// Part two
add_filter('site_transient_update_plugins', 'misha_push_update');

function misha_push_update($transient)
{
    if (empty($transient->checked)) {
        return $transient;
    }

    $plugin_info = get_plugins('/' . explode('/', plugin_basename(__FILE__))[0]);

    $lk="123gbc";
    $nonce = wp_create_nonce("license_key_nonce");
    $urlProtected = admin_url('admin-ajax.php?action=save_license_key&_nonce='.$nonce);
    $url = admin_url('admin-ajax.php?action=save_license_key');
//var_dump(urlencode($url));exit;
    //var_dump(get_option('my_client_id'), get_option('my_client_secret'));exit;
    $provider = new \League\OAuth2\Client\Provider\GenericProvider([
        'clientId'                => get_option('my_client_id'),    // The client ID assigned to you by the provider
        'clientSecret'            => get_option('my_client_secret'),   // The client password assigned to you by the provider
        'urlAuthorize'            => LB_URL.'/oauth2/lockdin/authorize',
        'redirectUri'             => $url,
        'urlAccessToken'          => LB_URL.'/oauth/token',
        'urlResourceOwnerDetails' => 'http://brentertainment.com/oauth2/lockdin/resource'
    ]);

    try {

        $token = false;
        if ($dbToken = get_option('my_access_token', false)) {
            $token = unserialize($dbToken);
        }
        if (!$token || $token->hasExpired()) {
            $token = $provider->getAccessToken('client_credentials');
            update_option('my_access_token', serialize($token));
        }

        $details = fetchPluginDetails($token);
        
        echo '<pre>';
        var_dump($details['body'], "s");exit;
        


    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

        // Failed to get the access token
        exit($e->getMessage());
    } catch (\Exception $e) {
        exit($e->getMessage());
    }
    

var_dump(urlencode($url));exit;
    /*echo '<pre>';
    var_dump(plugin_dir_path(__FILE__).key($e));
    exit;
    $plugin_file = get_plugin_files(__FILE__) ?? false;*/  

    $plugin_dir = plugin_dir_path(__FILE__);
    //$plugin_dir = WP_PLUGIN_DIR . '/license-example';
    if (is_dir($plugin_dir)) {
        // plugin directory found!
        //echo 'FOUNT';exit;
    }
    //$e = get_plugins('/license-example');
    //echo '<pre>';
    //var_dump($e, $plugin_dir);exit;

    if ($remote = fetchPluginDetails()) {

        $remote = json_decode($remote['body']);

        // your installed plugin version should be on the line below! You can obtain it dynamically of course 
        if ($remote && version_compare('1.0', $remote->version, '<') && version_compare($remote->requires, get_bloginfo('version'), '<')) {
            $res = new stdClass();
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

function fetchPluginDetails($token) {
    if (false == $remote = get_transient(LICENSE_CHECK_PLUGIN_NAME)) {
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token->getToken()
        ];
        //var_dump($token->getToken());exit;
        $remote = wp_remote_get(
            LB_URL. '/api/product/update-check/my-first-product',
            array(
                'timeout' => 10,
                'headers' => $headers
            )
        );

        if (!is_wp_error($remote) && isset($remote['response']['code']) && $remote['response']['code'] == 200 && !empty($remote['body'])) {
            //set_transient('misha_upgrade_YOUR_PLUGIN_SLUG', $remote, 43200); // 12 hours cache
            //set_transient(LICENSE_CHECK_PLUGIN_NAME, $remote, 120); // 12 hours cache
        }
    }

    return $remote;
}


add_action('wp_ajax_nopriv_save_license_key', 'save_license_key');
add_action('wp_ajax_save_license_key', 'save_license_key');

function save_license_key()
{
    if (!wp_verify_nonce($_REQUEST['_nonce'], "license_key_nonce")) {
        exit("No naughty business please".$_REQUEST['_nonce']);
    }   

    
    // Check license key and save it 
    update_option('my_license_key', $_REQUEST['lk']);
    update_option('my_client_id', $_REQUEST['client_id']);
    update_option('my_client_secret', $_REQUEST['client_secret']);

    wp_die(); // this is required to terminate immediately and return a proper response
}

// If Logged user create nonce for guest user, that nonce doesn't work
// This function create a nonce without token and id, like from wp_create_nonce but for guest user
// Is based from WordPress builtin wp_create_nonce
// Work with wp_verify_nonce and can be used like wp_create_nonce
function wp_create_nonce_guest($action = -1)
{
    $i = wp_nonce_tick();
    return substr(wp_hash($i . '|' . $action . '|0|', 'nonce'), -12, 10);
}