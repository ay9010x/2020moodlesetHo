<?php


namespace report_stats\event;

defined('MOODLE_INTERNAL') || die();


class report_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventreportviewed', 'report_stats');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the statistics report for the course with id '$this->courseid'.";
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, "course", "report stats", "report/stats/index.php?course=$this->courseid", $this->courseid);
    }

    
    public function get_url() {
        return new \moodle_url('/report/stats/index.php', array('id' => $this->courseid, 'mode' => $this->other['mode'],
                'report' => $this->other['report']));
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['report'])) {
            throw new \coding_exception('The \'report\' value must be set in other.');
        }

        if (!isset($this->other['time'])) {
            throw new \coding_exception('The \'time\' value must be set in other.');
        }

        if (!isset($this->other['mode'])) {
            throw new \coding_exception('The \'mode\' value must be set in other.');
        }

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
    }

    public static function get_other_mapping() {
                return array();
    }
}

