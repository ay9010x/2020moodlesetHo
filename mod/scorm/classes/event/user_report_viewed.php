<?php



namespace mod_scorm\event;
defined('MOODLE_INTERNAL') || die();


class user_report_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the scorm user report for the user with id '$this->relateduserid'.";
    }

    
    public static function get_name() {
        return get_string('eventuserreportviewed', 'mod_scorm');
    }

    
    public function get_url() {
        $params = array(
            'id' => $this->contextinstanceid,
            'user' => $this->relateduserid,
            'attempt' => $this->other['attemptid']
        );
        return new \moodle_url('/mod/scorm/userreport.php', $params);
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'scorm', 'userreport', 'report/userreport.php?id=' .
                $this->contextinstanceid . '&user=' . $this->relateduserid . '&attempt=' . $this->other['attemptid'],
                $this->other['instanceid'], $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
        if (empty($this->other['attemptid'])) {
            throw new \coding_exception('The \'attemptid\' value must be set in other.');
        }
        if (empty($this->other['instanceid'])) {
            throw new \coding_exception('The \'instanceid\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['instanceid'] = array('db' => 'scorm', 'restore' => 'scorm');

        return $othermapped;
    }
}
