<?php

namespace LicenseBridge\WordPress\Update;

class PremiumUpdate
{
    /**
     * License Server Provider
     *
     * @var LicenseServer
     */
    private $licenseServer;

    /**
     * Archive Manager
     *
     * @var PluginZip
     */
    private $archive;


    /**
     * Init hooks and create object
     */
    public function __construct()
    {
        $this->init_hooks();
        $this->licenseServer = new LicenseServer;
        $this->archive = new PluginZip;
    }

    /**
     * Init hooks
     *
     * @return void
     */
    private function init_hooks()
    {
        add_filter('plugins_api', [$this, 'pluginPopupInfo'], 20, 3);
        add_filter('site_transient_update_plugins', [$this, 'licensePluginUpdate']);
    }

    /**
     * Opens popup with new plugin version informations
     * Attached to the plugins_api filter
     *
     * @param object $res
     * @param string $action
     * @param object $args
     * @return mixed
     */
    public function pluginPopupInfo($res, $action, $args)
    {
        // do nothing if this is not about getting plugin information
        if ($action !== 'plugin_information') {
            return false;
        }

        // do nothing if it is not our plugin
        if (LICENSE_CHECK_PLUGIN_NAME !== $args->slug) {
            return $res;
        }

        if ($remote = $this->licenseServer->fetchPluginDetails()) {
            $filePath = $this->archive->preparePluginZip($remote);

            $res = new \stdClass();
            $res->name = $remote->name;
            $res->slug = LICENSE_CHECK_PLUGIN_NAME;
            $res->version = $remote->version;
            $res->tested = $remote->tested;
            $res->requires = $remote->requires;
            $res->author = $remote->author;
            $res->author_profile = $remote->author_uri;
            $res->download_link = $filePath;
            $res->trunk = $filePath;
            $res->last_updated = $remote->last_updated;
            $res->sections = [];
            if (!empty($remote->sections->description)) {
                $res->sections['description'] = $remote->sections->description;
            }
            if (!empty($remote->sections->installation)) {
                $res->sections['installation'] = $remote->sections->installation;
            }
            if (!empty($remote->sections->changelog)) {
                $res->sections['changelog'] = $remote->sections->changelog;
            }
            // in case you want the screenshots tab, use the following HTML format for its content:
            // <ol><li><a href="IMG_URL" target="_blank" rel="noopener noreferrer"><img src="IMG_URL" alt="CAPTION" /></a><p>CAPTION</p></li></ol>
            if (!empty($remote->sections->screenshots)) {
                $res->sections['screenshots'] = $remote->sections->screenshots;
            }

            $res->banners = array(
                'low' => 'https://wpengine.com/wp-content/uploads/2017/03/plugged-in-hero.jpg',
                'high' => 'https://wpengine.com/wp-content/uploads/2017/03/plugged-in-hero.jpg'
            );
            return $res;
        }

        return false;
    }

    /**
     * Updateplugin to the latest version
     * Attached to the site_transient_update_plugins filter
     *
     * @param object $transient
     * @return object
     */
    public function licensePluginUpdate($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }
        if (isset($transient->response[LICENSE_CHECK_PLUGIN_NAME])) {
            return $transient;
        }

        $remote = $this->licenseServer->fetchPluginDetails();

        if (is_wp_error($remote)) {
            new AdminNotice($remote->get_error_message(), 'error');
            return $transient;
        }

        if ($remote && $this->newVersionAvailable($remote)) {
            $filePath = $this->archive->preparePluginZip($remote);

            if (is_wp_error($filePath)) {
                return new AdminNotice($filePath->get_error_message(), 'error');
            }

            $res = new \stdClass();
            $res->slug = LICENSE_CHECK_PLUGIN_NAME;
            $res->plugin = LICENSE_CHECK_PLUGIN_NAME;
            $res->new_version = $remote->version;
            $res->tested = $remote->tested;
            $res->package = $filePath;
            $transient->response[$res->plugin] = $res;
            $transient->checked[$res->plugin] = $remote->version;
        }
        return $transient;
    }

    /**
     * Check is nre plugin version available or not
     *
     * @param object $remote
     * @return bool
     */
    private function newVersionAvailable($remote)
    {
        return version_compare(LICENSE_PLUGIN_VERSION, $remote->version, '<') && version_compare($remote->requires, get_bloginfo('version'), '<');
    }

    /**
     * Set license server
     *
     * @param LicenseServer $licenseServer
     * @return void
     */
    public function setLicenseServer(LicenseServer $licenseServer)
    {
        $this->licenseServer = $licenseServer;
    }

    /**
     * Set plugin zip
     *
     * @param PluginZip $pluginZip
     * @return void
     */
    public function setPluginZip(PluginZip $pluginZip)
    {
        $this->archive = $pluginZip;
    }
}
