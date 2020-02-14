<?php


namespace core\plugininfo;

use moodle_url, part_of_admin_tree, admin_settingpage, admin_externalpage, core_plugin_manager;

defined('MOODLE_INTERNAL') || die();


class dataformat extends base {

    
    public function init_display_name() {
        if (!get_string_manager()->string_exists('dataformat', $this->component)) {
            $this->displayname = '[dataformat,' . $this->component . ']';
        } else {
            $this->displayname = get_string('dataformat', $this->component);
        }
    }

    
    public static function get_plugins($type, $typerootdir, $typeclass, $pluginman) {
        global $CFG;
        $formats = parent::get_plugins($type, $typerootdir, $typeclass, $pluginman);

        if (!empty($CFG->dataformat_plugins_sortorder)) {
            $order = explode(',', $CFG->dataformat_plugins_sortorder);
            $order = array_merge(array_intersect($order, array_keys($formats)),
                        array_diff(array_keys($formats), $order));
        } else {
            $order = array_keys($formats);
        }
        $sortedformats = array();
        foreach ($order as $formatname) {
            $sortedformats[$formatname] = $formats[$formatname];
        }
        return $sortedformats;
    }

    
    public static function get_enabled_plugins() {
        $enabled = array();
        $plugins = core_plugin_manager::instance()->get_installed_plugins('dataformat');

        if (!$plugins) {
            return array();
        }

        $enabled = array();
        foreach ($plugins as $plugin => $version) {
            $disabled = get_config('dataformat_' . $plugin, 'disabled');
            if (empty($disabled)) {
                $enabled[$plugin] = $plugin;
            }
        }
        return $enabled;
    }

    
    public function get_settings_section_name() {
        return 'dataformatsetting' . $this->name;
    }

    
    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;         $ADMIN = $adminroot;         $plugininfo = $this;         $dataformat = $this;     
        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (!$hassiteconfig) {
            return;
        }
        if (file_exists($this->full_path('settings.php'))) {
            $fullpath = $this->full_path('settings.php');
        } else if (file_exists($this->full_path('dataformatsettings.php'))) {
            $fullpath = $this->full_path('dataformatsettings.php');
        } else {
            return;
        }

        $section = $this->get_settings_section_name();
        $settings = new admin_settingpage($section, $this->displayname, 'moodle/site:config', $this->is_enabled() === false);
        include($fullpath); 
        if ($settings) {
            $ADMIN->add($parentnodename, $settings);
        }
    }

    
    public function is_uninstall_allowed() {
        return true;
    }

    
    public static function get_manage_url() {
        return new moodle_url('/admin/settings.php?section=managedataformats');
    }

}

