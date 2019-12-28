<?php

namespace LicenseBridge\WordPress\Update;

use League\OAuth2\Client\Token\AccessToken;
use WP_Error;

class LicenseServer
{

    /**
     * Token
     *
     * @var Token
     */
    private $token;

    /**
     * Remote
     *
     * @var Remote
     */
    private $remote;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->token = new Token;
        $this->remote = new Remote;
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
                'Authorization' => 'Bearer ' . $token->getToken(),
                'LicenseKey' => get_option(LP_OPTION_PREFIX . 'my_license_key'),
            ];

            $remote = $this->remote->post(FETCH_PLUGIN_DETAILS_URL, [
                'method' => 'POST',
                'timeout' => 30,
                'headers' => $headers,
                'body' => [
                    'pluginFile' => substr(LICENSE_CHECK_PLUGIN_NAME, strpos(LICENSE_CHECK_PLUGIN_NAME, '/') + 1)
                ]
            ]);

            if (!$this->validResponse($remote)) {
                return new WP_Error('404', 'We could not get plugin information from License Bridge');
            }
            
            set_transient(LICENSE_CHECK_PLUGIN_NAME, $remote, TRANSIENT_CACHE_TIME);
        }

        $remote = json_decode($remote['body']);
        return $remote;
    }

    /**
     * Set token, used for mocking in unit testing
     *
     * @param Token $token
     * @return void
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Set remote, used for mocking in unit testing
     *
     * @param Remote $remote
     * @return void
     */
    public function setRemote($remote)
    {
        $this->remote = $remote;
    }

    /**
     * Check is response from server valid
     *
     * @param array $remote
     * @return void
     */
    private function validResponse($remote)
    {
        return isset($remote['response']['code']) && $remote['response']['code'] == 200 && !empty($remote['body']);
    }
}
