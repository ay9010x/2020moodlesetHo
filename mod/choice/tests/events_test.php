<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/choice/lib.php');


class mod_choice_events_testcase extends advanced_testcase {
    
    protected $choice;

    
    protected $course;

    
    protected $cm;

    
    protected $context;

    
    protected function setup() {
        global $DB;

        $this->resetAfterTest();

        $this->course = $this->getDataGenerator()->create_course();
        $this->choice = $this->getDataGenerator()->create_module('choice', array('course' => $this->course->id));
        $this->cm = $DB->get_record('course_modules', array('id' => $this->choice->cmid));
        $this->context = context_module::instance($this->choice->cmid);
    }

    
    public function test_answer_submitted() {
        global $DB;
                $user = $this->getDataGenerator()->create_user();

        $optionids = array_keys($DB->get_records('choice_options', array('choiceid' => $this->choice->id)));
                $sink = $this->redirectEvents();
        choice_user_submit_response($optionids[3], $this->choice, $user->id, $this->course, $this->cm);
        $events = $sink->get_events();

                $this->assertCount(1, $events);
        $this->assertInstanceOf('\mod_choice\event\answer_submitted', $events[0]);
        $this->assertEquals($user->id, $events[0]->userid);
        $this->assertEquals(context_module::instance($this->choice->cmid), $events[0]->get_context());
        $this->assertEquals($this->choice->id, $events[0]->other['choiceid']);
        $this->assertEquals(array($optionids[3]), $events[0]->other['optionid']);
        $expected = array($this->course->id, "choice", "choose", 'view.php?id=' . $this->cm->id, $this->choice->id, $this->cm->id);
        $this->assertEventLegacyLogData($expected, $events[0]);
        $this->assertEventContextNotUsed($events[0]);
        $sink->close();
    }

    
    public function test_answer_submitted_multiple() {
        global $DB;

                $user = $this->getDataGenerator()->create_user();

                $choice = $this->getDataGenerator()->create_module('choice', array('course' => $this->course->id,
            'allowmultiple' => 1));
        $cm = $DB->get_record('course_modules', array('id' => $choice->cmid));
        $context = context_module::instance($choice->cmid);

        $optionids = array_keys($DB->get_records('choice_options', array('choiceid' => $choice->id)));
        $submittedoptionids = array($optionids[1], $optionids[3]);

                $sink = $this->redirectEvents();
        choice_user_submit_response($submittedoptionids, $choice, $user->id, $this->course, $cm);
        $events = $sink->get_events();

                $this->assertCount(1, $events);
        $this->assertInstanceOf('\mod_choice\event\answer_submitted', $events[0]);
        $this->assertEquals($user->id, $events[0]->userid);
        $this->assertEquals(context_module::instance($choice->cmid), $events[0]->get_context());
        $this->assertEquals($choice->id, $events[0]->other['choiceid']);
        $this->assertEquals($submittedoptionids, $events[0]->other['optionid']);
        $expected = array($this->course->id, "choice", "choose", 'view.php?id=' . $cm->id, $choice->id, $cm->id);
        $this->assertEventLegacyLogData($expected, $events[0]);
        $this->assertEventContextNotUsed($events[0]);
        $sink->close();
    }

    
    public function test_answer_submitted_other_exception() {
                $user = $this->getDataGenerator()->create_user();

        $eventdata = array();
        $eventdata['context'] = $this->context;
        $eventdata['objectid'] = 2;
        $eventdata['userid'] = $user->id;
        $eventdata['courseid'] = $this->course->id;
        $eventdata['other'] = array();

                $this->setExpectedException('coding_exception');
        $event = \mod_choice\event\answer_submitted::create($eventdata);
        $event->trigger();
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_answer_updated() {
        global $DB;
                $user = $this->getDataGenerator()->create_user();

        $optionids = array_keys($DB->get_records('choice_options', array('choiceid' => $this->choice->id)));

                choice_user_submit_response($optionids[2], $this->choice, $user->id, $this->course, $this->cm);

                $sink = $this->redirectEvents();
                choice_user_submit_response($optionids[3], $this->choice, $user->id, $this->course, $this->cm);

        $events = $sink->get_events();

                $this->assertCount(1, $events);
        $this->assertInstanceOf('\mod_choice\event\answer_updated', $events[0]);
        $this->assertEquals($user->id, $events[0]->userid);
        $this->assertEquals(context_module::instance($this->choice->cmid), $events[0]->get_context());
        $this->assertEquals($this->choice->id, $events[0]->other['choiceid']);
        $this->assertEquals($optionids[3], $events[0]->other['optionid']);
        $expected = array($this->course->id, "choice", "choose again", 'view.php?id=' . $this->cm->id,
                $this->choice->id, $this->cm->id);
        $this->assertEventLegacyLogData($expected, $events[0]);
        $this->assertEventContextNotUsed($events[0]);
        $sink->close();
    }

    
    public function test_answer_updated_other_exception() {
                $user = $this->getDataGenerator()->create_user();

        $eventdata = array();
        $eventdata['context'] = $this->context;
        $eventdata['objectid'] = 2;
        $eventdata['userid'] = $user->id;
        $eventdata['courseid'] = $this->course->id;
        $eventdata['other'] = array();

                $this->setExpectedException('coding_exception');
        $event = \mod_choice\event\answer_updated::create($eventdata);
        $event->trigger();
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_answer_deleted() {
        global $DB, $USER;
                $user = $this->getDataGenerator()->create_user();

        $optionids = array_keys($DB->get_records('choice_options', array('choiceid' => $this->choice->id)));

                choice_user_submit_response($optionids[2], $this->choice, $user->id, $this->course, $this->cm);
                $answer = $DB->get_record('choice_answers', array('userid' => $user->id, 'choiceid' => $this->choice->id),
                '*', $strictness = IGNORE_MULTIPLE);

                $sink = $this->redirectEvents();
                choice_delete_responses(array($answer->id), $this->choice, $this->cm, $this->course);

                $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_choice\event\answer_deleted', $event);
        $this->assertEquals($USER->id, $event->userid);
        $this->assertEquals($user->id, $event->relateduserid);
        $this->assertEquals(context_module::instance($this->choice->cmid), $event->get_context());
        $this->assertEquals($this->choice->id, $event->other['choiceid']);
        $this->assertEquals($answer->optionid, $event->other['optionid']);
        $this->assertEventContextNotUsed($event);
        $sink->close();
    }

    
    public function test_report_viewed() {
        global $USER;

        $this->resetAfterTest();

                $this->setAdminUser();

        $eventdata = array();
        $eventdata['objectid'] = $this->choice->id;
        $eventdata['context'] = $this->context;
        $eventdata['courseid'] = $this->course->id;
        $eventdata['other']['content'] = 'choicereportcontentviewed';

                $event = \mod_choice\event\report_viewed::create($eventdata);

                $sink = $this->redirectEvents();
        $event->trigger();
        $event = $sink->get_events();

                $this->assertCount(1, $event);
        $this->assertInstanceOf('\mod_choice\event\report_viewed', $event[0]);
        $this->assertEquals($USER->id, $event[0]->userid);
        $this->assertEquals(context_module::instance($this->choice->cmid), $event[0]->get_context());
        $expected = array($this->course->id, "choice", "report", 'report.php?id=' . $this->context->instanceid,
                $this->choice->id, $this->context->instanceid);
        $this->assertEventLegacyLogData($expected, $event[0]);
        $this->assertEventContextNotUsed($event[0]);
        $sink->close();
    }

    
    public function test_report_downloaded() {
        global $USER;

        $this->resetAfterTest();

                $this->setAdminUser();

        $eventdata = array();
        $eventdata['context'] = $this->context;
        $eventdata['courseid'] = $this->course->id;
        $eventdata['other']['content'] = 'choicereportcontentviewed';
        $eventdata['other']['format'] = 'csv';
        $eventdata['other']['choiceid'] = $this->choice->id;

                $event = \mod_choice\event\report_downloaded::create($eventdata);

                $sink = $this->redirectEvents();
        $event->trigger();
        $event = $sink->get_events();

                $this->assertCount(1, $event);
        $this->assertInstanceOf('\mod_choice\event\report_downloaded', $event[0]);
        $this->assertEquals($USER->id, $event[0]->userid);
        $this->assertEquals(context_module::instance($this->choice->cmid), $event[0]->get_context());
        $this->assertEquals('csv', $event[0]->other['format']);
        $this->assertEquals($this->choice->id, $event[0]->other['choiceid']);
        $this->assertEventContextNotUsed($event[0]);
        $sink->close();
    }

    
    public function test_course_module_viewed() {
        global $USER;

                $this->setAdminUser();

        $eventdata = array();
        $eventdata['objectid'] = $this->choice->id;
        $eventdata['context'] = $this->context;
        $eventdata['courseid'] = $this->course->id;
        $eventdata['other']['content'] = 'pageresourceview';

                $event = \mod_choice\event\course_module_viewed::create($eventdata);

                $sink = $this->redirectEvents();
        $event->trigger();
        $event = $sink->get_events();

                $this->assertCount(1, $event);
        $this->assertInstanceOf('\mod_choice\event\course_module_viewed', $event[0]);
        $this->assertEquals($USER->id, $event[0]->userid);
        $this->assertEquals(context_module::instance($this->choice->cmid), $event[0]->get_context());
        $expected = array($this->course->id, "choice", "view", 'view.php?id=' . $this->context->instanceid,
                $this->choice->id, $this->context->instanceid);
        $this->assertEventLegacyLogData($expected, $event[0]);
        $this->assertEventContextNotUsed($event[0]);
        $sink->close();
    }

    
    public function test_course_module_instance_list_viewed_viewed() {
        global $USER;

                        $this->setAdminUser();

        $params = array('context' => context_course::instance($this->course->id));
        $event = \mod_choice\event\course_module_instance_list_viewed::create($params);
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);
        $this->assertInstanceOf('\mod_choice\event\course_module_instance_list_viewed', $event);
        $this->assertEquals($USER->id, $event->userid);
        $this->assertEquals(context_course::instance($this->course->id), $event->get_context());
        $expected = array($this->course->id, 'choice', 'view all', 'index.php?id=' . $this->course->id, '');
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }
}
