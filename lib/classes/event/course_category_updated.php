<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class course_category_updated extends base {

    
    private $legacylogdata;

    
    protected function init() {
        $this->data['objecttable'] = 'course_categories';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventcoursecategoryupdated');
    }

    
    public function get_url() {
        return new \moodle_url('/course/editcategory.php', array('id' => $this->objectid));
    }

    
    public function get_description() {
        return "The user with id '$this->userid' updated the course category with id '$this->objectid'.";
    }

    
    public function set_legacy_logdata($logdata) {
        $this->legacylogdata = $logdata;
    }

    
    protected function get_legacy_logdata() {
        if (!empty($this->legacylogdata)) {
            return $this->legacylogdata;
        }

        return array(SITEID, 'category', 'update', 'editcategory.php?id=' . $this->objectid, $this->objectid);
    }

    public static function get_objectid_mapping() {
                return array('db' => 'course_categories', 'restore' => base::NOT_MAPPED);
    }
}
