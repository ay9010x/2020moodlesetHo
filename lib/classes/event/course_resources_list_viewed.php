<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class course_resources_list_viewed extends base {

    
    private $resourceslist = null;

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the list of resources in the course with id '$this->courseid'.";
    }

    
    public static function get_name() {
        return get_string('eventcoursemoduleinstancelistviewed', 'core');
    }

    
    public function get_url() {
        return new \moodle_url("/course/resources.php", array('id' => $this->courseid));
    }

    
    public function set_legacy_logdata($data) {
        $this->resourceslist = $data;
    }

    
    protected function get_legacy_logdata() {
        if (empty($this->resourceslist)) {
            return null;
        }
        $logs = array();
        foreach ($this->resourceslist as $resourcename) {
            $logs[] = array($this->courseid, $resourcename, 'view all', 'index.php?id=' . $this->courseid, '');
        }
        return $logs;
    }

    
    protected function validate_data() {
        if ($this->contextlevel != CONTEXT_COURSE) {
            throw new \coding_exception('Context passed must be course context.');
        }
    }
}
