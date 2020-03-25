<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class course_category_deleted extends base {

    
    private $coursecat;

    
    protected function init() {
        $this->data['objecttable'] = 'course_categories';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventcoursecategorydeleted');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the course category with id '$this->objectid'.";
    }

    
    public static function get_legacy_eventname() {
        return 'course_category_deleted';
    }

    
    protected function get_legacy_eventdata() {
        return $this->coursecat;
    }

    
    public function set_coursecat(\coursecat $coursecat) {
        $this->coursecat = $coursecat;
    }

    
    public function get_coursecat() {
        if ($this->is_restored()) {
            throw new \coding_exception('Function get_coursecat() can not be used on restored events.');
        }
        return $this->coursecat;
    }

    
    protected function get_legacy_logdata() {
        return array(SITEID, 'category', 'delete', 'index.php', $this->other['name'] . '(ID ' . $this->objectid . ')');
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['name'])) {
            throw new \coding_exception('The \'name\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
                return array('db' => 'course_categories', 'restore' => base::NOT_MAPPED);
    }

    public static function get_other_mapping() {
        return false;
    }
}
