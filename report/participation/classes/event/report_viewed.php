<?php


namespace report_participation\event;

defined('MOODLE_INTERNAL') || die();


class report_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventreportviewed', 'report_participation');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the course participation report for the course with id '$this->courseid'.";
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, "course", "report participation", "report/participation/index.php?id=" . $this->courseid,
                $this->courseid);
    }

    
    public function get_url() {
        return new \moodle_url('/report/participation/index.php', array('id' => $this->courseid,
            'instanceid' => $this->other['instanceid'], 'roleid' => $this->other['roleid']));
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (empty($this->other['instanceid'])) {
            throw new \coding_exception('The \'instanceid\' value must be set in other.');
        }

        if (empty($this->other['roleid'])) {
            throw new \coding_exception('The \'roleid\' value must be set in other.');
        }

        if (!isset($this->other['groupid'])) {
            throw new \coding_exception('The \'groupid\' value must be set in other.');
        }

        if (!isset($this->other['timefrom'])) {
            throw new \coding_exception('The \'timefrom\' value must be set in other.');
        }

        if (!isset($this->other['action'])) {
            throw new \coding_exception('The \'action\' value must be set in other.');
        }

    }

    public static function get_other_mapping() {
        $othermapped = array();
                $othermapped['instanceid'] = array('db' => 'course_modules', 'restore' => 'course_module');

        $othermapped['roleid'] = array('db' => 'role', 'restore' => 'role');
        $othermapped['groupid'] = array('db' => 'groups', 'restore' => 'group');

        return $othermapped;

    }
}

