<?php

namespace LicenseBridge\WordPress\Update;

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
        $url = admin_url('admin.php?page=' . STORE_VALUES_URI);
        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => get_option('my_client_id'),    // The client ID assigned to you by the provider
            'clientSecret'            => get_option('my_client_secret'),   // The client password assigned to you by the provider
            'urlAuthorize'            => LB_URL,
            'redirectUri'             => $url,
            'urlAccessToken'          => FETCH_TOKEN_URL,
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
            new AdminNotice("We can't get key from Licence Bridge. The error has occurred.", 'error');
        } catch (\Exception $e) {
            new AdminNotice("We can't get key from Licence Bridge. The error has occurred.", 'error');
        }

        return $token;
    }
}
