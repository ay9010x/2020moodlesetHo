<?php



namespace gradereport_history\event;

defined('MOODLE_INTERNAL') || die();


class grade_report_viewed extends \core\event\grade_report_viewed {

    
    public static function get_name() {
        return get_string('eventgradereportviewed', 'gradereport_history');
    }
}
