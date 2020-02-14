<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/grade/report/user/externallib.php');


class gradereport_user_externallib_testcase extends externallib_advanced_testcase {

    
    private function load_data($s1grade, $s2grade) {
        global $DB;

        $course = $this->getDataGenerator()->create_course();

        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $student1 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student1->id, $course->id, $studentrole->id);

        $student2 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student2->id, $course->id, $studentrole->id);

        $teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $teacherrole->id);

        $assignment = $this->getDataGenerator()->create_module('assign', array('name' => "Test assign", 'course' => $course->id));
        $modcontext = get_coursemodule_from_instance('assign', $assignment->id, $course->id);
        $assignment->cmidnumber = $modcontext->id;

        $student1grade = array('userid' => $student1->id, 'rawgrade' => $s1grade);
        $student2grade = array('userid' => $student2->id, 'rawgrade' => $s2grade);
        $studentgrades = array($student1->id => $student1grade, $student2->id => $student2grade);
        assign_grade_item_update($assignment, $studentgrades);

        return array($course, $teacher, $student1, $student2);
    }

    
    public function test_get_grades_table_teacher() {

        $this->resetAfterTest(true);

        $s1grade = 80;
        $s2grade = 60;

        list($course, $teacher, $student1, $student2) = $this->load_data($s1grade, $s2grade);

                $this->setUser($teacher);

        $studentgrades = gradereport_user_external::get_grades_table($course->id);
        $studentgrades = external_api::clean_returnvalue(gradereport_user_external::get_grades_table_returns(), $studentgrades);

                $this->assertTrue(count($studentgrades['warnings']) == 0);

                $this->assertTrue(count($studentgrades['tables']) == 2);

                $studentreturnedgrades = array();
        $studentreturnedgrades[$studentgrades['tables'][0]['userid']] =
            (int) $studentgrades['tables'][0]['tabledata'][1]['grade']['content'];

        $studentreturnedgrades[$studentgrades['tables'][1]['userid']] =
            (int) $studentgrades['tables'][1]['tabledata'][1]['grade']['content'];

        $this->assertEquals($s1grade, $studentreturnedgrades[$student1->id]);
        $this->assertEquals($s2grade, $studentreturnedgrades[$student2->id]);
    }

    
    public function test_get_grades_table_student() {
        global $CFG, $DB;

        $this->resetAfterTest(true);

        $s1grade = 80;
        $s2grade = 60;

        list($course, $teacher, $student1, $student2) = $this->load_data($s1grade, $s2grade);

                $this->setUser($student1);
        $studentgrade = gradereport_user_external::get_grades_table($course->id, $student1->id);
        $studentgrade = external_api::clean_returnvalue(gradereport_user_external::get_grades_table_returns(), $studentgrade);

                $this->assertTrue(count($studentgrade['warnings']) == 0);

        $this->assertTrue(count($studentgrade['tables']) == 1);
        $student1returnedgrade = (int) $studentgrade['tables'][0]['tabledata'][1]['grade']['content'];
        $this->assertEquals($s1grade, $student1returnedgrade);

    }

    
    public function test_get_grades_table_permissions() {
        global $CFG, $DB;

        $this->resetAfterTest(true);

        $s1grade = 80;
        $s2grade = 60;

        list($course, $teacher, $student1, $student2) = $this->load_data($s1grade, $s2grade);

        $this->setUser($student2);

        try {
            $studentgrade = gradereport_user_external::get_grades_table($course->id, $student1->id);
            $this->fail('Exception expected due to not perissions to view other user grades.');
        } catch (moodle_exception $e) {
            $this->assertEquals('nopermissiontoviewgrades', $e->errorcode);
        }

    }

    
    public function test_view_grade_report() {
        global $USER;

        $this->resetAfterTest(true);

        $s1grade = 80;
        $s2grade = 60;
        list($course, $teacher, $student1, $student2) = $this->load_data($s1grade, $s2grade);

                $sink = $this->redirectEvents();

        $this->setUser($student1);
        $result = gradereport_user_external::view_grade_report($course->id);
        $result = external_api::clean_returnvalue(gradereport_user_external::view_grade_report_returns(), $result);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\gradereport_user\event\grade_report_viewed', $event);
        $this->assertEquals(context_course::instance($course->id), $event->get_context());
        $this->assertEquals($USER->id, $event->get_data()['relateduserid']);

        $this->setUser($teacher);
        $result = gradereport_user_external::view_grade_report($course->id, $student1->id);
        $result = external_api::clean_returnvalue(gradereport_user_external::view_grade_report_returns(), $result);
        $events = $sink->get_events();
        $event = reset($events);
        $sink->close();

                $this->assertInstanceOf('\gradereport_user\event\grade_report_viewed', $event);
        $this->assertEquals(context_course::instance($course->id), $event->get_context());
        $this->assertEquals($student1->id, $event->get_data()['relateduserid']);

        $this->setUser($student2);
        try {
            $studentgrade = gradereport_user_external::view_grade_report($course->id, $student1->id);
            $this->fail('Exception expected due to not permissions to view other user grades.');
        } catch (moodle_exception $e) {
            $this->assertEquals('nopermissiontoviewgrades', $e->errorcode);
        }

    }

}
