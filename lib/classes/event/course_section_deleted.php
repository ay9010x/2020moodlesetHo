<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class course_section_deleted extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'course_sections';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventcoursesectiondeleted');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted section number '{$this->other['sectionnum']}' " .
                "(section name '{$this->other['sectionname']}') for the course with id '$this->courseid'";
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'course', 'delete section', 'view.php?id=' . $this->courseid, $this->other['sectionnum']);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['sectionnum'])) {
            throw new \coding_exception('The \'sectionnum\' value must be set in other.');
        }
        if (!isset($this->other['sectionname'])) {
            throw new \coding_exception('The \'sectionname\' value must be set in other.');
        }
    }
}
