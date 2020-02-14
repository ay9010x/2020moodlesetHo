<?php


namespace core\plugininfo;

use moodle_url;

defined('MOODLE_INTERNAL') || die();


class profilefield extends base {

    public function is_uninstall_allowed() {
        global $DB;
        return !$DB->record_exists('user_info_field', array('datatype'=>$this->name));
    }

    
    public static function get_manage_url() {
        return new moodle_url('/user/profile/index.php');
    }
}
