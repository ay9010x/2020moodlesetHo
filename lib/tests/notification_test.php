<?php



defined('MOODLE_INTERNAL') || die();


class core_notification_testcase extends advanced_testcase {

    
    public function setUp() {
        global $PAGE, $SESSION;

        parent::setUp();
        $PAGE = new moodle_page();
        \core\session\manager::init_empty_session();
        $SESSION->notifications = [];
    }

    
    public function tearDown() {
        global $PAGE, $SESSION;

        $PAGE = null;
        \core\session\manager::init_empty_session();
        $SESSION->notifications = [];
        parent::tearDown();
    }

    
    public function test_add_during_output_stages() {
        global $PAGE, $SESSION;

        \core\notification::add('Example before header', \core\notification::INFO);
        $this->assertCount(1, $SESSION->notifications);

        $PAGE->set_state(\moodle_page::STATE_PRINTING_HEADER);
        \core\notification::add('Example during header', \core\notification::INFO);
        $this->assertCount(2, $SESSION->notifications);

        $PAGE->set_state(\moodle_page::STATE_IN_BODY);
        \core\notification::add('Example in body', \core\notification::INFO);
        $this->expectOutputRegex('/Example in body/');
        $this->assertCount(2, $SESSION->notifications);

        $PAGE->set_state(\moodle_page::STATE_DONE);
        \core\notification::add('Example after page', \core\notification::INFO);
        $this->assertCount(3, $SESSION->notifications);
    }

    
    public function test_fetch() {
                $this->assertCount(0, \core\notification::fetch());

                \core\notification::success('Notification created');
        $this->assertCount(1, \core\notification::fetch());
        $this->assertCount(0, \core\notification::fetch());
    }

    
    public function test_session_persistance() {
        global $PAGE, $SESSION;

                $this->assertCount(0, $SESSION->notifications);

                \core\notification::success('Notification created');
        $this->assertCount(1, $SESSION->notifications);

                \core\session\manager::init_empty_session();
        $this->assertCount(1, $SESSION->notifications);
    }
}
