<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class course_viewed extends base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public function get_description() {

                $sectionstr = '';
        if (!empty($this->other['coursesectionnumber'])) {
            $sectionstr = "section number '{$this->other['coursesectionnumber']}' of the ";
        } else if (!empty($this->other['coursesectionid'])) {
            $sectionstr = "section number '{$this->other['coursesectionid']}' of the ";
        }
        $description = "The user with id '$this->userid' viewed the " . $sectionstr . "course with id '$this->courseid'.";

        return $description;
    }

    
    public static function get_name() {
        return get_string('eventcourseviewed', 'core');
    }

    
    public function get_url() {
        global $CFG;

                $sectionnumber = null;
        if (isset($this->other['coursesectionnumber'])) {
            $sectionnumber = $this->other['coursesectionnumber'];
        } else if (isset($this->other['coursesectionid'])) {
            $sectionnumber = $this->other['coursesectionid'];
        }
        require_once($CFG->dirroot . '/course/lib.php');
        try {
            return course_get_url($this->courseid, $sectionnumber);
        } catch (\Exception $e) {
            return null;
        }
    }

    
    protected function get_legacy_logdata() {
        if ($this->courseid == SITEID and !isloggedin()) {
                        return null;
        }

                if (isset($this->other['coursesectionnumber']) || isset($this->other['coursesectionid'])) {
            if (isset($this->other['coursesectionnumber'])) {
                $sectionnumber = $this->other['coursesectionnumber'];
            } else {
                $sectionnumber = $this->other['coursesectionid'];
            }
            return array($this->courseid, 'course', 'view section', 'view.php?id=' . $this->courseid . '&amp;section='
                    . $sectionnumber, $sectionnumber);
        }
        return array($this->courseid, 'course', 'view', 'view.php?id=' . $this->courseid, $this->courseid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if ($this->contextlevel != CONTEXT_COURSE) {
            throw new \coding_exception('Context level must be CONTEXT_COURSE.');
        }
    }

    public static function get_other_mapping() {
                return false;
    }
}
