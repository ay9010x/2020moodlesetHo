<?php


namespace core\plugininfo;

defined('MOODLE_INTERNAL') || die();


class gradeexport extends base {

    public function is_uninstall_allowed() {
        return true;
    }
}
