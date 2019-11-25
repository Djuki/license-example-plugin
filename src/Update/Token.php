<?php

namespace LicenseExample\Update;

class Token
{
    /**
     * This method get oauth tokent from the database if exist, and check is it still valid.
     * If token do not exists, or if expired we will try to get a fresh one from License Bridge server
     *
     * @return void
     */
    public function getLicenceOauthToken()
    {
        $token = false;
        $url = admin_url('admin-ajax.php?action=save_license_key');
        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => get_option('my_client_id'),    // The client ID assigned to you by the provider
            'clientSecret'            => get_option('my_client_secret'),   // The client password assigned to you by the provider
            'urlAuthorize'            => LB_URL,
            'redirectUri'             => $url,
            'urlAccessToken'          => LB_URL . '/oauth/token',
            'urlResourceOwnerDetails' => LB_URL
        ]);

        try {
            if ($dbToken = get_option('my_access_token', false)) {
                $token = unserialize($dbToken);
            }
            if (!$token || $token->hasExpired()) {
                $token = $provider->getAccessToken('client_credentials');
                update_option('my_access_token', serialize($token));
            }
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            add_action('admin_notices', [$this, 'error_oauth_key']);
        } catch (\Exception $e) {
            add_action('admin_notices', [$this, 'error_oauth_key']);
        }

        return $token;
    }

    function error_oauth_key($message)
    {
        echo ' <div class="error notice">
                 <p>We can\'t get key from Licence Bridge. The error has occurred.</p>
          </div>';
    }
}