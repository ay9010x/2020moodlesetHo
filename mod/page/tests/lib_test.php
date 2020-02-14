<?php



defined('MOODLE_INTERNAL') || die();



class mod_page_lib_testcase extends advanced_testcase {

    
    public static function setUpBeforeClass() {
        global $CFG;
        require_once($CFG->dirroot . '/mod/page/lib.php');
    }

    
    public function test_page_view() {
        global $CFG;

        $CFG->enablecompletion = 1;
        $this->resetAfterTest();

                $course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        $page = $this->getDataGenerator()->create_module('page', array('course' => $course->id),
                                                            array('completion' => 2, 'completionview' => 1));
        $context = context_module::instance($page->cmid);
        $cm = get_coursemodule_from_instance('page', $page->id);

                $sink = $this->redirectEvents();

        $this->setAdminUser();
        page_view($page, $course, $cm, $context);

        $events = $sink->get_events();
                $this->assertCount(3, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_page\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $moodleurl = new \moodle_url('/mod/page/view.php', array('id' => $cm->id));
        $this->assertEquals($moodleurl, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

                $completion = new completion_info($course);
        $completiondata = $completion->get_data($cm);
        $this->assertEquals(1, $completiondata->completionstate);

    }
}
