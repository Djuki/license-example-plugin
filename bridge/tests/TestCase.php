<?php

namespace LicenseBridge\WordPress\Update;

use PHPUnit\Framework\TestCase as FrameworkTestCase;

class TestCase extends FrameworkTestCase
{
    public function setUp()
    {
        if (!defined("LP_OPTION_PREFIX")) {
            define("LP_OPTION_PREFIX", "LP_TEST_");
        }
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        require('../../../../wp-blog-header.php');
    }

    public function tearDown()
    {
        delete_transient(LICENSE_CHECK_PLUGIN_NAME);
    }
}
