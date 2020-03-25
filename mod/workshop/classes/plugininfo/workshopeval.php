<?php


namespace mod_workshop\plugininfo;

use core\plugininfo\base;

defined('MOODLE_INTERNAL') || die();


class workshopeval extends base {
    public function is_uninstall_allowed() {
        if ($this->is_standard()) {
            return false;
        }
        return true;
    }
}
