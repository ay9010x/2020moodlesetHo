<?php



defined('MOODLE_INTERNAL') || die();



class mod_lti_lib_testcase extends advanced_testcase {

    
    public static function setUpBeforeClass() {
        global $CFG;
        require_once($CFG->dirroot . '/mod/lti/lib.php');
    }

    
    public function test_lti_view() {
        global $CFG;

        $CFG->enablecompletion = 1;
        $this->resetAfterTest();

        $this->setAdminUser();
                $course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        $lti = $this->getDataGenerator()->create_module('lti', array('course' => $course->id),
                                                            array('completion' => 2, 'completionview' => 1));
        $context = context_module::instance($lti->cmid);
        $cm = get_coursemodule_from_instance('lti', $lti->id);

                $sink = $this->redirectEvents();

        lti_view($lti, $course, $cm, $context);

        $events = $sink->get_events();
                $this->assertCount(3, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_lti\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $moodleurl = new \moodle_url('/mod/lti/view.php', array('id' => $cm->id));
        $this->assertEquals($moodleurl, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

                $completion = new completion_info($course);
        $completiondata = $completion->get_data($cm);
        $this->assertEquals(1, $completiondata->completionstate);

    }

    
    public function test_lti_delete_instance() {
        $this->resetAfterTest();

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course(array());
        $lti = $this->getDataGenerator()->create_module('lti', array('course' => $course->id));
        $cm = get_coursemodule_from_instance('lti', $lti->id);

                course_delete_module($cm->id);
    }
}
