<?php


namespace mod_assignment\plugininfo;

use core\plugininfo\base;

defined('MOODLE_INTERNAL') || die();


class assignment extends base {
    
    public function is_enabled() {
        return false;
    }
}
