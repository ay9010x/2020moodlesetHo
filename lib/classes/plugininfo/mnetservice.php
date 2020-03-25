<?php


namespace core\plugininfo;

defined('MOODLE_INTERNAL') || die();


class mnetservice extends base {

    public function is_enabled() {
        global $CFG;

        if (empty($CFG->mnet_dispatcher_mode) || $CFG->mnet_dispatcher_mode !== 'strict') {
            return false;
        } else {
            return parent::is_enabled();
        }
    }
}
