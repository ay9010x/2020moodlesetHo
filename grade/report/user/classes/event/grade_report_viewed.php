<?php



namespace gradereport_user\event;

defined('MOODLE_INTERNAL') || die();


class grade_report_viewed extends \core\event\grade_report_viewed {

    
    protected function init() {
        parent::init();
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public static function get_name() {
        return get_string('eventgradereportviewed', 'gradereport_user');
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' value must be set.');
        }
    }
}
