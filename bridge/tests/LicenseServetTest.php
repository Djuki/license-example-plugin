<?php

namespace LicenseBridge\WordPress\Update;

use League\OAuth2\Client\Token\AccessToken;

class LicenseServerTest extends TestCase
{
    /**
     * @test
     */
    public function valid_response_has_all_expected_attributes_in_object()
    {
        // Arrange
        $tokenStub = $this->makeTokenStub();

        $body = [
            'name' => 'Test Plugin',
            'slug' => 'plugin-slug',
            'download_content' => 'fileContent',
            'version' => '1.0.0',
            'requires' => '4.5',
            'tested' => '4.5',
            'author' => 'John Doe',
            'author_uri' => 'http://doe.com',
            'last_updated' => '1969-12-31 23:59:59',
        ];
        $remoteStub = $this->makeRemoteStub($body);

        $license = new LicenseServer;
        $license->setToken($tokenStub);
        $license->setRemote($remoteStub);

        // Act
        $details = $license->fetchPluginDetails();

        // Assert
        $this->assertObjectHasAttribute('name', $details);
        $this->assertObjectHasAttribute('slug', $details);
        $this->assertObjectHasAttribute('download_content', $details);
        $this->assertObjectHasAttribute('version', $details);
        $this->assertObjectHasAttribute('requires', $details);
        $this->assertObjectHasAttribute('tested', $details);
        $this->assertObjectHasAttribute('author', $details);
        $this->assertObjectHasAttribute('author_uri', $details);
        $this->assertObjectHasAttribute('last_updated', $details);

        $this->assertEquals($body['name'], $details->name);
        $this->assertEquals($body['slug'], $details->slug);
        $this->assertEquals($body['download_content'], $details->download_content);
        $this->assertEquals($body['version'], $details->version);
        $this->assertEquals($body['requires'], $details->requires);
        $this->assertEquals($body['tested'], $details->tested);
        $this->assertEquals($body['author'], $details->author);
        $this->assertEquals($body['author_uri'], $details->author_uri);
        $this->assertEquals($body['last_updated'], $details->last_updated);
    }

    /**
     * @test
     */
    public function on_bad_response_plugin_details_return_wp_error_object()
    {
        // Arrange
        $tokenStub = $this->makeTokenStub();

        $body = [
            'error' => 'Authorization failed',
        ];
        $remoteStub = $this->makeRemoteStub($body, 401);

        $license = new LicenseServer;
        $license->setToken($tokenStub);
        $license->setRemote($remoteStub);

        // Act
        $details = $license->fetchPluginDetails();
        
        // Assert
        $this->assertTrue(is_wp_error($details));
        $this->assertEquals('We could not get plugin information from License Bridge', $details->get_error_message());
    }

    private function makeTokenStub()
    {
        $tokenStub = $this->createMock(Token::class);
        $tokenStub->method('getLicenceOauthToken')
            ->willReturn(new AccessToken([
                'access_token' => 'abc'
            ]));


        return $tokenStub;
    }

    private function makeRemoteStub($body, $code = 200)
    {
        $remoteStub = $this->createMock(Remote::class);
        $remoteStub->method('get')
            ->willReturn([
                'response' => ['code' => $code],
                'body' => json_encode($body)
            ]);
        return $remoteStub;
    }
}
