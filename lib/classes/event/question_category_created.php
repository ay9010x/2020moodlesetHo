<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class question_category_created extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'question_categories';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventquestioncategorycreated', 'question');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' created the question category with id '$this->objectid'.";
    }

    
    public function get_url() {
        if ($this->courseid) {
            $cat = $this->objectid . ',' . $this->contextid;
            if ($this->contextlevel == CONTEXT_MODULE) {
                return new \moodle_url('/question/edit.php', array('cmid' => $this->contextinstanceid, 'cat' => $cat));
            }
            return new \moodle_url('/question/edit.php', array('courseid' => $this->courseid, 'cat' => $cat));
        }

                                return new \moodle_url('/question/category.php', array('courseid' => SITEID, 'edit' => $this->objectid));
    }

    
    protected function get_legacy_logdata() {
        if ($this->contextlevel == CONTEXT_MODULE) {
            return array($this->courseid, 'quiz', 'addcategory', 'view.php?id=' . $this->contextinstanceid,
                $this->objectid, $this->contextinstanceid);
        }
                return null;
    }

    public static function get_objectid_mapping() {
        return array('db' => 'question_categories', 'restore' => 'question_category');
    }
}
