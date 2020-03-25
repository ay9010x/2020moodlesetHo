<?php



namespace mod_lesson;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/lesson/locallib.php');


class group_observers {

    
    protected static $resetinprogress = false;

    
    public static function course_reset_started($event) {
        self::$resetinprogress = $event->courseid;
    }

    
    public static function course_reset_ended($event) {
        if (!empty(self::$resetinprogress)) {
            if (!empty($event->other['reset_options']['reset_groups_remove'])) {
                lesson_process_group_deleted_in_course($event->courseid);
            }
        }

        self::$resetinprogress = null;
    }

    
    public static function group_deleted($event) {
        if (!empty(self::$resetinprogress)) {
                        return;
        }
        lesson_process_group_deleted_in_course($event->courseid, $event->objectid);
    }
}
