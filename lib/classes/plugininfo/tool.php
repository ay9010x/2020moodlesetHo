<?php


namespace core\plugininfo;

use moodle_url;

defined('MOODLE_INTERNAL') || die();


class tool extends base {

    public function is_uninstall_allowed() {
        return true;
    }

    
    public function is_enabled() {
        return true;
    }

    
    public static function get_manage_url() {
        return new moodle_url('/admin/tools.php');
    }

    
    public function load_settings(\part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;         $ADMIN = $adminroot;         $plugininfo = $this; 
        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (file_exists($this->full_path('settings.php'))) {
            include($this->full_path('settings.php'));
        }
    }
}
