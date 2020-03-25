<?php



namespace mod_choice\event;
defined('MOODLE_INTERNAL') || die();


class report_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'choice';
    }

    
    public static function get_name() {
        return get_string('eventreportviewed', 'mod_choice');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has viewed the report for the choice activity with course module id
            '$this->contextinstanceid'";
    }

    
    public function get_url() {
        return new \moodle_url('/mod/choice/report.php', array('id' => $this->contextinstanceid));
    }

    
    protected function get_legacy_logdata() {
        $url = new \moodle_url('report.php', array('id' => $this->contextinstanceid));
        return array($this->courseid, 'choice', 'report', $url->out(), $this->objectid, $this->contextinstanceid);
    }

    public static function get_objectid_mapping() {
        return array('db' => 'choice', 'restore' => 'choice');
    }

    public static function get_other_mapping() {
                return false;
    }
}
