<?php


namespace core\plugininfo;

defined('MOODLE_INTERNAL') || die();


class gradeimport extends base {

    public function is_uninstall_allowed() {
        return true;
    }
}
