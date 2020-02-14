<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/assign/tests/base_test.php');

class assignsubmission_file_events_testcase extends advanced_testcase {

    
    protected $user;

    
    protected $course;

    
    protected $cm;

    
    protected $context;

    
    protected $assign;

    
    protected $files;

    
    protected $submission;

    
    protected $fi;

    
    protected $fi2;

    
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

        $fs = get_file_storage();
        $dummy = (object) array(
            'contextid' => $this->context->id,
            'component' => 'assignsubmission_file',
            'filearea' => ASSIGNSUBMISSION_FILE_FILEAREA,
            'itemid' => $this->submission->id,
            'filepath' => '/',
            'filename' => 'myassignmnent.pdf'
        );
        $this->fi = $fs->create_file_from_string($dummy, 'Content of ' . $dummy->filename);
        $dummy = (object) array(
            'contextid' => $this->context->id,
            'component' => 'assignsubmission_file',
            'filearea' => ASSIGNSUBMISSION_FILE_FILEAREA,
            'itemid' => $this->submission->id,
            'filepath' => '/',
            'filename' => 'myassignmnent.png'
        );
        $this->fi2 = $fs->create_file_from_string($dummy, 'Content of ' . $dummy->filename);
        $this->files = $fs->get_area_files($this->context->id, 'assignsubmission_file', ASSIGNSUBMISSION_FILE_FILEAREA,
            $this->submission->id, 'id', false);

    }

    
    public function test_assessable_uploaded() {
        $this->resetAfterTest();

        $data = new stdClass();
        $plugin = $this->assign->get_submission_plugin_by_type('file');
        $sink = $this->redirectEvents();
        $plugin->save($this->submission, $data);
        $events = $sink->get_events();

        $this->assertCount(2, $events);
        $event = reset($events);
        $this->assertInstanceOf('\assignsubmission_file\event\assessable_uploaded', $event);
        $this->assertEquals($this->context->id, $event->contextid);
        $this->assertEquals($this->submission->id, $event->objectid);
        $this->assertCount(2, $event->other['pathnamehashes']);
        $this->assertEquals($this->fi->get_pathnamehash(), $event->other['pathnamehashes'][0]);
        $this->assertEquals($this->fi2->get_pathnamehash(), $event->other['pathnamehashes'][1]);
        $expected = new stdClass();
        $expected->modulename = 'assign';
        $expected->cmid = $this->cm->id;
        $expected->itemid = $this->submission->id;
        $expected->courseid = $this->course->id;
        $expected->userid = $this->user->id;
        $expected->file = $this->files;
        $expected->files = $this->files;
        $expected->pathnamehashes = array($this->fi->get_pathnamehash(), $this->fi2->get_pathnamehash());
        $this->assertEventLegacyData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_submission_created() {
        $this->resetAfterTest();

        $data = new stdClass();
        $plugin = $this->assign->get_submission_plugin_by_type('file');
        $sink = $this->redirectEvents();
        $plugin->save($this->submission, $data);
        $events = $sink->get_events();

        $this->assertCount(2, $events);
                $event = $events[1];
        $this->assertInstanceOf('\assignsubmission_file\event\submission_created', $event);
        $this->assertEquals($this->context->id, $event->contextid);
        $this->assertEquals($this->course->id, $event->courseid);
        $this->assertEquals($this->submission->id, $event->other['submissionid']);
        $this->assertEquals($this->submission->attemptnumber, $event->other['submissionattempt']);
        $this->assertEquals($this->submission->status, $event->other['submissionstatus']);
        $this->assertEquals($this->submission->userid, $event->relateduserid);
    }

    
    public function test_submission_updated() {
        $this->resetAfterTest();

        $data = new stdClass();
        $plugin = $this->assign->get_submission_plugin_by_type('file');
        $sink = $this->redirectEvents();
                $plugin->save($this->submission, $data);
                $plugin->save($this->submission, $data);
        $events = $sink->get_events();

        $this->assertCount(4, $events);
                $event = $events[3];
        $this->assertInstanceOf('\assignsubmission_file\event\submission_updated', $event);
        $this->assertEquals($this->context->id, $event->contextid);
        $this->assertEquals($this->course->id, $event->courseid);
        $this->assertEquals($this->submission->id, $event->other['submissionid']);
        $this->assertEquals($this->submission->attemptnumber, $event->other['submissionattempt']);
        $this->assertEquals($this->submission->status, $event->other['submissionstatus']);
        $this->assertEquals($this->submission->userid, $event->relateduserid);
    }

}
