<?php



namespace logstore_database\test;

defined('MOODLE_INTERNAL') || die();

class store extends \logstore_database\log\store {
    
    public function is_event_ignored(\core\event\base $event) {
        return parent::is_event_ignored($event);
    }
}