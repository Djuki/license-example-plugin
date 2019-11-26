<?php

namespace LicenseExample\Update;

class LicenseServer
{
    private $token;

    public function __construct()
    {
        $this->token = new Token;
    }
    public function fetchPluginDetails()
    {
        if (false == $remote = get_transient(LICENSE_CHECK_PLUGIN_NAME)) {

            if (!$token = $this->token->getLicenceOauthToken()) {
                return false;
            }

            $headers = [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token->getToken()
            ];

            $remote = wp_remote_get(
                LB_URL . '/api/product/update-check/my-first-product',
                array(
                    'timeout' => 10,
                    'headers' => $headers
                )
            );

            if (!is_wp_error($remote) && isset($remote['response']['code']) && $remote['response']['code'] == 200 && !empty($remote['body'])) {
                //set_transient('misha_upgrade_YOUR_PLUGIN_SLUG', $remote, 43200); // 12 hours cache
                set_transient(LICENSE_CHECK_PLUGIN_NAME, $remote, 30); // 12 hours cache
            }
        }

        $remote = json_decode($remote['body']);
        
        return $remote;
    }
}