<?php


namespace core\plugininfo;

use admin_settingpage;

defined('MOODLE_INTERNAL') || die();


class availability extends base {
    public static function get_enabled_plugins() {
        global $DB;

                $plugins = \core_plugin_manager::instance()->get_installed_plugins('availability');
        if (!$plugins) {
            return array();
        }

                $enabled = array();
        foreach ($plugins as $plugin => $version) {
            $disabled = get_config('availability_' . $plugin, 'disabled');
            if (empty($disabled)) {
                $enabled[$plugin] = $plugin;
            }
        }

        return $enabled;
    }

    
    public function is_uninstall_allowed() {
        return true;
    }

    
    public function get_settings_section_name() {
        return 'availabilitysetting' . $this->name;
    }

    
    public function load_settings(\part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;         $ADMIN = $adminroot;         $plugininfo = $this;         $availability = $this; 
        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (!$hassiteconfig) {
            return;
        }

        $section = $this->get_settings_section_name();

        $settings = null;
        if (file_exists($this->full_path('settings.php'))) {
            $settings = new admin_settingpage($section, $this->displayname, 'moodle/site:config', $this->is_enabled() === false);
            include($this->full_path('settings.php'));         }
        if ($settings) {
            $ADMIN->add($parentnodename, $settings);
        }
    }
}
