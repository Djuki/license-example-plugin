<?php

namespace LicenseBridge\WordPress\Update;

use \League\OAuth2\Client\Provider\GenericProvider;

class ProviderFactory
{
    /**
     * Create Oauth2 Provider
     *
     * @param array $options
     * @return \League\OAuth2\Client\Provider\GenericProvider
     */
    public function make($options)
    {
        return new GenericProvider($options);
    }
}
