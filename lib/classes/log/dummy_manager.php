<?php



namespace core\log;

defined('MOODLE_INTERNAL') || die();

class dummy_manager implements manager {
    public function get_readers($interface = null) {
        return array();
    }

    public function dispose() {
    }

    public function get_supported_logstores($component) {
        return array();
    }
}
