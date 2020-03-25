<?php


require_once(dirname(__FILE__).'/../../../config.php');
require_once(dirname(__FILE__).'/../locallib.php');
require_once(dirname(__FILE__).'/structure.php');
require_once(dirname(__FILE__).'/../../../lib/sessionlib.php');
require_once(dirname(__FILE__).'/../../../lib/datalib.php');

class attendance_handler {
    
    public static function get_courses_with_today_sessions($userid) {
        $usercourses = enrol_get_users_courses($userid);
        $attendanceinstance = get_all_instances_in_courses('attendance', $usercourses);

        $coursessessions = array();

        foreach ($attendanceinstance as $attendance) {
            $context = context_course::instance($attendance->course);
            if (has_capability('mod/attendance:takeattendances', $context, $userid)) {
                $course = $usercourses[$attendance->course];
                $course->attendance_instance = array();

                $att = new stdClass();
                $att->id = $attendance->id;
                $att->course = $attendance->course;
                $att->name = $attendance->name;
                $att->grade = $attendance->grade;

                $cm = new stdClass();
                $cm->id = $attendance->coursemodule;

                $att = new mod_attendance_structure($att, $cm, $course, $context);
                $course->attendance_instance[$att->id] = array();
                $course->attendance_instance[$att->id]['name'] = $att->name;
                $todaysessions = $att->get_today_sessions();

                if (!empty($todaysessions)) {
                    $course->attendance_instance[$att->id]['today_sessions'] = $todaysessions;
                    $coursessessions[$course->id] = $course;
                }
            }
        }

        return self::prepare_data($coursessessions);
    }

    private static function prepare_data($coursessessions) {
        $courses = array();

        foreach ($coursessessions as $c) {
            $courses[$c->id] = new stdClass();
            $courses[$c->id]->shortname = $c->shortname;
            $courses[$c->id]->fullname = $c->fullname;
            $courses[$c->id]->attendance_instances = $c->attendance_instance;
        }

        return $courses;
    }

    
    public static function get_session($sessionid) {
        global $DB;

        $session = $DB->get_record('attendance_sessions', array('id' => $sessionid));
        $session->courseid = $DB->get_field('attendance', 'course', array('id' => $session->attendanceid));
        $session->statuses = attendance_get_statuses($session->attendanceid, true, $session->statusset);
        $coursecontext = context_course::instance($session->courseid);
        $session->users = get_enrolled_users($coursecontext, 'mod/attendance:canbelisted', 0, 'u.id, u.firstname, u.lastname');
        $session->attendance_log = array();

        if ($attendancelog = $DB->get_records('attendance_log', array('sessionid' => $sessionid),
                                              '', 'studentid, statusid, remarks, id')) {
            $session->attendance_log = $attendancelog;
        }

        return $session;
    }

    public static function update_user_status($sessionid, $studentid, $takenbyid, $statusid, $statusset) {
        global $DB;

        $record = new stdClass();
        $record->statusset = $statusset;
        $record->sessionid = $sessionid;
        $record->timetaken = time();
        $record->takenby = $takenbyid;
        $record->statusid = $statusid;
        $record->studentid = $studentid;

        if ($attendancelog = $DB->get_record('attendance_log', array('sessionid' => $sessionid, 'studentid' => $studentid))) {
            $record->id = $attendancelog->id;
            $DB->update_record('attendance_log', $record);
        } else {
            $DB->insert_record('attendance_log', $record);
        }

        if ($attendancesession = $DB->get_record('attendance_sessions', array('id' => $sessionid))) {
            $attendancesession->lasttaken = time();
            $attendancesession->lasttakenby = $takenbyid;
            $attendancesession->timemodified = time();

            $DB->update_record('attendance_sessions', $attendancesession);
        }
    }
}
