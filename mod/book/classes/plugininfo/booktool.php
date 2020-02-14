<?php


namespace mod_book\plugininfo;

use core\plugininfo\base;

defined('MOODLE_INTERNAL') || die();


class booktool extends base {
    public function is_uninstall_allowed() {
        return true;
    }
}
