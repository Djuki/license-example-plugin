<?php

namespace LicenseBridge\WordPress\Update;

class PluginZipTest extends TestCase
{
    /**
     * Created file we want to delete ad the end of the testing
     *
     * @var string
     */
    private $createdFilePath;

    /**
     * @test
     * @dataProvider getRemoteData
     */
    public function return_wp_error_when_file_is_not_created($remote)
    {
        // Arrange
        $pluginZip = $this->createPartialMock(PluginZip::class, ['createPluginDirectory', 'createPluginFile']);
        $pluginZip->expects($this->once())
            ->method('createPluginDirectory')
            ->willReturn('/path/ss')
        ;
        
        $pluginZip->expects($this->once())
            ->method('createPluginFile')
            ->willReturn(true);
        ;

        // Act
        $result = $pluginZip->preparePluginZip($remote);

        // Assert
        $this->assertTrue(is_wp_error($result));
    }


    /**
     * @test
     * @dataProvider getRemoteData
     */
    public function file_is_created_and_file_path_returned($remote)
    {
        // Arrange
        $pluginZip = $this->createPartialMock(PluginZip::class, ['createPluginDirectory', 'renameFolderInZip']);
        $pluginZip->method('createPluginDirectory')
            ->willReturn(get_temp_dir())
        ;
    

        $pluginZip->method('renameFolderInZip')
            ->willReturn(true);
        ;

        // Act
        $this->createdFilePath = $pluginZip->preparePluginZip($remote);

        // Assert
        $this->assertEquals($pluginZip->tempPluginZip($remote), $this->createdFilePath);
    }

    public function getRemoteData()
    {
        $remote = new \StdClass;
        $remote->version = '1.0.0';
        $remote->download_content = 'abc';

        return [
            [
                    $remote
            ]
        ];
    }

    public function tearDown()
    {
        parent::tearDown();

        if ($this->createdFilePath) {
            unlink($this->createdFilePath);
        }
    }
}
