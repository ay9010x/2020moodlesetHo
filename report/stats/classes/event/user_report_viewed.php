<?php


namespace report_stats\event;

defined('MOODLE_INTERNAL') || die();


class user_report_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventuserreportviewed', 'report_stats');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the user statistics report for the user with id '$this->relateduserid'.";
    }

    
    protected function get_legacy_logdata() {
        $url = 'report/stats/user.php?id=' . $this->relateduserid . '&course=' . $this->courseid;
        return (array($this->courseid, 'course', 'report stats', $url, $this->courseid));
    }

    
    public function get_url() {
        return new \moodle_url('/report/stats/user.php', array('id' => $this->relateduserid, 'course' => $this->courseid));
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (empty($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
    }
}

