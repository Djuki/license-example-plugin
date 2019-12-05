<?php

namespace LicenseBridge\WordPress\Update;

class Remote
{
    /**
     * Get remote url
     *
     * @param string $url
     * @param array $headers
     * @param integer $timeout
     * @return void
     */
    public function get($url, $headers = [], $timeout  = 10)
    {
        return wp_remote_get(
            $url,
            array(
                'timeout' => $timeout,
                'headers' => $headers
            )
        );
    }
}
