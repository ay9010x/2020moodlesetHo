<?php


namespace core\plugininfo;

defined('MOODLE_INTERNAL') || die();


class coursereport extends base {

    public function is_uninstall_allowed() {
        return true;
    }
}
