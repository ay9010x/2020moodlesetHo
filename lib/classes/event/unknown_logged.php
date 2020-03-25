<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class unknown_logged extends base {
    public function init() {
        throw new \coding_exception('unknown events cannot be triggered');
    }

    public static function get_name() {
        return get_string('eventunknownlogged', 'core');
    }

    public function get_description() {
        return 'Unknown event (' . $this->eventname . ')';
    }
}
