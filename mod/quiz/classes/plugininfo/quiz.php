<?php


namespace mod_quiz\plugininfo;

use core\plugininfo\base;

defined('MOODLE_INTERNAL') || die();


class quiz extends base {
    public function is_uninstall_allowed() {
        return true;
    }

    
    public function uninstall_cleanup() {
        global $DB;

        
        $DB->delete_records('quiz_reports', array('name'=>$this->name));

        parent::uninstall_cleanup();
    }
}
