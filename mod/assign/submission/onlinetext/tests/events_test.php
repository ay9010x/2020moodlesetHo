<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/assign/tests/base_test.php');

class assignsubmission_onlinetext_events_testcase extends advanced_testcase {

    
    protected $user;

    
    protected $course;

    
    protected $cm;

    
    protected $context;

    
    protected $assign;

    
    protected $submission;

    
    protected $data;

    
    protected function setUp() {
        $this->user = $this->getDataGenerator()->create_user();
        $this->course = $this->getDataGenerator()->create_course();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_assign');
        $params['course'] = $this->course->id;
        $instance = $generator->create_instance($params);
        $this->cm = get_coursemodule_from_instance('assign', $instance->id);
        $this->context = context_module::instance($this->cm->id);
        $this->assign = new testable_assign($this->context, $this->cm, $this->course);

        $this->setUser($this->user->id);
        $this->submission = $this->assign->get_user_submission($this->user->id, true);
        $this->data = new stdClass();
        $this->data->onlinetext_editor = array(
            'itemid' => file_get_unused_draft_itemid(),
            'text' => 'Submission text',
            'format' => FORMAT_PLAIN
        );
    }

    
    public function test_assessable_uploaded() {
        $this->resetAfterTest();

        $plugin = $this->assign->get_submission_plugin_by_type('onlinetext');
        $sink = $this->redirectEvents();
        $plugin->save($this->submission, $this->data);
        $events = $sink->get_events();

        $this->assertCount(2, $events);
        $event = reset($events);
        $this->assertInstanceOf('\assignsubmission_onlinetext\event\assessable_uploaded', $event);
        $this->assertEquals($this->context->id, $event->contextid);
        $this->assertEquals($this->submission->id, $event->objectid);
        $this->assertEquals(array(), $event->other['pathnamehashes']);
        $this->assertEquals(FORMAT_PLAIN, $event->other['format']);
        $this->assertEquals('Submission text', $event->other['content']);
        $expected = new stdClass();
        $expected->modulename = 'assign';
        $expected->cmid = $this->cm->id;
        $expected->itemid = $this->submission->id;
        $expected->courseid = $this->course->id;
        $expected->userid = $this->user->id;
        $expected->content = 'Submission text';
        $this->assertEventLegacyData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_submission_created() {
        $this->resetAfterTest();

        $plugin = $this->assign->get_submission_plugin_by_type('onlinetext');
        $sink = $this->redirectEvents();
        $plugin->save($this->submission, $this->data);
        $events = $sink->get_events();

        $this->assertCount(2, $events);
        $event = $events[1];
        $this->assertInstanceOf('\assignsubmission_onlinetext\event\submission_created', $event);
        $this->assertEquals($this->context->id, $event->contextid);
        $this->assertEquals($this->course->id, $event->courseid);
        $this->assertEquals($this->submission->id, $event->other['submissionid']);
        $this->assertEquals($this->submission->attemptnumber, $event->other['submissionattempt']);
        $this->assertEquals($this->submission->status, $event->other['submissionstatus']);
        $this->assertEquals($this->submission->userid, $event->relateduserid);
    }

    
    public function test_submission_updated() {
        $this->resetAfterTest();

        $plugin = $this->assign->get_submission_plugin_by_type('onlinetext');
        $sink = $this->redirectEvents();
                $plugin->save($this->submission, $this->data);
                $plugin->save($this->submission, $this->data);
        $events = $sink->get_events();

        $this->assertCount(4, $events);
        $event = $events[3];
        $this->assertInstanceOf('\assignsubmission_onlinetext\event\submission_updated', $event);
        $this->assertEquals($this->context->id, $event->contextid);
        $this->assertEquals($this->course->id, $event->courseid);
        $this->assertEquals($this->submission->id, $event->other['submissionid']);
        $this->assertEquals($this->submission->attemptnumber, $event->other['submissionattempt']);
        $this->assertEquals($this->submission->status, $event->other['submissionstatus']);
        $this->assertEquals($this->submission->userid, $event->relateduserid);
    }
}
