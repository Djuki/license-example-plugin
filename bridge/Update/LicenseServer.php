<?php

namespace LicenseBridge\WordPress\Update;

use League\OAuth2\Client\Token\AccessToken;

class LicenseServer
{

    /**
     * Token
     *
     * @var Token
     */
    private $token;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->token = new Token;
    }

    /**
     * Fetch plugin details from LicenseBridge API
     *
     * @return array
     */
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
                FETCH_PLUGIN_DETAILS_URL,
                array(
                    'timeout' => 10,
                    'headers' => $headers
                )
            );

            if (!is_wp_error($remote) && isset($remote['response']['code']) && $remote['response']['code'] == 200 && !empty($remote['body'])) {
                set_transient(LICENSE_CHECK_PLUGIN_NAME, $remote, TRANSIENT_CACHE_TIME);
            }
        }

        $remote = json_decode($remote['body']);
        
        return $remote;
    }
}
