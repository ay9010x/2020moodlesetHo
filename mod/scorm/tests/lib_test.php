<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/scorm/lib.php');


class mod_scorm_lib_testcase extends externallib_advanced_testcase {

    
    public function setUp() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

                $this->course = $this->getDataGenerator()->create_course();
        $this->scorm = $this->getDataGenerator()->create_module('scorm', array('course' => $this->course->id));
        $this->context = context_module::instance($this->scorm->cmid);
        $this->cm = get_coursemodule_from_instance('scorm', $this->scorm->id);

                $this->student = self::getDataGenerator()->create_user();
        $this->teacher = self::getDataGenerator()->create_user();

                $this->studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->getDataGenerator()->enrol_user($this->student->id, $this->course->id, $this->studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($this->teacher->id, $this->course->id, $this->teacherrole->id, 'manual');
    }

    
    public function test_scorm_view() {
        global $CFG;

                $sink = $this->redirectEvents();

        scorm_view($this->scorm, $this->course, $this->cm, $this->context);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_scorm\event\course_module_viewed', $event);
        $this->assertEquals($this->context, $event->get_context());
        $url = new \moodle_url('/mod/scorm/view.php', array('id' => $this->cm->id));
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());
    }

    
    public function test_scorm_check_and_require_available() {
        global $DB;

                self::setUser($this->student);

                list($status, $warnings) = scorm_get_availability_status($this->scorm, false);
        $this->assertEquals(true, $status);
        $this->assertCount(0, $warnings);

                $this->scorm->timeopen = time() + DAYSECS;
        list($status, $warnings) = scorm_get_availability_status($this->scorm, false);
        $this->assertEquals(false, $status);
        $this->assertCount(1, $warnings);

                $this->scorm->timeopen = 0;
        $this->scorm->timeclose = time() - DAYSECS;
        list($status, $warnings) = scorm_get_availability_status($this->scorm, false);
        $this->assertEquals(false, $status);
        $this->assertCount(1, $warnings);

                $this->scorm->timeopen = time() + DAYSECS;
        list($status, $warnings) = scorm_get_availability_status($this->scorm, false);
        $this->assertEquals(false, $status);
        $this->assertCount(2, $warnings);

                list($status, $warnings) = scorm_get_availability_status($this->scorm, true, $this->context);
        $this->assertEquals(false, $status);
        $this->assertCount(2, $warnings);

                $this->scorm->timeopen = time() + DAYSECS;
        $this->scorm->timeclose = 0;
        list($status, $warnings) = scorm_get_availability_status($this->scorm, true, $this->context);
        $this->assertEquals(false, $status);
        $this->assertCount(1, $warnings);

                $this->scorm->timeopen = 0;
        $this->scorm->timeclose = time() - DAYSECS;
        list($status, $warnings) = scorm_get_availability_status($this->scorm, true, $this->context);
        $this->assertEquals(false, $status);
        $this->assertCount(1, $warnings);

                $this->scorm->timeopen = time() + DAYSECS;
        list($status, $warnings) = scorm_get_availability_status($this->scorm, true, $this->context);
        $this->assertEquals(false, $status);
        $this->assertCount(2, $warnings);

                self::setUser($this->teacher);

                $this->scorm->timeopen = time() + DAYSECS;
        list($status, $warnings) = scorm_get_availability_status($this->scorm, false);
        $this->assertEquals(false, $status);
        $this->assertCount(2, $warnings);

                        $this->scorm->timeopen = time() + DAYSECS;
        list($status, $warnings) = scorm_get_availability_status($this->scorm, true, $this->context);
        $this->assertEquals(true, $status);
        $this->assertCount(0, $warnings);

                scorm_require_available($this->scorm, true, $this->context);
                $this->setExpectedException('moodle_exception', get_string("notopenyet", "scorm", userdate($this->scorm->timeopen)));

                self::setUser($this->student);
        $this->scorm->timeopen = 0;
        $this->scorm->timeclose = time() - DAYSECS;

        $this->setExpectedException('moodle_exception', get_string("expired", "scorm", userdate($this->scorm->timeclose)));
        scorm_require_available($this->scorm, false);
    }

    
    public function test_scorm_get_last_completed_attempt() {
        $this->assertEquals(1, scorm_get_last_completed_attempt($this->scorm->id, $this->student->id));
    }
}
