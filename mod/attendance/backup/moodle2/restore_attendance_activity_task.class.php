<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/attendance/backup/moodle2/restore_attendance_stepslib.php');


class restore_attendance_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
        $this->add_step(new restore_attendance_activity_structure_step('attendance_structure', 'attendance.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('attendance_sessions',
                          array('description'), 'attendance_session');

        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('ATTENDANCEVIEWBYID',
                    '/mod/attendance/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('ATTENDANCEVIEWBYIDSTUD',
                    '/mod/attendance/view.php?id=$1&studentid=$2', array('course_module', 'user'));

                $rules[] = new restore_decode_rule('ATTFORBLOCKVIEWBYID',
            '/mod/attendance/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('ATTFORBLOCKVIEWBYIDSTUD',
            '/mod/attendance/view.php?id=$1&studentid=$2', array('course_module', 'user'));

        return $rules;

    }

    
    static public function define_restore_log_rules() {
        $rules = array();

                return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        return $rules;
    }
}
