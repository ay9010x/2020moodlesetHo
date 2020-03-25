<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/workshop/lib.php'); require_once($CFG->dirroot . '/mod/workshop/locallib.php'); require_once($CFG->dirroot . '/lib/cronlib.php'); require_once(__DIR__ . '/fixtures/testable.php');



class mod_workshop_events_testcase extends advanced_testcase {

    
    protected $workshop;
    
    protected $course;
    
    protected $cm;
    
    protected $context;

    
    protected function setUp() {
        parent::setUp();
        $this->setAdminUser();

                $this->course = $this->getDataGenerator()->create_course();
        $this->workshop = $this->getDataGenerator()->create_module('workshop', array('course' => $this->course));
        $this->cm = get_coursemodule_from_instance('workshop', $this->workshop->id);
        $this->context = context_module::instance($this->cm->id);
    }

    protected function tearDown() {
        $this->workshop = null;
        $this->course = null;
        $this->cm = null;
        $this->context = null;
        parent::tearDown();
    }

    
    public function test_phase_switched_event() {
        $this->resetAfterTest();
        $this->setAdminUser();

                $this->workshop->phase = 20;
        $this->workshop->phaseswitchassessment = 1;
        $this->workshop->submissionend = time() - 1;

        $cm = get_coursemodule_from_instance('workshop', $this->workshop->id, $this->course->id, false, MUST_EXIST);
        $workshop = new testable_workshop($this->workshop, $cm, $this->course);

                $newphase = 30;
                $sink = $this->redirectEvents();
        $workshop->switch_phase($newphase);
        $events = $sink->get_events();
        $event = reset($events);

                $expected = array($this->course->id, 'workshop', 'update switch phase', 'view.php?id=' . $this->cm->id,
            $newphase, $this->cm->id);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);

        $sink->close();
    }

    public function test_assessment_evaluated() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $cm = get_coursemodule_from_instance('workshop', $this->workshop->id, $this->course->id, false, MUST_EXIST);

        $workshop = new testable_workshop($this->workshop, $cm, $this->course);

        $assessments = array();
        $assessments[] = (object)array('reviewerid' => 2, 'gradinggrade' => null,
            'gradinggradeover' => null, 'aggregationid' => null, 'aggregatedgrade' => 12);

                $sink = $this->redirectEvents();
        $workshop->aggregate_grading_grades_process($assessments);
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\mod_workshop\event\assessment_evaluated', $event);
        $this->assertEquals('workshop_aggregations', $event->objecttable);
        $this->assertEquals(context_module::instance($cm->id), $event->get_context());
        $this->assertEventContextNotUsed($event);

        $sink->close();
    }

    public function test_assessment_reevaluated() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $cm = get_coursemodule_from_instance('workshop', $this->workshop->id, $this->course->id, false, MUST_EXIST);

        $workshop = new testable_workshop($this->workshop, $cm, $this->course);

        $assessments = array();
        $assessments[] = (object)array('reviewerid' => 2, 'gradinggrade' => null, 'gradinggradeover' => null,
            'aggregationid' => 2, 'aggregatedgrade' => 12);

                $sink = $this->redirectEvents();
        $workshop->aggregate_grading_grades_process($assessments);
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\mod_workshop\event\assessment_reevaluated', $event);
        $this->assertEquals('workshop_aggregations', $event->objecttable);
        $this->assertEquals(context_module::instance($cm->id), $event->get_context());
        $expected = array($this->course->id, 'workshop', 'update aggregate grade',
            'view.php?id=' . $event->get_context()->instanceid, $event->objectid, $event->get_context()->instanceid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);

        $sink->close();
    }

    
    public function test_aggregate_grades_reset_event() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $event = \mod_workshop\event\assessment_evaluations_reset::create(array(
            'context'  => $this->context,
            'courseid' => $this->course->id,
            'other' => array('workshopid' => $this->workshop->id)
        ));

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

                $expected = array($this->course->id, 'workshop', 'update clear aggregated grade', 'view.php?id=' . $this->cm->id,
            $this->workshop->id, $this->cm->id);
        $this->assertEventLegacyLogData($expected, $event);

        $sink->close();
    }

    
    public function test_instances_list_viewed_event() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $context = context_course::instance($this->course->id);

        $event = \mod_workshop\event\course_module_instance_list_viewed::create(array('context' => $context));

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

                $expected = array($this->course->id, 'workshop', 'view all', 'index.php?id=' . $this->course->id, '');
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);

        $sink->close();
    }

    
    public function test_submission_created_event() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $user = $this->getDataGenerator()->create_user();
        $submissionid = 48;

        $event = \mod_workshop\event\submission_created::create(array(
                'objectid'      => $submissionid,
                'context'       => $this->context,
                'courseid'      => $this->course->id,
                'relateduserid' => $user->id,
                'other'         => array(
                    'submissiontitle' => 'The submission title'
                )
            )
        );

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

                $expected = array($this->course->id, 'workshop', 'add submission',
            'submission.php?cmid=' . $this->cm->id . '&id=' . $submissionid, $submissionid, $this->cm->id);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);

        $sink->close();
    }

    
    public function test_submission_updated_event() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $user = $this->getDataGenerator()->create_user();
        $submissionid = 48;

        $event = \mod_workshop\event\submission_updated::create(array(
                'objectid'      => $submissionid,
                'context'       => $this->context,
                'courseid'      => $this->course->id,
                'relateduserid' => $user->id,
                'other'         => array(
                    'submissiontitle' => 'The submission title'
                )
            )
        );

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

                $expected = array($this->course->id, 'workshop', 'update submission',
            'submission.php?cmid=' . $this->cm->id . '&id=' . $submissionid, $submissionid, $this->cm->id);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);

        $sink->close();
    }

    
    public function test_submission_viewed_event() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $user = $this->getDataGenerator()->create_user();
        $submissionid = 48;

        $event = \mod_workshop\event\submission_viewed::create(array(
                'objectid'      => $submissionid,
                'context'       => $this->context,
                'courseid'      => $this->course->id,
                'relateduserid' => $user->id,
                'other'         => array(
                    'workshopid' => $this->workshop->id
                )
            )
        );

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

                $expected = array($this->course->id, 'workshop', 'view submission',
            'submission.php?cmid=' . $this->cm->id . '&id=' . $submissionid, $submissionid, $this->cm->id);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);

        $sink->close();
    }
}
