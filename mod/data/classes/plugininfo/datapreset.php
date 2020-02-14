<?php


namespace mod_data\plugininfo;

use core\plugininfo\base;

defined('MOODLE_INTERNAL') || die();


class datapreset extends base {
    public function is_uninstall_allowed() {
        return true;
    }
}
