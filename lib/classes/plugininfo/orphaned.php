<?php


namespace core\plugininfo;

defined('MOODLE_INTERNAL') || die();



class orphaned extends base {
    public function is_uninstall_allowed() {
        return true;
    }

    
    public function is_enabled() {
        return null;
    }

    
    public function init_display_name() {
        $this->displayname = $this->component;
    }

    
    public static function get_enabled_plugins() {
        return null;
    }

    
    public static function get_plugins($type, $typerootdir, $typeclass, $pluginman) {
        $return = array();
        $plugins = $pluginman->get_installed_plugins($type);

        foreach ($plugins as $name => $version) {
            $plugin              = new $typeclass();
            $plugin->type        = $type;
            $plugin->typerootdir = $typerootdir;
            $plugin->name        = $name;
            $plugin->rootdir     = null;
            $plugin->displayname = $name;
            $plugin->versiondb   = $version;
            $plugin->pluginman   = $pluginman;
            $plugin->init_is_standard();

            $return[$name] = $plugin;
        }

        return $return;
    }
}
