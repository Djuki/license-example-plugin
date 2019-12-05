<?php

namespace LicenseBridge\WordPress\Update;

class Token
{

    /**
     * Provider Factory
     *
     * @var ProviderFactory
     */
    private $factory;


    public function __construct()
    {
        $this->factory = new ProviderFactory;
    }

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
        $options = [
            'clientId'                => get_option(LP_OPTION_PREFIX . 'my_client_id'),
            'clientSecret'            => get_option(LP_OPTION_PREFIX . 'my_client_secret'),
            'urlAuthorize'            => LB_URL,
            'redirectUri'             => $url,
            'urlAccessToken'          => FETCH_TOKEN_URL,
            'urlResourceOwnerDetails' => LB_URL
        ];

        $provider = $this->factory->make($options);
        
        try {
            if ($dbToken = get_option(LP_OPTION_PREFIX.'my_access_token', false)) {
                $token = unserialize($dbToken);
            }
            
            if (!$token || $token->hasExpired()) {
                $token = $provider->getAccessToken('client_credentials');
                update_option(LP_OPTION_PREFIX . 'my_access_token', serialize($token));
            }
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            new AdminNotice("We can't get key from Licence Bridge. The error has occurred.", 'error');
        } catch (\Exception $e) {
            new AdminNotice("We can't get key from Licence Bridge. The error has occurred.", 'error');
        }

        return $token;
    }

    /**
     * Set factory, used for testing
     *
     * @param ProviderFactory $factory
     * @return void
     */
    public function setFactory(ProviderFactory $factory)
    {
        $this->factory = $factory;
    }
}
