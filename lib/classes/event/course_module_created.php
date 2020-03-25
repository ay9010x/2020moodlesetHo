<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class course_module_created extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'course_modules';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static final function create_from_cm($cm, $modcontext = null) {
                if (empty($modcontext)) {
            $modcontext = \context_module::instance($cm->id);
        }

                $event = static::create(array(
            'context'  => $modcontext,
            'objectid' => $cm->id,
            'other'    => array(
                'modulename' => $cm->modname,
                'instanceid' => $cm->instance,
                'name'       => $cm->name,
            )
        ));
        return $event;
    }

    
    public static function get_name() {
        return get_string('eventcoursemodulecreated', 'core');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' created the '{$this->other['modulename']}' activity with " .
            "course module id '$this->contextinstanceid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/mod/' . $this->other['modulename'] . '/view.php', array('id' => $this->objectid));
    }

    
    public static function get_legacy_eventname() {
        return 'mod_created';
    }

    
    protected function get_legacy_eventdata() {
        $eventdata = new \stdClass();
        $eventdata->modulename = $this->other['modulename'];
        $eventdata->name       = $this->other['name'];
        $eventdata->cmid       = $this->objectid;
        $eventdata->courseid   = $this->courseid;
        $eventdata->userid     = $this->userid;
        return $eventdata;
    }

    
    protected function get_legacy_logdata() {
        $log1 = array($this->courseid, "course", "add mod", "../mod/" . $this->other['modulename'] . "/view.php?id=" .
                $this->objectid, $this->other['modulename'] . " " . $this->other['instanceid']);
        $log2 = array($this->courseid, $this->other['modulename'], "add",
            "view.php?id={$this->objectid}",
                "{$this->other['instanceid']}", $this->objectid);
        return array($log1, $log2);
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['modulename'])) {
            throw new \coding_exception('The \'modulename\' value must be set in other.');
        }
        if (!isset($this->other['instanceid'])) {
            throw new \coding_exception('The \'instanceid\' value must be set in other.');
        }
        if (!isset($this->other['name'])) {
            throw new \coding_exception('The \'name\' value must be set in other.');
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

