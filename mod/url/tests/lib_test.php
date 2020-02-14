<?php



defined('MOODLE_INTERNAL') || die();



class mod_url_lib_testcase extends advanced_testcase {

    
    public static function setUpBeforeClass() {
        global $CFG;
        require_once($CFG->dirroot . '/mod/url/lib.php');
        require_once($CFG->dirroot . '/mod/url/locallib.php');
    }

    
    public function test_url_appears_valid_url() {
        $this->assertTrue(url_appears_valid_url('http://example'));
        $this->assertTrue(url_appears_valid_url('http://www.example.com'));
        $this->assertTrue(url_appears_valid_url('http://www.exa-mple2.com'));
        $this->assertTrue(url_appears_valid_url('http://www.example.com/~nobody/index.html'));
        $this->assertTrue(url_appears_valid_url('http://www.example.com#hmm'));
        $this->assertTrue(url_appears_valid_url('http://www.example.com/#hmm'));
        $this->assertTrue(url_appears_valid_url('http://www.example.com/žlutý koníček/lala.txt'));
        $this->assertTrue(url_appears_valid_url('http://www.example.com/žlutý koníček/lala.txt#hmmmm'));
        $this->assertTrue(url_appears_valid_url('http://www.example.com/index.php?xx=yy&zz=aa'));
        $this->assertTrue(url_appears_valid_url('https://user:password@www.example.com/žlutý koníček/lala.txt'));
        $this->assertTrue(url_appears_valid_url('ftp://user:password@www.example.com/žlutý koníček/lala.txt'));

        $this->assertFalse(url_appears_valid_url('http:example.com'));
        $this->assertFalse(url_appears_valid_url('http:/example.com'));
        $this->assertFalse(url_appears_valid_url('http://'));
        $this->assertFalse(url_appears_valid_url('http://www.exa mple.com'));
        $this->assertFalse(url_appears_valid_url('http://www.examplé.com'));
        $this->assertFalse(url_appears_valid_url('http://@www.example.com'));
        $this->assertFalse(url_appears_valid_url('http://user:@www.example.com'));

        $this->assertTrue(url_appears_valid_url('lalala://@:@/'));
    }

    
    public function test_url_view() {
        global $CFG;

        $CFG->enablecompletion = 1;
        $this->resetAfterTest();

                $course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        $url = $this->getDataGenerator()->create_module('url', array('course' => $course->id),
                                                            array('completion' => 2, 'completionview' => 1));
        $context = context_module::instance($url->cmid);
        $cm = get_coursemodule_from_instance('url', $url->id);

                $sink = $this->redirectEvents();

        $this->setAdminUser();
        url_view($url, $course, $cm, $context);

        $events = $sink->get_events();
                $this->assertCount(3, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_url\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $url = new \moodle_url('/mod/url/view.php', array('id' => $cm->id));
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

                $completion = new completion_info($course);
        $completiondata = $completion->get_data($cm);
        $this->assertEquals(1, $completiondata->completionstate);

    }
}