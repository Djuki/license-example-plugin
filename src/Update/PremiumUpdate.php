<?php

namespace LicenseExample\Update;

use PclZip;
use WP_Error;
use ZipArchive;

class PremiumUpdate
{
    private $licenseServer;


    public function __construct()
    {
        $this->init_hooks();
        $this->licenseServer = new LicenseServer;
    }

    private function init_hooks()
    {
        add_filter('plugins_api', [$this, 'plugin_popup_info'], 20, 3);
        add_filter('site_transient_update_plugins', [$this, 'license_plugin_update']);
    }
    
    
    public function plugin_popup_info($res, $action, $args)
    {

        // do nothing if this is not about getting plugin information
        if ($action !== 'plugin_information')
            return false;

        // do nothing if it is not our plugin	
        if (LICENSE_CHECK_PLUGIN_NAME !== $args->slug)
            return $res;

        if ($remote = $this->licenseServer->fetchPluginDetails()) {
            
            $res = new \stdClass();
            $res->name = $remote->name;
            $res->slug = LICENSE_CHECK_PLUGIN_NAME;
            $res->version = $remote->version;
            $res->tested = $remote->tested;
            $res->requires = $remote->requires;
            $res->author = '<a href="https://rudrastyh.com">Misha Rudrastyh</a>'; // I decided to write it directly in the plugin
            $res->author_profile = 'https://profiles.wordpress.org/rudrastyh'; // WordPress.org profile
            $res->download_link = $remote->download_url;
            $res->trunk = $remote->download_url;
            $res->last_updated = $remote->last_updated;
            $res->sections = array(
                'description' => $remote->sections->description ?? '', // description tab
                'installation' => $remote->sections->installation ?? '', // installation tab
                'changelog' => $remote->sections->changelog?? '', // changelog tab
                // you can add your custom sections (tabs) here 
            );
            // in case you want the screenshots tab, use the following HTML format for its content:
            // <ol><li><a href="IMG_URL" target="_blank" rel="noopener noreferrer"><img src="IMG_URL" alt="CAPTION" /></a><p>CAPTION</p></li></ol>
            if (!empty($remote->sections->screenshots)) {
                $res->sections['screenshots'] = $remote->sections->screenshots;
            }

            $res->banners = array(
                'low' => 'https://YOUR_WEBSITE/banner-772x250.jpg',
                'high' => 'https://YOUR_WEBSITE/banner-1544x500.jpg'
            );
            return $res;
        }

        return false;
    }


    public function license_plugin_update($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $remote = $this->licenseServer->fetchPluginDetails();

        if ($remote && $this->newVersionAvailable($remote)) {
                $filePath = $this->createPluginFile($remote);

                $this->renameFolderInZip($filePath, dirname(LICENSE_CHECK_PLUGIN_NAME));

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

    public function renameFolderInZip($file, $newName)
    {
        if (class_exists('ZipArchive', false)) {
            //return $this->renameFilderInZipWithArchive($file, $newName);
        }
        // Fall through to PclZip if ZipArchive is not available, or encountered an error opening the file.
        require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
        $zip = new PclZip($file);

        $tempDir = get_temp_dir();
        $result = _unzip_file_pclzip($file, $tempDir);

        if ($result !== true) {
            return $result;
        }

        $extracted = $zip->extract();
        $firstFolder = $extracted[0]['filename'] ?? '';   
        
        $filesToCreate = [];
        foreach($extracted as $node => $details) {
            $filesToCreate[] = $tempDir.str_replace($firstFolder, $newName . '/', $extracted[$node]['filename']);
            //$extracted[$node]['filename'] = str_replace($firstFolder, $newName.'/', $extracted[$node]['filename']);
            //$extracted[$node]['stored_filename'] = str_replace($firstFolder, $newName.'/', $extracted[$node]['stored_filename']);
        }
        rename($tempDir.$firstFolder, $tempDir.$newName);

        //$newFile = wp_tempnam(LICENSE_CHECK_PLUGIN_NAME);
        //$newZip = new PclZip($newFile);
        if ($zip->create($filesToCreate) === 0) {
            return $newZip->errorInfo(true);
        }
        
        return true;
        
    }

    public function renameFilderInZipWithArchive($file, $newName)
    {
        $archive = new ZipArchive;
        $archive->open($file, ZipArchive::CREATE);

        $originalName = $archive->getNameIndex(0);
        $i = 0;
        while ($item_name = $archive->getNameIndex($i)) {
            if ($item_name === $originalName) {
                $archive->deleteIndex($i);
            }
            $archive->renameIndex($i++, str_replace($originalName, $newName.'/', $item_name));
        }

        $archive->close();
    }

    private function newVersionAvailable($remote)
    {
        return version_compare(LICENSE_PLUGIN_VERSION, $remote->version, '<') && version_compare($remote->requires, get_bloginfo('version'), '<');
    }

    private function createPluginFile($remote)
    {
        $filePath = wp_tempnam(LICENSE_CHECK_PLUGIN_NAME);
        $content = base64_decode($remote->download_content);
        file_put_contents($filePath, $content);

        return $filePath;
    }
}