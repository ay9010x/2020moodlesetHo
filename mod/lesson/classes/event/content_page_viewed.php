<?php



namespace mod_lesson\event;

defined('MOODLE_INTERNAL') || die();


class content_page_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'lesson_pages';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public static function get_name() {
        return get_string('eventcontentpageviewed', 'mod_lesson');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/lesson/view.php', array('id' => $this->contextinstanceid, 'pageid' => $this->objectid));
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has viewed the content page with id '$this->objectid' in " .
            "the lesson activity with course module id '$this->contextinstanceid'.";
    }

    
    protected function validate_data() {
        parent::validate_data();
                if (!$this->contextlevel === CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'lesson_pages', 'restore' => 'lesson_page');
    }
}
