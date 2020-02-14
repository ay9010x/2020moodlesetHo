<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class course_category_created extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'course_categories';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventcoursecategorycreated');
    }

    
    public function get_url() {
        return new \moodle_url('/course/management.php', array('categoryid' => $this->objectid));
    }

    
    public function get_description() {
        return "The user with id '$this->userid' created the course category with id '$this->objectid'.";
    }

    
    protected function get_legacy_logdata() {
        return array(SITEID, 'category', 'add', 'editcategory.php?id=' . $this->objectid, $this->objectid);
    }

    public static function get_objectid_mapping() {
                return array('db' => 'course_categories', 'restore' => base::NOT_MAPPED);
    }
}
