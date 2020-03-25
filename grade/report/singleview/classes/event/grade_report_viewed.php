<?php



namespace gradereport_singleview\event;

defined('MOODLE_INTERNAL') || die();


class grade_report_viewed extends \core\event\grade_report_viewed {

    
    public static function get_name() {
        return get_string('eventgradereportviewed', 'gradereport_singleview');
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' value must be set.');
        }
    }
}
