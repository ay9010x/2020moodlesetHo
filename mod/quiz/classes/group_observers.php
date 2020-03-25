<?php



namespace mod_quiz;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/locallib.php');


class group_observers {

    
    protected static $resetinprogress = false;

    
    public static function course_reset_started($event) {
        self::$resetinprogress = $event->courseid;
    }

    
    public static function course_reset_ended($event) {
        if (!empty(self::$resetinprogress)) {
            if (!empty($event->other['reset_options']['reset_groups_remove'])) {
                quiz_process_group_deleted_in_course($event->courseid);
            }
            if (!empty($event->other['reset_options']['reset_groups_members'])) {
                quiz_update_open_attempts(array('courseid' => $event->courseid));
            }
        }

        self::$resetinprogress = null;
    }

    
    public static function group_deleted($event) {
        if (!empty(self::$resetinprogress)) {
                        return;
        }
        quiz_process_group_deleted_in_course($event->courseid);
    }

    
    public static function group_member_added($event) {
        quiz_update_open_attempts(array('userid' => $event->relateduserid, 'groupid' => $event->objectid));
    }

    
    public static function group_member_removed($event) {
        if (!empty(self::$resetinprogress)) {
                        return;
        }
        quiz_update_open_attempts(array('userid' => $event->relateduserid, 'groupid' => $event->objectid));
    }

}
