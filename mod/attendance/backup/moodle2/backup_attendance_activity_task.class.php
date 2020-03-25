<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/attendance/backup/moodle2/backup_attendance_stepslib.php');


class backup_attendance_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
        $this->add_step(new backup_attendance_activity_structure_step('attendance_structure', 'attendance.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

                $search = "/(" . $base . "\/mod\/attendance\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@ATTENDANCEVIEWBYID*$2@$', $content);

                $search = "/(" . $base . "\/mod\/attendance\/view.php\?id\=)([0-9]+)\&studentid\=([0-9]+)/";
        $content = preg_replace($search, '$@ATTENDANCEVIEWBYIDSTUD*$2*$3@$', $content);

        return $content;
    }
}
