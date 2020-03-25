<?php


namespace editor_atto\plugininfo;

use core\plugininfo\base;

defined('MOODLE_INTERNAL') || die();


class atto extends base {

    
    public function is_uninstall_allowed() {
        return true;
    }

    
    public static function get_manage_url() {
        return new \moodle_url('/admin/settings.php', array('section'=>'editorsettingsatto'));
    }

    
    public function load_settings(\part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;         $ADMIN = $adminroot;         $plugininfo = $this; 
        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (!$hassiteconfig or !file_exists($this->full_path('settings.php'))) {
            return;
        }

        $section = $this->get_settings_section_name();
        $settings = new \admin_settingpage($section, $this->displayname, 'moodle/site:config', $this->is_enabled() === false);
        include($this->full_path('settings.php')); 
        if ($settings) {
            $ADMIN->add($parentnodename, $settings);
        }
    }

    
    public function get_settings_section_name() {
        return 'atto_' . $this->name . '_settings';
    }
}
