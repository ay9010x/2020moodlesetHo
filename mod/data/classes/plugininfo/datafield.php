<?php


namespace mod_data\plugininfo;

use core\plugininfo\base;

defined('MOODLE_INTERNAL') || die();


class datafield extends base {
    public function is_uninstall_allowed() {
        global $DB;
        return !$DB->record_exists('data_fields', array('type'=>$this->name));
    }
}
