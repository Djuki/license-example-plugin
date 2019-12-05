<?php

namespace LicenseBridge\WordPress\Update;

use PclZip;
use WP_Error;
use ZipArchive;

class PluginZip
{
    /**
     * Create temporary plugin archive, and rename main folder to match the plugin's folder name
     * Returns apsolute path to the temporary plugin archive
     *
     * @param object $remote
     * @return string
     */
    public function preparePluginZip($remote)
    {
        $filePath = $this->tempPluginZip($remote);

        if (!file_exists($filePath)) {
            $this->createPluginFile($remote);

            if (!file_exists($filePath)) {
                return new WP_Error('401', sprintf("The file `%s` is not created", $filePath));
            }

            $this->renameFolderInZip($filePath, dirname(LICENSE_CHECK_PLUGIN_NAME));
        }

        return $filePath;
    }

    /**
     * Rename root folder in archive file
     *
     * @param string $file
     * @param string $newName
     * @return void
     */
    public function renameFolderInZip($file, $newName)
    {
        if (class_exists('ZipArchive', false)) {
            return $this->renameFolderInZipWithArchive($file, $newName);
        }

        $this->renameFolderInZipFallback($file, $newName);
    }

    /**
     * Rename root folder in archive using ZipArchive package
     *
     * @param string $file
     * @param string $newName
     * @return void
     */
    private function renameFolderInZipWithArchive($file, $newName)
    {
        $archive = new ZipArchive;
        $archive->open($file, ZipArchive::CREATE);

        $originalName = $archive->getNameIndex(0);
        $i = 0;
        while ($item_name = $archive->getNameIndex($i)) {
            if ($item_name === $originalName) {
                $archive->deleteIndex($i);
            }
            $archive->renameIndex($i++, str_replace($originalName, $newName . '/', $item_name));
        }

        $archive->close();
    }

    /**
     * Rename root folder in archive using PclZip WordPress package
     *
     * @param string $file
     * @param string $newName
     * @return void
     */
    private function renameFolderInZipFallback($file, $newName)
    {
        require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
        $zip = new PclZip($file);

        $tempDir = $this->tempPluginDirectory();
        $result = _unzip_file_pclzip($file, $tempDir);

        if ($result !== true) {
            return $result;
        }

        $extracted = $zip->extract();
        $firstFolder = $extracted[0]['filename'] ?? '';

        $filesToCreate = [];
        foreach ($extracted as $node => $details) {
            $filesToCreate[] = $tempDir . str_replace($firstFolder, $newName . '/', $extracted[$node]['filename']);
        }
        rename($tempDir . $firstFolder, $tempDir . $newName);

        wp_delete_file($file);
        if ($zip->create($filesToCreate, null, $this->tempPluginDirectory()) === 0) {
            return $zip->errorInfo(true);
        }
        $this->deleteDirectory($tempDir . $newName);
    }

    /**
     * Returns absolute path where the plugin archive will be stored
     *
     * @return void
     */
    private function tempPluginDirectory()
    {
        return get_temp_dir() . md5(LICENSE_CHECK_PLUGIN_NAME) . DIRECTORY_SEPARATOR;
    }

    /**
     * Create temporary plugin archive folder is one doesn't exists
     *
     * @return void
     */
    public function createPluginDirectory()
    {
        $dir = $this->tempPluginDirectory();
        if (!file_exists($dir)) {
            mkdir($dir);
        }
        return $dir;
    }

    /**
     * Full path to the temporary plugin archive file
     * It also creates parent folder if one doesn't exists
     *
     * @param object $remote
     * @return string
     */
    public function tempPluginZip($remote)
    {
        return $this->createPluginDirectory() . md5($remote->version) . '.tmp';
    }

    /**
     * Create plugin archive file with orifinal content from repository
     *
     * @param object $remote
     * @return void
     */
    public function createPluginFile($remote)
    {
        $this->deleteDirectory($this->tempPluginDirectory());
        $this->createPluginDirectory();

        $filePath = $this->tempPluginZip($remote);
        $content = base64_decode($remote->download_content);
        file_put_contents($filePath, $content);
    }

    /**
     * Delete folder and it's content
     *
     * @param string $dirname
     * @return void
     */
    private function deleteDirectory($dirname)
    {
        if (empty($dirname) || $dirname == '/') {
            return false;
        }

        if (is_dir($dirname)) {
            $dir_handle = opendir($dirname);
        }
        if (!isset($dir_handle) || !$dir_handle) {
            return false;
        }
        while ($file = readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                if (!is_dir($dirname . DIRECTORY_SEPARATOR . $file)) {
                    unlink($dirname . DIRECTORY_SEPARATOR . $file);
                } else {
                    $this->deleteDirectory($dirname . DIRECTORY_SEPARATOR . $file);
                }
            }
        }
        closedir($dir_handle);
        rmdir($dirname);
        return true;
    }
}
