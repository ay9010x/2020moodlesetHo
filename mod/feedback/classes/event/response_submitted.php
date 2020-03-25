<?php



namespace mod_feedback\event;
defined('MOODLE_INTERNAL') || die();


class response_submitted extends \core\event\base {

    
    protected function init() {
        global $CFG;

        require_once($CFG->dirroot.'/mod/feedback/lib.php');
        $this->data['objecttable'] = 'feedback_completed';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public static function create_from_record($completed, $cm) {
        $event = self::create(array(
            'relateduserid' => $completed->userid,
            'objectid' => $completed->id,
            'context' => \context_module::instance($cm->id),
            'anonymous' => ($completed->anonymous_response == FEEDBACK_ANONYMOUS_YES),
            'other' => array(
                'cmid' => $cm->id,
                'instanceid' => $completed->feedback,
                'anonymous' => $completed->anonymous_response             )
        ));
        $event->add_record_snapshot('feedback_completed', $completed);
        return $event;
    }

    
    public static function get_name() {
        return get_string('eventresponsesubmitted', 'mod_feedback');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' submitted response for 'feedback' activity with "
                . "course module id '$this->contextinstanceid'.";
    }

    
    public function get_url() {
        if ($this->anonymous) {
            return new \moodle_url('/mod/feedback/show_entries.php', array('id' => $this->other['cmid'],
                    'showcompleted' => $this->objectid));
        } else {
            return new \moodle_url('/mod/feedback/show_entries.php' , array('id' => $this->other['cmid'],
                    'userid' => $this->userid, 'showcompleted' => $this->objectid));
        }
    }

    
    protected function get_legacy_logdata() {
        if ($this->anonymous) {
            return null;
        } else {
            return array($this->courseid, 'feedback', 'submit', 'view.php?id=' . $this->other['cmid'],
                    $this->other['instanceid'], $this->other['cmid'], $this->relateduserid);
        }
    }

    
    public function can_view($userorid = null) {
        global $USER;
        debugging('can_view() method is deprecated, use anonymous flag instead if necessary.', DEBUG_DEVELOPER);

        if (empty($userorid)) {
            $userorid = $USER;
        }
        if ($this->anonymous) {
            return is_siteadmin($userorid);
        } else {
            return has_capability('mod/feedback:viewreports', $this->context, $userorid);
        }
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
        if (!isset($this->other['anonymous'])) {
            throw new \coding_exception('The \'anonymous\' value must be set in other.');
        }
        if (!isset($this->other['cmid'])) {
            throw new \coding_exception('The \'cmid\' value must be set in other.');
        }
        if (!isset($this->other['instanceid'])) {
            throw new \coding_exception('The \'instanceid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'feedback_completed', 'restore' => 'feedback_completed');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['cmid'] = array('db' => 'course_modules', 'restore' => 'course_module');
        $othermapped['instanceid'] = array('db' => 'feedback', 'restore' => 'feedback');

        return $othermapped;
    }
}

