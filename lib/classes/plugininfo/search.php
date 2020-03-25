<?php


namespace core\plugininfo;

defined('MOODLE_INTERNAL') || die();


class search extends base {

    
    public function is_uninstall_allowed() {
        return true;
    }

    
    public function get_settings_section_name() {
        return 'searchsetting' . $this->name;
    }
}
