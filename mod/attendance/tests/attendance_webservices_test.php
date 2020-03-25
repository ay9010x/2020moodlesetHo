<?php


if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

global $CFG;

require_once($CFG->dirroot . '/mod/attendance/classes/attendance_webservices_handler.php');
require_once($CFG->dirroot . '/mod/attendance/classes/structure.php');


class attendance_webservices_tests extends advanced_testcase {
    protected $category;
    protected $course;
    protected $attendance;
    protected $teacher;
    protected $sessions;

    public function setUp() {
        global $DB;

        $this->category = $this->getDataGenerator()->create_category();
        $this->course = $this->getDataGenerator()->create_course(array('category' => $this->category->id));

        $record = new stdClass();
        $record->course = $this->course->id;
        $record->name = "Attendance";
        $record->grade = 100;

        $DB->insert_record('attendance', $record);

        $this->getDataGenerator()->create_module('attendance', array('course' => $this->course->id));

        $moduleid = $DB->get_field('modules', 'id', array('name' => 'attendance'));
        $cm = $DB->get_record('course_modules', array('course' => $this->course->id, 'module' => $moduleid));
        $context = context_course::instance($this->course->id);
        $att = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);
        $this->attendance = new mod_attendance_structure($att, $cm, $this->course, $context);

        $this->create_and_enrol_users();

        $this->setUser($this->teacher);

        $sessions = array();
        $session = new stdClass();
        $session->sessdate = time();
        $session->duration = 6000;
        $session->description = "";
        $session->descriptionformat = 1;
        $session->descriptionitemid = 0;
        $session->timemodified = time();
        $session->statusset = 0;
        $session->groupid = 0;

                $this->sessions[] = $session;

        $this->attendance->add_sessions($this->sessions);
    }

    
    protected function create_and_enrol_users() {
        for ($i = 0; $i < 10; $i++) {
            $student = $this->getDataGenerator()->create_user();
            $this->getDataGenerator()->enrol_user($student->id, $this->course->id, 5);         }

        $this->teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->teacher->id, $this->course->id, 3);     }

    public function test_get_courses_with_today_sessions() {
        $this->resetAfterTest(true);

                $this->attendance->add_sessions($this->sessions);

        $courseswithsessions = attendance_handler::get_courses_with_today_sessions($this->teacher->id);

        $this->assertTrue(is_array($courseswithsessions));
        $this->assertEquals(count($courseswithsessions), 1);
        $course = array_pop($courseswithsessions);
        $this->assertEquals($course->fullname, $this->course->fullname);
        $attendanceinstance = array_pop($course->attendance_instances);
        $this->assertEquals(count($attendanceinstance['today_sessions']), 2);
    }

    public function test_get_session() {
        $this->resetAfterTest(true);

        $courseswithsessions = attendance_handler::get_courses_with_today_sessions($this->teacher->id);

        $course = array_pop($courseswithsessions);
        $attendanceinstance = array_pop($course->attendance_instances);
        $session = array_pop($attendanceinstance['today_sessions']);

        $sessioninfo = attendance_handler::get_session($session->id);

        $this->assertEquals($this->attendance->id, $sessioninfo->attendanceid);
        $this->assertEquals($session->id, $sessioninfo->id);
        $this->assertEquals(count($sessioninfo->users), 10);
    }

    public function test_update_user_status() {
        $this->resetAfterTest(true);

        $courseswithsessions = attendance_handler::get_courses_with_today_sessions($this->teacher->id);

        $course = array_pop($courseswithsessions);
        $attendanceinstance = array_pop($course->attendance_instances);
        $session = array_pop($attendanceinstance['today_sessions']);

        $sessioninfo = attendance_handler::get_session($session->id);

        $student = array_pop($sessioninfo->users);
        $status = array_pop($sessioninfo->statuses);
        $statusset = $sessioninfo->statusset;
        attendance_handler::update_user_status($session->id, $student->id, $this->teacher->id, $status->id, $statusset);

        $sessioninfo = attendance_handler::get_session($session->id);
        $log = $sessioninfo->attendance_log;
        $studentlog = $log[$student->id];

        $this->assertEquals($status->id, $studentlog->statusid);
    }
}
