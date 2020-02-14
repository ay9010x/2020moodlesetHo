<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class course_section_updated extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'course_sections';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventcoursesectionupdated');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' updated section number '{$this->other['sectionnum']}' for the " .
            "course with id '$this->courseid'";
    }

    
    public function get_url() {
        return new \moodle_url('/course/editsection.php', array('id' => $this->objectid));
    }

    
    protected function get_legacy_logdata() {
        $sectiondata = $this->get_record_snapshot('course_sections', $this->objectid);
        return array($this->courseid, 'course', 'editsection', 'editsection.php?id=' . $this->objectid, $sectiondata->section);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['sectionnum'])) {
            throw new \coding_exception('The \'sectionnum\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'course_sections', 'restore' => 'course_section');
    }

    public static function get_other_mapping() {
                return false;
    }
}
