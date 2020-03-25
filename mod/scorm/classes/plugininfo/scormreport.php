<?php


namespace mod_scorm\plugininfo;

use core\plugininfo\base;

defined('MOODLE_INTERNAL') || die();


class scormreport extends base {
    public function is_uninstall_allowed() {
        return true;
    }
}
