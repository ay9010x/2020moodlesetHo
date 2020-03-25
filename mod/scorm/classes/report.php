<?php


namespace mod_scorm;




defined('MOODLE_INTERNAL') || die();

class report {
    
    public function display($scorm, $cm, $course, $download) {
                return true;
    }
    
    public function canview($contextmodule) {
        return true;
    }
}
