<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


abstract class course_module_viewed extends base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the '{$this->objecttable}' activity with " .
            "course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventcoursemoduleviewed', 'core');
    }

    
    public function get_url() {
        return new \moodle_url("/mod/$this->objecttable/view.php", array('id' => $this->contextinstanceid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, $this->objecttable, 'view', 'view.php?id=' . $this->contextinstanceid, $this->objectid,
                     $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();
                if (empty($this->objectid) || empty($this->objecttable)) {
            throw new \coding_exception('The course_module_viewed event must define objectid and object table.');
        }
                if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }
}
