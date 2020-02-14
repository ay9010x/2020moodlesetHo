<?php



namespace mod_lesson\event;

defined('MOODLE_INTERNAL') || die();


class page_moved extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'lesson_pages';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventpagemoved', 'mod_lesson');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/lesson/view.php', array('id' => $this->contextinstanceid, 'pageid' => $this->objectid));
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has moved a ".$this->other['pagetype']." page with the ".
                "id '$this->objectid' to the slot after the page with the id '".$this->other['prevpageid'].
                "' and before the page with the id '".$this->other['nextpageid'].
                "' in the lesson activity with course module id '$this->contextinstanceid'.";
    }

    
    protected function validate_data() {
        parent::validate_data();
                if (!$this->contextlevel === CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
        if (!isset($this->other['pagetype'])) {
            throw new \coding_exception('The \'pagetype\' value must be set in other.');
        }
        if (!isset($this->other['prevpageid'])) {
            throw new \coding_exception('The \'prevpageid\' value must be set in other.');
        }
        if (!isset($this->other['nextpageid'])) {
            throw new \coding_exception('The \'nextpageid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'lesson_pages', 'restore' => 'lesson_page');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['prevpageid'] = array('db' => 'lesson_pages', 'restore' => 'lesson_page');
        $othermapped['nextpageid'] = array('db' => 'lesson_pages', 'restore' => 'lesson_page');

        return $othermapped;
    }
}
