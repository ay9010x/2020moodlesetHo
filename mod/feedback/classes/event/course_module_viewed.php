<?php



namespace mod_feedback\event;
defined('MOODLE_INTERNAL') || die();


class course_module_viewed extends \core\event\course_module_viewed {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'feedback';
    }

    
    public static function create_from_record($feedback, $cm, $course) {
        $event = self::create(array(
            'objectid' => $feedback->id,
            'context' => \context_module::instance($cm->id),
            'anonymous' => ($feedback->anonymous == FEEDBACK_ANONYMOUS_YES),
            'other' => array(
                'anonymous' => $feedback->anonymous             )
        ));
        $event->add_record_snapshot('course_modules', $cm);
        $event->add_record_snapshot('course', $course);
        $event->add_record_snapshot('feedback', $feedback);
        return $event;
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

    
    protected function get_legacy_logdata() {
        if ($this->anonymous) {
            return null;
        } else {
            return parent::get_legacy_logdata();
        }
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['anonymous'])) {
            throw new \coding_exception('The \'anonymous\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'feedback', 'restore' => 'feedback');
    }

    public static function get_other_mapping() {
                return false;
    }
}

