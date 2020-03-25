<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class course_module_deleted extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'course_modules';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventcoursemoduledeleted', 'core');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the '{$this->other['modulename']}' activity with " .
            "course module id '$this->contextinstanceid'.";
    }

    
    public static function get_legacy_eventname() {
        return 'mod_deleted';
    }

    
    protected function get_legacy_eventdata() {
        $eventdata = new \stdClass();
        $eventdata->modulename = $this->other['modulename'];
        $eventdata->cmid       = $this->objectid;
        $eventdata->courseid   = $this->courseid;
        $eventdata->userid     = $this->userid;
        return $eventdata;
    }

    
    protected function get_legacy_logdata() {
        return array ($this->courseid, "course", "delete mod", "view.php?id=$this->courseid",
                $this->other['modulename'] . " " . $this->other['instanceid'], $this->objectid);
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['modulename'])) {
            throw new \coding_exception('The \'modulename\' value must be set in other.');
        }
        if (!isset($this->other['instanceid'])) {
            throw new \coding_exception('The \'instanceid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'course_modules', 'restore' => 'course_module');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['instanceid'] = base::NOT_MAPPED;

        return $othermapped;
    }
}

