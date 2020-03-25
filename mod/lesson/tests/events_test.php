<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/mod/lesson/locallib.php');

class mod_lesson_events_testcase extends advanced_testcase {

    
    private $course;

    
    private $lesson;

    
    public function setUp() {
        $this->resetAfterTest();

        $this->setAdminUser();
        $this->course = $this->getDataGenerator()->create_course();
        $lesson = $this->getDataGenerator()->create_module('lesson', array('course' => $this->course->id));

                $this->lesson = new lesson($lesson);
    }

    
    public function test_page_created() {

                $generator = $this->getDataGenerator()->get_plugin_generator('mod_lesson');
                $sink = $this->redirectEvents();
        $pagerecord = $generator->create_content($this->lesson);
        $page = $this->lesson->load_page($pagerecord->id);

                $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_lesson\event\page_created', $event);
        $this->assertEquals($page->id, $event->objectid);
        $this->assertEventContextNotUsed($event);
        $this->assertDebuggingNotCalled();
    }

    
    public function test_page_moved() {

                        $generator = $this->getDataGenerator()->get_plugin_generator('mod_lesson');
        $pagerecord1 = $generator->create_content($this->lesson);
        $page1 = $this->lesson->load_page($pagerecord1->id);
        $pagerecord2 = $generator->create_content($this->lesson);
        $page2 = $this->lesson->load_page($pagerecord2->id);
        $pagerecord3 = $generator->create_content($this->lesson);
        $page3 = $this->lesson->load_page($pagerecord3->id);
                $sink = $this->redirectEvents();
        $this->lesson->resort_pages($page3->id, $pagerecord2->id);
                $events = $sink->get_events();
        $event = reset($events);

        $this->assertCount(1, $events);
                $this->assertInstanceOf('\mod_lesson\event\page_moved', $event);
        $this->assertEquals($page3->id, $event->objectid);
        $this->assertEquals($pagerecord1->id, $event->other['nextpageid']);
        $this->assertEquals($pagerecord2->id, $event->other['prevpageid']);
        $this->assertEventContextNotUsed($event);
        $this->assertDebuggingNotCalled();
    }

    
    public function test_page_deleted() {

                $generator = $this->getDataGenerator()->get_plugin_generator('mod_lesson');
                $pagerecord = $generator->create_content($this->lesson);
                $page = $this->lesson->load_page($pagerecord->id);
                $sink = $this->redirectEvents();
        $page->delete();

                $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_lesson\event\page_deleted', $event);
        $this->assertEquals($page->id, $event->objectid);
        $this->assertEventContextNotUsed($event);
        $this->assertDebuggingNotCalled();
    }

    
    public function test_page_updated() {

                $eventparams = array(
            'context' => context_module::instance($this->lesson->properties()->cmid),
            'objectid' => 25,
            'other' => array(
                'pagetype' => 'True/false'
                )
        );

        $event = \mod_lesson\event\page_updated::create($eventparams);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_lesson\event\page_updated', $event);
        $this->assertEquals(25, $event->objectid);
        $this->assertEquals('True/false', $event->other['pagetype']);
        $this->assertEventContextNotUsed($event);
        $this->assertDebuggingNotCalled();
    }

    
    public function test_essay_attempt_viewed() {
                $event = \mod_lesson\event\essay_attempt_viewed::create(array(
            'objectid' => $this->lesson->id,
            'relateduserid' => 3,
            'context' => context_module::instance($this->lesson->properties()->cmid),
            'courseid' => $this->course->id
        ));

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_lesson\event\essay_attempt_viewed', $event);
        $this->assertEquals(context_module::instance($this->lesson->properties()->cmid), $event->get_context());
        $expected = array($this->course->id, 'lesson', 'view grade', 'essay.php?id=' . $this->lesson->properties()->cmid .
            '&mode=grade&attemptid='.$this->lesson->id, get_string('manualgrading', 'lesson'), $this->lesson->properties()->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_lesson_started() {
                $sink = $this->redirectEvents();
        $this->lesson->start_timer();
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_lesson\event\lesson_started', $event);
        $this->assertEquals(context_module::instance($this->lesson->properties()->cmid), $event->get_context());
        $expected = array($this->course->id, 'lesson', 'start', 'view.php?id=' . $this->lesson->properties()->cmid,
            $this->lesson->properties()->id, $this->lesson->properties()->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_lesson_restarted() {

                $this->lesson->start_timer();
                $sink = $this->redirectEvents();
        $this->lesson->update_timer(true);
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_lesson\event\lesson_restarted', $event);
        $this->assertEquals(context_module::instance($this->lesson->properties()->cmid), $event->get_context());
        $expected = array($this->course->id, 'lesson', 'start', 'view.php?id=' . $this->lesson->properties()->cmid,
            $this->lesson->properties()->id, $this->lesson->properties()->cmid);
        $this->assertEventContextNotUsed($event);
        $this->assertDebuggingNotCalled();

    }

    
    public function test_lesson_resumed() {

                $this->lesson->start_timer();
                $sink = $this->redirectEvents();
        $this->lesson->update_timer(true, true);
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_lesson\event\lesson_resumed', $event);
        $this->assertEquals(context_module::instance($this->lesson->properties()->cmid), $event->get_context());
        $expected = array($this->course->id, 'lesson', 'start', 'view.php?id=' . $this->lesson->properties()->cmid,
            $this->lesson->properties()->id, $this->lesson->properties()->cmid);
        $this->assertEventContextNotUsed($event);
        $this->assertDebuggingNotCalled();

    }
    
    public function test_lesson_ended() {
        global $DB, $USER;

                $lessontimer = new stdClass();
        $lessontimer->lessonid = $this->lesson->properties()->id;
        $lessontimer->userid = $USER->id;
        $lessontimer->startime = time();
        $lessontimer->lessontime = time();
        $DB->insert_record('lesson_timer', $lessontimer);

                $sink = $this->redirectEvents();
        $this->lesson->stop_timer();
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_lesson\event\lesson_ended', $event);
        $this->assertEquals(context_module::instance($this->lesson->properties()->cmid), $event->get_context());
        $expected = array($this->course->id, 'lesson', 'end', 'view.php?id=' . $this->lesson->properties()->cmid,
            $this->lesson->properties()->id, $this->lesson->properties()->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_essay_assessed() {
                $gradeid = 5;
        $attemptid = 7;
        $event = \mod_lesson\event\essay_assessed::create(array(
            'objectid' => $gradeid,
            'relateduserid' => 3,
            'context' => context_module::instance($this->lesson->properties()->cmid),
            'courseid' => $this->course->id,
            'other' => array(
                'lessonid' => $this->lesson->id,
                'attemptid' => $attemptid
            )
        ));

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_lesson\event\essay_assessed', $event);
        $this->assertEquals(context_module::instance($this->lesson->properties()->cmid), $event->get_context());
        $expected = array($this->course->id, 'lesson', 'update grade', 'essay.php?id=' . $this->lesson->properties()->cmid,
                $this->lesson->name, $this->lesson->properties()->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_content_page_viewed() {
        global $DB, $PAGE;

                $generator = $this->getDataGenerator()->get_plugin_generator('mod_lesson');
                $pagerecord = $generator->create_content($this->lesson);
                $page = $this->lesson->load_page($pagerecord->id);
                $coursemodule = $DB->get_record('course_modules', array('id' => $this->lesson->properties()->cmid));
                $PAGE->set_cm($coursemodule);
                $lessonoutput = $PAGE->get_renderer('mod_lesson');

                $sink = $this->redirectEvents();
                $lessonoutput->display_page($this->lesson, $page, false);
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_lesson\event\content_page_viewed', $event);
        $this->assertEquals($page->id, $event->objectid);
        $this->assertEventContextNotUsed($event);
        $this->assertDebuggingNotCalled();
    }

    
    public function test_question_viewed() {
        global $DB, $PAGE;

                $generator = $this->getDataGenerator()->get_plugin_generator('mod_lesson');
                $pagerecord = $generator->create_question_truefalse($this->lesson);
                $page = $this->lesson->load_page($pagerecord->id);
                $coursemodule = $DB->get_record('course_modules', array('id' => $this->lesson->properties()->cmid));
                $PAGE->set_cm($coursemodule);
                $lessonoutput = $PAGE->get_renderer('mod_lesson');

                $sink = $this->redirectEvents();
                $lessonoutput->display_page($this->lesson, $page, false);
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_lesson\event\question_viewed', $event);
        $this->assertEquals($page->id, $event->objectid);
        $this->assertEquals('True/false', $event->other['pagetype']);
        $this->assertEventContextNotUsed($event);
        $this->assertDebuggingNotCalled();
    }

    
    public function test_question_answered() {

                $eventparams = array(
            'context' => context_module::instance($this->lesson->properties()->cmid),
            'objectid' => 25,
            'other' => array(
                'pagetype' => 'True/false'
                )
        );

        $event = \mod_lesson\event\question_answered::create($eventparams);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_lesson\event\question_answered', $event);
        $this->assertEquals(25, $event->objectid);
        $this->assertEquals('True/false', $event->other['pagetype']);
        $this->assertEventContextNotUsed($event);
        $this->assertDebuggingNotCalled();
    }

    
    public function test_user_override_created() {

        $params = array(
            'objectid' => 1,
            'relateduserid' => 2,
            'context' => context_module::instance($this->lesson->properties()->cmid),
            'other' => array(
                'lessonid' => $this->lesson->id
            )
        );
        $event = \mod_lesson\event\user_override_created::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_lesson\event\user_override_created', $event);
        $this->assertEquals(context_module::instance($this->lesson->properties()->cmid), $event->get_context());
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_group_override_created() {

        $params = array(
            'objectid' => 1,
            'context' => context_module::instance($this->lesson->properties()->cmid),
            'other' => array(
                'lessonid' => $this->lesson->id,
                'groupid' => 2
            )
        );
        $event = \mod_lesson\event\group_override_created::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_lesson\event\group_override_created', $event);
        $this->assertEquals(context_module::instance($this->lesson->properties()->cmid), $event->get_context());
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_user_override_updated() {

        $params = array(
            'objectid' => 1,
            'relateduserid' => 2,
            'context' => context_module::instance($this->lesson->properties()->cmid),
            'other' => array(
                'lessonid' => $this->lesson->id
            )
        );
        $event = \mod_lesson\event\user_override_updated::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_lesson\event\user_override_updated', $event);
        $this->assertEquals(context_module::instance($this->lesson->properties()->cmid), $event->get_context());
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_group_override_updated() {

        $params = array(
            'objectid' => 1,
            'context' => context_module::instance($this->lesson->properties()->cmid),
            'other' => array(
                'lessonid' => $this->lesson->id,
                'groupid' => 2
            )
        );
        $event = \mod_lesson\event\group_override_updated::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_lesson\event\group_override_updated', $event);
        $this->assertEquals(context_module::instance($this->lesson->properties()->cmid), $event->get_context());
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_user_override_deleted() {
        global $DB;

                $override = new stdClass();
        $override->lesson = $this->lesson->id;
        $override->userid = 2;
        $override->id = $DB->insert_record('lesson_overrides', $override);

                $sink = $this->redirectEvents();
        $this->lesson->delete_override($override->id);
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_lesson\event\user_override_deleted', $event);
        $this->assertEquals(context_module::instance($this->lesson->properties()->cmid), $event->get_context());
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_group_override_deleted() {
        global $DB;

                $override = new stdClass();
        $override->lesson = $this->lesson->id;
        $override->groupid = 2;
        $override->id = $DB->insert_record('lesson_overrides', $override);

                $sink = $this->redirectEvents();
        $this->lesson->delete_override($override->id);
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_lesson\event\group_override_deleted', $event);
        $this->assertEquals(context_module::instance($this->lesson->properties()->cmid), $event->get_context());
        $this->assertEventContextNotUsed($event);
    }
}
