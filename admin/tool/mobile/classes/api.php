<?php


namespace tool_mobile;

use core_component;
use core_plugin_manager;


class api {

    
    public static function get_plugins_supporting_mobile() {
        global $CFG;
        require_once($CFG->libdir . '/adminlib.php');

        $pluginsinfo = [];
        $plugintypes = core_component::get_plugin_types();

        foreach ($plugintypes as $plugintype => $unused) {
                        $pluginswithfile = core_component::get_plugin_list_with_file($plugintype, 'db' . DIRECTORY_SEPARATOR . 'mobile.php');
            foreach ($pluginswithfile as $plugin => $notused) {
                $path = core_component::get_plugin_directory($plugintype, $plugin);
                $component = $plugintype . '_' . $plugin;
                $version = get_component_version($component);

                require_once("$path/db/mobile.php");
                foreach ($addons as $addonname => $addoninfo) {
                    $plugininfo = array(
                        'component' => $component,
                        'version' => $version,
                        'addon' => $addonname,
                        'dependencies' => !empty($addoninfo['dependencies']) ? $addoninfo['dependencies'] : array(),
                        'fileurl' => '',
                        'filehash' => '',
                        'filesize' => 0
                    );

                                        $package = $path . DIRECTORY_SEPARATOR . 'mobile' . DIRECTORY_SEPARATOR . $addonname . '.zip';
                    if (file_exists($package)) {
                        $plugininfo['fileurl'] = $CFG->wwwroot . '' . str_replace($CFG->dirroot, '', $package);
                        $plugininfo['filehash'] = sha1_file($package);
                        $plugininfo['filesize'] = filesize($package);
                    }
                    $pluginsinfo[] = $plugininfo;
                }
            }
        }
        return $pluginsinfo;
    }

}
