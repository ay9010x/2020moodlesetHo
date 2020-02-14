<?php



namespace mod_forum\event;

defined('MOODLE_INTERNAL') || die();


class course_searched extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public function get_description() {
        $searchterm = s($this->other['searchterm']);
        return "The user with id '$this->userid' has searched the course with id '$this->courseid' for forum posts " .
            "containing \"{$searchterm}\".";
    }

    
    public static function get_name() {
        return get_string('eventcoursesearched', 'mod_forum');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/forum/search.php',
            array('id' => $this->courseid, 'search' => $this->other['searchterm']));
    }

    
    protected function get_legacy_logdata() {
                $logurl = substr($this->get_url()->out_as_local_url(), strlen('/mod/forum/'));

        return array($this->courseid, 'forum', 'search', $logurl, $this->other['searchterm']);
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['searchterm'])) {
            throw new \coding_exception('The \'searchterm\' value must be set in other.');
        }

        if ($this->contextlevel != CONTEXT_COURSE) {
            throw new \coding_exception('Context level must be CONTEXT_COURSE.');
        }
    }

    public static function get_other_mapping() {
        return false;
    }
}

