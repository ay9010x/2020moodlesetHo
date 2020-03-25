<?php



namespace mod_survey\event;

defined('MOODLE_INTERNAL') || die();


class response_submitted extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public static function get_name() {
        return get_string('eventresponsesubmitted', 'mod_survey');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' submitted a response for the survey with course module id '$this->contextinstanceid'.";
    }

    
    public function get_url() {
        return new \moodle_url("/mod/survey/view.php", array('id' => $this->contextinstanceid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, "survey", "submit", "view.php?id=" . $this->contextinstanceid, $this->other['surveyid'],
                     $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (empty($this->other['surveyid'])) {
            throw new \coding_exception('The \'surveyid\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['surveyid'] = array('db' => 'survey', 'restore' => 'survey');

        return $othermapped;
    }
}
