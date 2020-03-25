<?php



namespace mod_lesson\event;

defined('MOODLE_INTERNAL') || die();

debugging('mod_lesson\event\highscore_added has been deprecated. Since the functionality no longer resides in the lesson module.',
        DEBUG_DEVELOPER);


class highscore_added extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'lesson_high_scores';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public static function get_name() {
        return get_string('eventhighscoreadded', 'mod_lesson');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/lesson/highscores.php', array('id' => $this->contextinstanceid));
    }

    
    public function get_description() {
        return "The user with id '$this->userid' added a new highscore to the lesson activity with course module " .
            "id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'lesson', 'update highscores', 'highscores.php?id=' . $this->contextinstanceid,
            $this->other['nickname'], $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['lessonid'])) {
            throw new \coding_exception('The \'lessonid\' value must be set in other.');
        }

        if (!isset($this->other['nickname'])) {
            throw new \coding_exception('The \'nickname\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
                return false;
    }

    public static function get_other_mapping() {
                return false;
    }
}
