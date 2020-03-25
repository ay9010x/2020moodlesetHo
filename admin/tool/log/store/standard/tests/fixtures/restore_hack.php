<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

class logstore_standard_restore extends restore_controller {
    public static function hack_executing($state) {
        self::$executing = $state;
    }
}
