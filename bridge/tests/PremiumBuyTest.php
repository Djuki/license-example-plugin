<?php

namespace LicenseBridge\WordPress\Update;

class PremiumBuyTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // Reset options
        update_option(LP_OPTION_PREFIX . 'my_license_key', false);
        update_option(LP_OPTION_PREFIX . 'my_client_id', false);
        update_option(LP_OPTION_PREFIX . 'my_client_secret', false);
    }

    /**
     * @test
     */
    public function it_saves_options_when_nonce_is_valid()
    {
        // Arrange
        $buy = $this->createPartialMock(PremiumBuy::class, ['upgradePlugin']);
        $buy->expects($this->once())
            ->method('upgradePlugin')
            ->willReturn(true)
        ;

        $_REQUEST['_nonce'] = wp_create_nonce("license_key_nonce");
        $_REQUEST['lk'] = 'abc';
        $_REQUEST['client_id'] = 'id';
        $_REQUEST['client_secret'] = 'secret';

        // Act
        $buy->saveLicenseKey();

        // Assert
        $this->assertEquals('abc', get_option(LP_OPTION_PREFIX . 'my_license_key'));
        $this->assertEquals('id', get_option(LP_OPTION_PREFIX . 'my_client_id'));
        $this->assertEquals('secret', get_option(LP_OPTION_PREFIX . 'my_client_secret'));
        $this->assertEquals(false, get_option(LP_OPTION_PREFIX . 'my_access_token'));
    }

    /**
     * @test
     */
    public function do_not_save_when_nonce_is_invalid()
    {
        // Arrange
        $buy = $this->createPartialMock(PremiumBuy::class, ['upgradePlugin']);

        $_REQUEST['_nonce'] = 'invaid_nonce';
        $_REQUEST['lk'] = 'abc';
        $_REQUEST['client_id'] = 'id';
        $_REQUEST['client_secret'] = 'secret';

        // Act
        $buy->saveLicenseKey();

        // Assert
        $this->assertEquals(false, get_option(LP_OPTION_PREFIX . 'my_license_key'));
        $this->assertEquals(false, get_option(LP_OPTION_PREFIX . 'my_client_id'));
        $this->assertEquals(false, get_option(LP_OPTION_PREFIX . 'my_client_secret'));
        $this->assertEquals(false, get_option(LP_OPTION_PREFIX . 'my_access_token'));
    }
}
