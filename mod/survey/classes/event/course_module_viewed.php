<?php



namespace mod_survey\event;

defined('MOODLE_INTERNAL') || die();


class course_module_viewed extends \core\event\course_module_viewed {

    
    protected function init() {
        $this->data['objecttable'] = 'survey';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, $this->objecttable, 'view '. $this->other['viewed'], 'view.php?id=' .
            $this->contextinstanceid, $this->objectid, $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (empty($this->other['viewed'])) {
            throw new \coding_exception('Other must contain the key viewed.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'survey', 'restore' => 'survey');
    }

    public static function get_other_mapping() {
                return false;
    }
}
