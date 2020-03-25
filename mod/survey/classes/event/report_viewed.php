<?php



namespace mod_survey\event;

defined('MOODLE_INTERNAL') || die();


class report_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'survey';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventreportviewed', 'mod_survey');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the report for the survey with course module id '$this->contextinstanceid'.";
    }

    
    public function get_url() {
        $params = array();
        $params['id'] = $this->contextinstanceid;
        if (isset($this->other['action'])) {
            $params['action'] = $this->other['action'];
        }
        return new \moodle_url("/mod/survey/report.php", $params);
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, "survey", "view report", "report.php?id=" . $this->contextinstanceid, $this->objectid,
                     $this->contextinstanceid);
    }

    public static function get_objectid_mapping() {
        return array('db' => 'survey', 'restore' => 'survey');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['groupid'] = array('db' => 'groups', 'restore' => 'group');

        return $othermapped;
    }
}
