<?php




defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/imscp/lib.php');


class mod_imscp_lib_testcase extends advanced_testcase {

    public function test_export_contents() {
        global $DB, $USER;

        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $studentrole->id);

        $this->setAdminUser();
        $imscp = $this->getDataGenerator()->create_module('imscp', array('course' => $course->id));
        $cm = get_coursemodule_from_id('imscp', $imscp->cmid);

        $this->setUser($user);
        $contents = imscp_export_contents($cm, '');

                $this->assertCount(47, $contents);
                $this->assertEquals('structure', $contents[0]['filename']);
                $this->assertEquals(json_encode(unserialize($imscp->structure)), $contents[0]['content']);

    }

    
    public function test_imscp_view() {
        global $CFG;

        $CFG->enablecompletion = 1;
        $this->resetAfterTest();

        $this->setAdminUser();
                $course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        $imscp = $this->getDataGenerator()->create_module('imscp', array('course' => $course->id),
                                                            array('completion' => 2, 'completionview' => 1));
        $context = context_module::instance($imscp->cmid);
        $cm = get_coursemodule_from_instance('imscp', $imscp->id);

                $sink = $this->redirectEvents();

        imscp_view($imscp, $course, $cm, $context);

        $events = $sink->get_events();
                $this->assertCount(3, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_imscp\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $moodleurl = new \moodle_url('/mod/imscp/view.php', array('id' => $cm->id));
        $this->assertEquals($moodleurl, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

                $completion = new completion_info($course);
        $completiondata = $completion->get_data($cm);
        $this->assertEquals(1, $completiondata->completionstate);

    }
}
