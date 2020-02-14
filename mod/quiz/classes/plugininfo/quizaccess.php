<?php


namespace mod_quiz\plugininfo;

use core\plugininfo\base;

defined('MOODLE_INTERNAL') || die();


class quizaccess extends base {
    public function is_uninstall_allowed() {
                return !$this->is_standard();
    }
}
