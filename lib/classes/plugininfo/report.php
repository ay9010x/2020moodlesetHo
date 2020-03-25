<?php


namespace core\plugininfo;

use moodle_url;

defined('MOODLE_INTERNAL') || die();


class report extends base {

    public function is_uninstall_allowed() {
        return true;
    }

    
    public static function get_manage_url() {
        return new moodle_url('/admin/reports.php');
    }
}
