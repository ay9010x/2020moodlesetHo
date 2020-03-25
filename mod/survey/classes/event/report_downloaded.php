<?php



namespace mod_survey\event;

defined('MOODLE_INTERNAL') || die();


class report_downloaded extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'survey';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventreportdownloaded', 'mod_survey');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' downloaded the report for the survey with course module id '$this->contextinstanceid'.";
    }

    
    public function get_url() {
        $params = array('id' => $this->contextinstanceid, 'type' => $this->other['type']);
        if (isset($this->other['groupid'])) {
            $params['group'] = $this->other['groupid'];
        }
        return new \moodle_url("/mod/survey/download.php", $params);
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, "survey", "download", $this->get_url(), $this->objectid, $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (empty($this->other['type'])) {
            throw new \coding_exception('The \'type\' value must be set in other.');
        }
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
