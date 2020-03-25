<?php



namespace mod_lesson\event;

defined('MOODLE_INTERNAL') || die();

debugging('mod_lesson\event\highscores_viewed has been deprecated. Since the functionality no longer resides in the lesson module.',
        DEBUG_DEVELOPER);

class highscores_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'lesson';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public static function get_name() {
        return get_string('eventhighscoresviewed', 'mod_lesson');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/lesson/highscores.php', array('id' => $this->contextinstanceid));
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the highscores for the lesson activity with course module " .
            "id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        $lesson = $this->get_record_snapshot('lesson', $this->objectid);

        return array($this->courseid, 'lesson', 'view highscores', 'highscores.php?id=' . $this->contextinstanceid,
            $lesson->name, $this->contextinstanceid);
    }

    public static function get_objectid_mapping() {
                return false;
    }

    public static function get_other_mapping() {
                return false;
    }
}
