<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


abstract class course_module_instance_list_viewed extends base{

    
    protected $modname;

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        if (strstr($this->component, 'mod_') === false) {
            throw new \coding_exception('The event name or namespace is invalid.');
        } else {
            $this->modname = str_replace('mod_', '', $this->component);
        }
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the instance list for the module '$this->modname' in the course " .
            "with id '$this->courseid'.";
    }

    
    public static function get_name() {
        return get_string('eventcoursemoduleinstancelistviewed', 'core');
    }

    
    public function get_url() {
        return new \moodle_url("/mod/$this->modname/index.php", array('id' => $this->courseid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, $this->modname, 'view all', 'index.php?id=' . $this->courseid, '');
    }


    
    protected function validate_data() {
        parent::validate_data();
        if ($this->contextlevel != CONTEXT_COURSE) {
            throw new \coding_exception('Context level must be CONTEXT_COURSE.');
        }
    }
}
