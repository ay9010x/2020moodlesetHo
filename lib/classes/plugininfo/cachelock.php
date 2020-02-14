<?php


namespace core\plugininfo;

defined('MOODLE_INTERNAL') || die();


class cachelock extends base {

    public function is_uninstall_allowed() {
        return false;
    }
}
