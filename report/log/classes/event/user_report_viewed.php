<?php


namespace report_log\event;

defined('MOODLE_INTERNAL') || die();


class user_report_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventuserreportviewed', 'report_log');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the user log report for the user with id '$this->relateduserid'.";
    }

    
    protected function get_legacy_logdata() {
        $url = 'report/log/user.php?id=' . $this->relateduserid . '&course=' . $this->courseid . '&mode=' . $this->other['mode'];
        return array($this->courseid, 'course', 'report log', $url, $this->courseid);
    }

    
    public function get_url() {
        return new \moodle_url('/report/log/user.php', array('course' => $this->courseid, 'id' => $this->relateduserid,
                'mode' => $this->other['mode']));
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (empty($this->other['mode'])) {
            throw new \coding_exception('The \'mode\' value must be set in other.');
        }

        if (empty($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
    }

    public static function get_other_mapping() {
                return false;
    }
}
