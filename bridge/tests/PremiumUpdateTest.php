<?php

namespace LicenseBridge\WordPress\Update;

class PremiumUpdateTest extends TestCase
{
    /**
     * @test
     */
    public function it_return_new_version_info()
    {
        // Arrange
        $transient = new \StdClass;
        $transient->checked = [
            LICENSE_CHECK_PLUGIN_NAME => []
        ];
        
        $remote = $this->getRemote();
        $filePath = '/tmp/path';
        $licenseServer = $this->licenseServerMock($remote);
        $pluginZip = $this->pluginZipMock($remote, $filePath);

        $premiumUpdate = new PremiumUpdate;
        $premiumUpdate->setLicenseServer($licenseServer);
        $premiumUpdate->setPluginZip($pluginZip);

        // Act
        $premiumUpdate->licensePluginUpdate($transient);

        // Assert
        $res = new \stdClass();
        $res->slug = LICENSE_CHECK_PLUGIN_NAME;
        $res->plugin = LICENSE_CHECK_PLUGIN_NAME;
        $res->new_version = $remote->version;
        $res->tested = $remote->tested;
        $res->package = $filePath;

        $this->assertEquals(LICENSE_CHECK_PLUGIN_NAME, $transient->response[LICENSE_CHECK_PLUGIN_NAME]->slug);
        $this->assertEquals(LICENSE_CHECK_PLUGIN_NAME, $transient->response[LICENSE_CHECK_PLUGIN_NAME]->plugin);
        $this->assertEquals($remote->version, $transient->response[LICENSE_CHECK_PLUGIN_NAME]->new_version);
        $this->assertEquals($remote->tested, $transient->response[LICENSE_CHECK_PLUGIN_NAME]->tested);
        $this->assertEquals($filePath, $transient->response[LICENSE_CHECK_PLUGIN_NAME]->package);
        $this->assertEquals($res, $transient->response[LICENSE_CHECK_PLUGIN_NAME]);
        $this->assertEquals($remote->version, $transient->checked[LICENSE_CHECK_PLUGIN_NAME]);
    }

    /**
     * @test
     */
    public function it_returns_plugin_info_for_popup()
    {
        // Arrange
        $remote = $this->getRemote();
        $filePath = '/tmp/path';
        $licenseServer = $this->licenseServerMock($remote);
        $pluginZip = $this->pluginZipMock($remote, $filePath);

        $premiumUpdate = new PremiumUpdate;
        $premiumUpdate->setLicenseServer($licenseServer);
        $premiumUpdate->setPluginZip($pluginZip);

        // Act
        $args = new \StdClass;
        $args->slug = LICENSE_CHECK_PLUGIN_NAME;
        $response = $premiumUpdate->pluginPopupInfo(null, 'plugin_information', $args);

        // Assert
        $res = new \stdClass();
        $res->slug = LICENSE_CHECK_PLUGIN_NAME;
        $res->plugin = LICENSE_CHECK_PLUGIN_NAME;
        $res->new_version = $remote->version;
        $res->tested = $remote->tested;
        $res->package = $filePath;
        $res->last_updated = $remote->last_updated;

        $this->assertEquals(LICENSE_CHECK_PLUGIN_NAME, $response->name);
        $this->assertEquals(LICENSE_CHECK_PLUGIN_NAME, $response->slug);
        $this->assertEquals($remote->version, $response->version);
        $this->assertEquals($remote->tested, $response->tested);
        $this->assertEquals($remote->requires, $response->requires);
        $this->assertEquals($remote->author, $response->author);
        $this->assertEquals($remote->author_uri, $response->author_profile);
        $this->assertEquals($filePath, $response->download_link);
        $this->assertEquals($filePath, $response->trunk);
        $this->assertEquals($remote->last_updated, $response->last_updated);
        $this->assertEquals($remote->sections->description, $response->sections['description']);
        $this->assertEquals($remote->sections->installation, $response->sections['installation']);
        $this->assertEquals($remote->sections->changelog, $response->sections['changelog']);
        $this->assertEquals($remote->sections->screenshots, $response->sections['screenshots']);
    }

    private function getRemote()
    {
        $remote = new \StdClass;
        $remote->name = LICENSE_CHECK_PLUGIN_NAME;
        $remote->slug = LICENSE_CHECK_PLUGIN_NAME;
        $remote->download_content = 'abc';
        $remote->version = '5.0.0';
        $remote->requires = '5.0.0';
        $remote->tested = '5.0.0';
        $remote->author = 'John Doe';
        $remote->author_uri = 'http://wwww.google.com';
        $remote->last_updated = '1970-01-01 00:59:59';
        $remote->sections = new \StdClass;
        $remote->sections->description = 'abc';
        $remote->sections->installation = 'abc';
        $remote->sections->changelog = 'abc';
        $remote->sections->screenshots = '<ol><li><a href="IMG_URL" target="_blank" rel="noopener noreferrer"><img src="IMG_URL" alt="CAPTION" /></a><p>CAPTION</p></li></ol>';


        return $remote;
    }

    private function licenseServerMock($remote)
    {
        $licenseServer = $this->createMock(LicenseServer::class);
        $licenseServer->expects($this->once())
            ->method('fetchPluginDetails')
            ->willReturn($remote);

        return $licenseServer;
    }

    private function pluginZipMock($remote, $filePath)
    {
        $pluginZip = $this->createMock(PluginZip::class);
        $pluginZip->expects($this->once())
            ->method('preparePluginZip')
            ->with($remote)
            ->willReturn($filePath);

        return $pluginZip;
    }
}
