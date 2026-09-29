<?php

namespace LicenseExample\Plugin;

/**
 * Copy license/OAuth options from the legacy bridge prefix (LP_{slug}_) to the SDK prefix (md5(slug)_).
 */
class LegacyOptionsMigration
{
    public static function migrate(string $pluginFile): void
    {
        $slug = plugin_basename($pluginFile);
        $oldPrefix = 'LP_' . $slug . '_';
        $newPrefix = md5($slug) . '_';

        foreach (['my_license_key', 'my_client_id', 'my_client_secret', 'my_access_token'] as $key) {
            $newOption = $newPrefix . $key;
            $oldValue = get_option($oldPrefix . $key);

            if ($oldValue !== false && $oldValue !== '' && empty(get_option($newOption))) {
                update_option($newOption, $oldValue);
            }
        }
    }
}
