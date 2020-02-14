<?php



defined('MOODLE_INTERNAL') || die();


class block_comments_events_testcase extends advanced_testcase {
    
    private $course;

    
    private $wiki;

    
    public function setUp() {
        $this->resetAfterTest();
        $this->setAdminUser();

                $this->course = $this->getDataGenerator()->create_course();
        $this->wiki = $this->getDataGenerator()->create_module('wiki', array('course' => $this->course->id));
    }

    
    public function test_comment_created() {
        global $CFG;

        require_once($CFG->dirroot . '/comment/lib.php');

                $context = context_course::instance($this->course->id);
        $args = new stdClass;
        $args->context = $context;
        $args->course = $this->course;
        $args->area = 'page_comments';
        $args->itemid = 0;
        $args->component = 'block_comments';
        $args->linktext = get_string('showcomments');
        $args->notoggle = true;
        $args->autostart = true;
        $args->displaycancel = false;
        $comment = new comment($args);

                $sink = $this->redirectEvents();
        $comment->add('New comment');
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\block_comments\event\comment_created', $event);
        $this->assertEquals($context, $event->get_context());
        $url = new moodle_url('/course/view.php', array('id' => $this->course->id));
        $this->assertEquals($url, $event->get_url());

                $context = context_module::instance($this->wiki->cmid);
        $args = new stdClass;
        $args->context   = $context;
        $args->course    = $this->course;
        $args->area      = 'page_comments';
        $args->itemid    = 0;
        $args->component = 'block_comments';
        $args->linktext  = get_string('showcomments');
        $args->notoggle  = true;
        $args->autostart = true;
        $args->displaycancel = false;
        $comment = new comment($args);

                $sink = $this->redirectEvents();
        $comment->add('New comment 1');
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\block_comments\event\comment_created', $event);
        $this->assertEquals($context, $event->get_context());
        $url = new moodle_url('/mod/wiki/view.php', array('id' => $this->wiki->cmid));
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_comment_deleted() {
        global $CFG;

        require_once($CFG->dirroot . '/comment/lib.php');

                $context = context_course::instance($this->course->id);
        $args = new stdClass;
        $args->context   = $context;
        $args->course    = $this->course;
        $args->area      = 'page_comments';
        $args->itemid    = 0;
        $args->component = 'block_comments';
        $args->linktext  = get_string('showcomments');
        $args->notoggle  = true;
        $args->autostart = true;
        $args->displaycancel = false;
        $comment = new comment($args);
        $newcomment = $comment->add('New comment');

                $sink = $this->redirectEvents();
        $comment->delete($newcomment->id);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\block_comments\event\comment_deleted', $event);
        $this->assertEquals($context, $event->get_context());
        $url = new moodle_url('/course/view.php', array('id' => $this->course->id));
        $this->assertEquals($url, $event->get_url());

                $context = context_module::instance($this->wiki->cmid);
        $args = new stdClass;
        $args->context   = $context;
        $args->course    = $this->course;
        $args->area      = 'page_comments';
        $args->itemid    = 0;
        $args->component = 'block_comments';
        $args->linktext  = get_string('showcomments');
        $args->notoggle  = true;
        $args->autostart = true;
        $args->displaycancel = false;
        $comment = new comment($args);
        $newcomment = $comment->add('New comment 1');

                $sink = $this->redirectEvents();
        $comment->delete($newcomment->id);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\block_comments\event\comment_deleted', $event);
        $this->assertEquals($context, $event->get_context());
        $url = new moodle_url('/mod/wiki/view.php', array('id' => $this->wiki->cmid));
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);
    }
}
