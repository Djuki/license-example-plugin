<?php

namespace LicenseBridge\WordPress\Update;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessToken as TokenAccessToken;

class TokenTest extends TestCase
{
    /**
     * @test
     */
    public function it_get_access_token_from_remote_server()
    {
        // Arrange
        update_option(LP_OPTION_PREFIX . 'my_access_token', false);

        $accessToken = new AccessToken([
            'access_token' => 'abc'
        ]);

        $providerStub = $this->providerMock($accessToken);
        $factory = $this->factoryMock($providerStub);

        $token = new Token;
        $token->setFactory($factory);

        // Act
        $oauthToken = $token->getLicenceOauthToken();

        // Assert
        $this->assertEquals($accessToken, $oauthToken);
        $this->assertEquals($accessToken->getToken(), $oauthToken->getToken());
    }

    /**
     * @test
     */
    public function if_token_srored_do_not_fetch_a_new_one()
    {
        // Arrange
        $accessToken = $this->accessTokenMock(false);

        update_option(LP_OPTION_PREFIX . 'my_access_token', serialize($accessToken));

        $providerStub = $this->createMock(\League\OAuth2\Client\Provider\GenericProvider::class);
        $factory = $this->factoryMock($providerStub);

        $token = new Token;
        $token->setFactory($factory);

        // Act
        $oauthToken = $token->getLicenceOauthToken();

        // Assert
        $this->assertEquals($accessToken, $oauthToken);
        $this->assertEquals($accessToken->getToken(), $oauthToken->getToken());
    }

    /**
     * @test
     */
    public function if_token_expired_fetch_new_one()
    {
        // Arrange
        $accessToken = $this->accessTokenMock(true);

        update_option(LP_OPTION_PREFIX . 'my_access_token', serialize($accessToken));

        $newAccessToken = new AccessToken([
            'access_token' => 'abc'
        ]);
        
        $providerStub = $this->providerMock($newAccessToken);
        $factory = $this->factoryMock($providerStub);

        $token = new Token;
        $token->setFactory($factory);

        // Act
        $oauthToken = $token->getLicenceOauthToken();

        // Assert
        $this->assertEquals($newAccessToken, $oauthToken);
        $this->assertEquals($newAccessToken->getToken(), $oauthToken->getToken());
    }

    /**
     * @test
     */
    public function it_return_false_when_provider_throw_exception()
    {
        // Arrange
        update_option(LP_OPTION_PREFIX . 'my_access_token', false);

        $accessToken = new AccessToken([
            'access_token' => 'abc'
        ]);
        
        $providerStub = $this->createMock(\League\OAuth2\Client\Provider\GenericProvider::class);
        $providerStub->expects($this->once())
            ->method('getAccessToken')
            ->willThrowException(new IdentityProviderException('Test exception', '401', 'response test'));

        $factory = $this->factoryMock($providerStub);

        $token = new Token;
        $token->setFactory($factory);

        // Act
        $oauthToken = $token->getLicenceOauthToken();

        // Assert
        $this->assertFalse($oauthToken);
    }

    private function factoryMock($providerStub)
    {
        $factory = $this->createMock(ProviderFactory::class);
        $factory->expects($this->once())
            ->method('make')
            ->willReturn($providerStub)
        ;

        return $factory;
    }

    private function providerMock($accessToken)
    {
        $providerStub = $this->createMock(\League\OAuth2\Client\Provider\GenericProvider::class);
        $providerStub->expects($this->once())
            ->method('getAccessToken')
            ->willReturn($accessToken)
        ;

        return $providerStub;
    }

    private function accessTokenMock($expired)
    {
        $accessToken = $this->createMock(AccessToken::class);
        $accessToken->method('hasExpired')
            ->willReturn($expired);
        $accessToken->method('getToken')
            ->willReturn('abc');

        return $accessToken;
    }
}
