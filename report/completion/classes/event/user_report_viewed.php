<?php



namespace report_completion\event;

defined('MOODLE_INTERNAL') || die();


class user_report_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventuserreportviewed', 'report_completion');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the user completion report for the user with id '$this->relateduserid'.";
    }

    
    protected function get_legacy_logdata() {
        $url = 'report/completion/user.php?id=' . $this->relateduserid . '&course=' . $this->courseid;
        return array($this->courseid, 'course', 'report completion', $url, $this->courseid);
    }

    
    public function get_url() {
        return new \moodle_url('/report/completion/user.php', array('course' => $this->courseid, 'id' => $this->relateduserid));
    }

    
    protected function validate_data() {
        parent::validate_data();
        if ($this->contextlevel != CONTEXT_COURSE) {
            throw new \coding_exception('Context level must be CONTEXT_COURSE.');
        }

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
    }
}
