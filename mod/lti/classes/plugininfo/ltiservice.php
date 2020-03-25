<?php


namespace mod_lti\plugininfo;

use core\plugininfo\base;

defined('MOODLE_INTERNAL') || die();



class ltiservice extends base {

    
    public function is_uninstall_allowed() {

        if ($this->is_standard()) {
            return false;
        }

        return true;

    }

}
