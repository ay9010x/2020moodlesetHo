<?php



namespace mod_lesson\event;

defined('MOODLE_INTERNAL') || die();


class page_updated extends \core\event\base {

    
    public static function create_from_lesson_page(\lesson_page $lessonpage, \context_module $context) {
        $data = array(
            'context' => $context,
            'objectid' => $lessonpage->properties()->id,
            'other' => array(
                'pagetype' => $lessonpage->get_typestring()
            )
        );
        return self::create($data);
    }


    
    protected function init() {
        $this->data['objecttable'] = 'lesson_pages';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventpageupdated', 'mod_lesson');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/lesson/view.php', array('id' => $this->contextinstanceid, 'pageid' => $this->objectid));
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has updated the ".$this->other['pagetype']." page with the ".
                "id '$this->objectid' in the lesson activity with course module id '$this->contextinstanceid'.";
    }

    
    protected function validate_data() {
        parent::validate_data();
                if (!$this->contextlevel === CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
        if (!isset($this->other['pagetype'])) {
            throw new \coding_exception('The \'pagetype\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'lesson_pages', 'restore' => 'lesson_page');
    }

    public static function get_other_mapping() {
                return false;
    }
}