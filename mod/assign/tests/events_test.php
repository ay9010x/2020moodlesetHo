<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/assign/tests/base_test.php');
require_once($CFG->dirroot . '/mod/assign/tests/fixtures/event_mod_assign_fixtures.php');


class assign_events_testcase extends mod_assign_base_testcase {

    
    public function test_base_event() {
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_assign');
        $instance = $generator->create_instance(array('course' => $this->course->id));
        $modcontext = context_module::instance($instance->cmid);

        $data = array(
            'context' => $modcontext,
        );
        
        $event = \mod_assign_unittests\event\nothing_happened::create($data);
        $assign = $event->get_assign();
        $this->assertDebuggingCalled();
        $this->assertInstanceOf('assign', $assign);

        $event = \mod_assign_unittests\event\nothing_happened::create($data);
        $event->set_assign($assign);
        $assign2 = $event->get_assign();
        $this->assertDebuggingNotCalled();
        $this->assertSame($assign, $assign2);
    }

    
    public function test_submission_created() {
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_assign');
        $instance = $generator->create_instance(array('course' => $this->course->id));
        $modcontext = context_module::instance($instance->cmid);

                $params = array(
            'context' => $modcontext,
            'courseid' => $this->course->id
        );

        $eventinfo = $params;
        $eventinfo['other'] = array(
            'submissionid' => '17',
            'submissionattempt' => 0,
            'submissionstatus' => 'submitted'
        );

        $sink = $this->redirectEvents();
        $event = \mod_assign_unittests\event\submission_created::create($eventinfo);
        $event->trigger();
        $result = $sink->get_events();
        $event = reset($result);
        $sink->close();

        $this->assertEquals($modcontext->id, $event->contextid);
        $this->assertEquals($this->course->id, $event->courseid);

                try {
            \mod_assign_unittests\event\submission_created::create($params);
            $this->fail('Other must contain the key submissionid.');
        } catch (Exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }
                $subinfo = $params;
        $subinfo['other'] = array('submissionid' => '23');
        try {
            \mod_assign_unittests\event\submission_created::create($subinfo);
            $this->fail('Other must contain the key submissionattempt.');
        } catch (Exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }

        $subinfo['other'] = array('submissionattempt' => '0');
        try {
            \mod_assign_unittests\event\submission_created::create($subinfo);
            $this->fail('Other must contain the key submissionstatus.');
        } catch (Exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }
    }

    
    public function test_submission_updated() {
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_assign');
        $instance = $generator->create_instance(array('course' => $this->course->id));
        $modcontext = context_module::instance($instance->cmid);

                $params = array(
            'context' => $modcontext,
            'courseid' => $this->course->id
        );

        $eventinfo = $params;
        $eventinfo['other'] = array(
            'submissionid' => '17',
            'submissionattempt' => 0,
            'submissionstatus' => 'submitted'
        );

        $sink = $this->redirectEvents();
        $event = \mod_assign_unittests\event\submission_updated::create($eventinfo);
        $event->trigger();
        $result = $sink->get_events();
        $event = reset($result);
        $sink->close();

        $this->assertEquals($modcontext->id, $event->contextid);
        $this->assertEquals($this->course->id, $event->courseid);

                try {
            \mod_assign_unittests\event\submission_created::create($params);
            $this->fail('Other must contain the key submissionid.');
        } catch (Exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }
                $subinfo = $params;
        $subinfo['other'] = array('submissionid' => '23');
        try {
            \mod_assign_unittests\event\submission_created::create($subinfo);
            $this->fail('Other must contain the key submissionattempt.');
        } catch (Exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }

        $subinfo['other'] = array('submissionattempt' => '0');
        try {
            \mod_assign_unittests\event\submission_created::create($subinfo);
            $this->fail('Other must contain the key submissionstatus.');
        } catch (Exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }
    }

    public function test_extension_granted() {
        $this->setUser($this->editingteachers[0]);

        $tomorrow = time() + 24*60*60;
        $yesterday = time() - 24*60*60;

        $assign = $this->create_instance(array('duedate' => $yesterday, 'cutoffdate' => $yesterday));
        $sink = $this->redirectEvents();

        $assign->testable_save_user_extension($this->students[0]->id, $tomorrow);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);
        $this->assertInstanceOf('\mod_assign\event\extension_granted', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $this->assertEquals($assign->get_instance()->id, $event->objectid);
        $this->assertEquals($this->students[0]->id, $event->relateduserid);
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'grant extension',
            'view.php?id=' . $assign->get_course_module()->id,
            $this->students[0]->id,
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $sink->close();
    }

    public function test_submission_locked() {
        $this->editingteachers[0]->ignoresesskey = true;
        $this->setUser($this->editingteachers[0]);

        $assign = $this->create_instance();
        $sink = $this->redirectEvents();

        $assign->lock_submission($this->students[0]->id);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);
        $this->assertInstanceOf('\mod_assign\event\submission_locked', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $this->assertEquals($assign->get_instance()->id, $event->objectid);
        $this->assertEquals($this->students[0]->id, $event->relateduserid);
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'lock submission',
            'view.php?id=' . $assign->get_course_module()->id,
            get_string('locksubmissionforstudent', 'assign', array('id' => $this->students[0]->id,
                'fullname' => fullname($this->students[0]))),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $sink->close();

                $this->editingteachers[0]->ignoresesskey = false;
    }

    public function test_identities_revealed() {
        $this->editingteachers[0]->ignoresesskey = true;
        $this->setUser($this->editingteachers[0]);

        $assign = $this->create_instance(array('blindmarking'=>1));
        $sink = $this->redirectEvents();

        $assign->reveal_identities();

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);
        $this->assertInstanceOf('\mod_assign\event\identities_revealed', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $this->assertEquals($assign->get_instance()->id, $event->objectid);
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'reveal identities',
            'view.php?id=' . $assign->get_course_module()->id,
            get_string('revealidentities', 'assign'),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $sink->close();

                $this->editingteachers[0]->ignoresesskey = false;
    }

    
    public function test_submission_status_viewed() {
        global $PAGE;

        $this->setUser($this->editingteachers[0]);

        $assign = $this->create_instance();

                $PAGE->set_url('/a_url');

                $sink = $this->redirectEvents();
        $assign->view();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_assign\event\submission_status_viewed', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'view',
            'view.php?id=' . $assign->get_course_module()->id,
            get_string('viewownsubmissionstatus', 'assign'),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    public function test_submission_status_updated() {
        $this->editingteachers[0]->ignoresesskey = true;
        $this->setUser($this->editingteachers[0]);

        $assign = $this->create_instance();
        $submission = $assign->get_user_submission($this->students[0]->id, true);
        $submission->status = ASSIGN_SUBMISSION_STATUS_SUBMITTED;
        $assign->testable_update_submission($submission, $this->students[0]->id, true, false);

        $sink = $this->redirectEvents();
        $assign->revert_to_draft($this->students[0]->id);

        $events = $sink->get_events();
        $this->assertCount(2, $events);
        $event = $events[1];
        $this->assertInstanceOf('\mod_assign\event\submission_status_updated', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $this->assertEquals($submission->id, $event->objectid);
        $this->assertEquals($this->students[0]->id, $event->relateduserid);
        $this->assertEquals(ASSIGN_SUBMISSION_STATUS_DRAFT, $event->other['newstatus']);
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'revert submission to draft',
            'view.php?id=' . $assign->get_course_module()->id,
            get_string('reverttodraftforstudent', 'assign', array('id' => $this->students[0]->id,
                'fullname' => fullname($this->students[0]))),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $sink->close();

                $this->editingteachers[0]->ignoresesskey = false;
    }

    public function test_marker_updated() {
        $this->editingteachers[0]->ignoresesskey = true;
        $this->setUser($this->editingteachers[0]);

        $assign = $this->create_instance();

        $sink = $this->redirectEvents();
        $assign->testable_process_set_batch_marking_allocation($this->students[0]->id, $this->teachers[0]->id);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);
        $this->assertInstanceOf('\mod_assign\event\marker_updated', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $this->assertEquals($assign->get_instance()->id, $event->objectid);
        $this->assertEquals($this->students[0]->id, $event->relateduserid);
        $this->assertEquals($this->editingteachers[0]->id, $event->userid);
        $this->assertEquals($this->teachers[0]->id, $event->other['markerid']);
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'set marking allocation',
            'view.php?id=' . $assign->get_course_module()->id,
            get_string('setmarkerallocationforlog', 'assign', array('id' => $this->students[0]->id,
                'fullname' => fullname($this->students[0]), 'marker' => fullname($this->teachers[0]))),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $sink->close();

                $this->editingteachers[0]->ignoresesskey = false;
    }

    public function test_workflow_state_updated() {
        $this->editingteachers[0]->ignoresesskey = true;
        $this->setUser($this->editingteachers[0]);

        $assign = $this->create_instance();

                $sink = $this->redirectEvents();
        $assign->testable_process_set_batch_marking_workflow_state($this->students[0]->id, ASSIGN_MARKING_WORKFLOW_STATE_INREVIEW);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);
        $this->assertInstanceOf('\mod_assign\event\workflow_state_updated', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $this->assertEquals($assign->get_instance()->id, $event->objectid);
        $this->assertEquals($this->students[0]->id, $event->relateduserid);
        $this->assertEquals($this->editingteachers[0]->id, $event->userid);
        $this->assertEquals(ASSIGN_MARKING_WORKFLOW_STATE_INREVIEW, $event->other['newstate']);
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'set marking workflow state',
            'view.php?id=' . $assign->get_course_module()->id,
            get_string('setmarkingworkflowstateforlog', 'assign', array('id' => $this->students[0]->id,
                'fullname' => fullname($this->students[0]), 'state' => ASSIGN_MARKING_WORKFLOW_STATE_INREVIEW)),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $sink->close();

                $sink = $this->redirectEvents();
        $data = new stdClass();
        $data->grade = '50.0';
        $data->workflowstate = 'readyforrelease';
        $assign->testable_apply_grade_to_user($data, $this->students[0]->id, 0);

        $events = $sink->get_events();
        $this->assertCount(4, $events);
        $event = reset($events);
        $this->assertInstanceOf('\mod_assign\event\workflow_state_updated', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $this->assertEquals($assign->get_instance()->id, $event->objectid);
        $this->assertEquals($this->students[0]->id, $event->relateduserid);
        $this->assertEquals($this->editingteachers[0]->id, $event->userid);
        $this->assertEquals(ASSIGN_MARKING_WORKFLOW_STATE_READYFORRELEASE, $event->other['newstate']);
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'set marking workflow state',
            'view.php?id=' . $assign->get_course_module()->id,
            get_string('setmarkingworkflowstateforlog', 'assign', array('id' => $this->students[0]->id,
                'fullname' => fullname($this->students[0]), 'state' => ASSIGN_MARKING_WORKFLOW_STATE_READYFORRELEASE)),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $sink->close();

                $sink = $this->redirectEvents();

        $data = array(
            'grademodified_' . $this->students[0]->id => time(),
            'gradeattempt_' . $this->students[0]->id => '',
            'quickgrade_' . $this->students[0]->id => '60.0',
            'quickgrade_' . $this->students[0]->id . '_workflowstate' => 'inmarking'
        );
        $assign->testable_process_save_quick_grades($data);

        $events = $sink->get_events();
        $this->assertCount(4, $events);
        $event = reset($events);
        $this->assertInstanceOf('\mod_assign\event\workflow_state_updated', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $this->assertEquals($assign->get_instance()->id, $event->objectid);
        $this->assertEquals($this->students[0]->id, $event->relateduserid);
        $this->assertEquals($this->editingteachers[0]->id, $event->userid);
        $this->assertEquals(ASSIGN_MARKING_WORKFLOW_STATE_INMARKING, $event->other['newstate']);
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'set marking workflow state',
            'view.php?id=' . $assign->get_course_module()->id,
            get_string('setmarkingworkflowstateforlog', 'assign', array('id' => $this->students[0]->id,
                'fullname' => fullname($this->students[0]), 'state' => ASSIGN_MARKING_WORKFLOW_STATE_INMARKING)),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $sink->close();

                $this->editingteachers[0]->ignoresesskey = false;
    }

    public function test_submission_duplicated() {
        $this->setUser($this->students[0]);

        $assign = $this->create_instance();
        $submission1 = $assign->get_user_submission($this->students[0]->id, true, 0);
        $submission2 = $assign->get_user_submission($this->students[0]->id, true, 1);
        $submission2->status = ASSIGN_SUBMISSION_STATUS_REOPENED;
        $assign->testable_update_submission($submission2, $this->students[0]->id, time(), $assign->get_instance()->teamsubmission);

        $sink = $this->redirectEvents();
        $notices = null;
        $assign->copy_previous_attempt($notices);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);
        $this->assertInstanceOf('\mod_assign\event\submission_duplicated', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $this->assertEquals($submission2->id, $event->objectid);
        $this->assertEquals($this->students[0]->id, $event->userid);
        $submission2->status = ASSIGN_SUBMISSION_STATUS_DRAFT;
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'submissioncopied',
            'view.php?id=' . $assign->get_course_module()->id,
            $assign->testable_format_submission_for_log($submission2),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $sink->close();
    }

    public function test_submission_unlocked() {
        $this->editingteachers[0]->ignoresesskey = true;
        $this->setUser($this->editingteachers[0]);

        $assign = $this->create_instance();
        $sink = $this->redirectEvents();

        $assign->unlock_submission($this->students[0]->id);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);
        $this->assertInstanceOf('\mod_assign\event\submission_unlocked', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $this->assertEquals($assign->get_instance()->id, $event->objectid);
        $this->assertEquals($this->students[0]->id, $event->relateduserid);
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'unlock submission',
            'view.php?id=' . $assign->get_course_module()->id,
            get_string('unlocksubmissionforstudent', 'assign', array('id' => $this->students[0]->id,
                'fullname' => fullname($this->students[0]))),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $sink->close();

                $this->editingteachers[0]->ignoresesskey = false;
    }

    public function test_submission_graded() {
        $this->editingteachers[0]->ignoresesskey = true;
        $this->setUser($this->editingteachers[0]);
        $assign = $this->create_instance();

                $sink = $this->redirectEvents();

        $data = new stdClass();
        $data->grade = '50.0';
        $assign->testable_apply_grade_to_user($data, $this->students[0]->id, 0);
        $grade = $assign->get_user_grade($this->students[0]->id, false, 0);

        $events = $sink->get_events();
        $this->assertCount(3, $events);
        $event = $events[2];
        $this->assertInstanceOf('\mod_assign\event\submission_graded', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $this->assertEquals($grade->id, $event->objectid);
        $this->assertEquals($this->students[0]->id, $event->relateduserid);
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'grade submission',
            'view.php?id=' . $assign->get_course_module()->id,
            $assign->format_grade_for_log($grade),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $sink->close();

                $sink = $this->redirectEvents();

        $grade = $assign->get_user_grade($this->students[0]->id, false);
        $data = array(
            'grademodified_' . $this->students[0]->id => time(),
            'gradeattempt_' . $this->students[0]->id => $grade->attemptnumber,
            'quickgrade_' . $this->students[0]->id => '60.0'
        );
        $assign->testable_process_save_quick_grades($data);
        $grade = $assign->get_user_grade($this->students[0]->id, false);
        $this->assertEquals('60.0', $grade->grade);

        $events = $sink->get_events();
        $this->assertCount(3, $events);
        $event = $events[2];
        $this->assertInstanceOf('\mod_assign\event\submission_graded', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $this->assertEquals($grade->id, $event->objectid);
        $this->assertEquals($this->students[0]->id, $event->relateduserid);
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'grade submission',
            'view.php?id=' . $assign->get_course_module()->id,
            $assign->format_grade_for_log($grade),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $sink->close();

                $sink = $this->redirectEvents();
        $data = clone($grade);
        $data->grade = '50.0';
        $assign->update_grade($data);
        $grade = $assign->get_user_grade($this->students[0]->id, false, 0);
        $this->assertEquals('50.0', $grade->grade);
        $events = $sink->get_events();

        $this->assertCount(3, $events);
        $event = $events[2];
        $this->assertInstanceOf('\mod_assign\event\submission_graded', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $this->assertEquals($grade->id, $event->objectid);
        $this->assertEquals($this->students[0]->id, $event->relateduserid);
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'grade submission',
            'view.php?id=' . $assign->get_course_module()->id,
            $assign->format_grade_for_log($grade),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $sink->close();
                $this->editingteachers[0]->ignoresesskey = false;
    }

    
    public function test_submission_viewed() {
        global $PAGE;

        $this->setUser($this->editingteachers[0]);

        $assign = $this->create_instance();
        $submission = $assign->get_user_submission($this->students[0]->id, true);

                $PAGE->set_url('/a_url');
                        global $_POST;
        $_POST['plugin'] = 'comments';
        $_POST['sid'] = $submission->id;

                $sink = $this->redirectEvents();
        $assign->view('viewpluginassignsubmission');
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_assign\event\submission_viewed', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $this->assertEquals($submission->id, $event->objectid);
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'view submission',
            'view.php?id=' . $assign->get_course_module()->id,
            get_string('viewsubmissionforuser', 'assign', $this->students[0]->id),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_feedback_viewed() {
        global $DB, $PAGE;

        $this->setUser($this->editingteachers[0]);

        $assign = $this->create_instance();
        $submission = $assign->get_user_submission($this->students[0]->id, true);

                $grade = new stdClass();
        $grade->assignment = $assign->get_instance()->id;
        $grade->userid = $this->students[0]->id;
        $gradeid = $DB->insert_record('assign_grades', $grade);

                $PAGE->set_url('/a_url');
                        global $_POST;
        $_POST['plugin'] = 'comments';
        $_POST['gid'] = $gradeid;
        $_POST['sid'] = $submission->id;

                $sink = $this->redirectEvents();
        $assign->view('viewpluginassignfeedback');
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_assign\event\feedback_viewed', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $this->assertEquals($gradeid, $event->objectid);
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'view feedback',
            'view.php?id=' . $assign->get_course_module()->id,
            get_string('viewfeedbackforuser', 'assign', $this->students[0]->id),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_grading_form_viewed() {
        global $PAGE;

        $this->setUser($this->editingteachers[0]);

        $assign = $this->create_instance();

                $PAGE->set_url('/a_url');
                global $_POST;
        $_POST['rownum'] = 1;
        $_POST['userid'] = $this->students[0]->id;

                $sink = $this->redirectEvents();
        $assign->view('grade');
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_assign\event\grading_form_viewed', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'view grading form',
            'view.php?id=' . $assign->get_course_module()->id,
            get_string('viewgradingformforstudent', 'assign', array('id' => $this->students[0]->id,
                'fullname' => fullname($this->students[0]))),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_grading_table_viewed() {
        global $PAGE;

        $this->setUser($this->editingteachers[0]);

        $assign = $this->create_instance();

                $PAGE->set_url('/a_url');
                global $_POST;
        $_POST['rownum'] = 1;
        $_POST['userid'] = $this->students[0]->id;

                $sink = $this->redirectEvents();
        $assign->view('grading');
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_assign\event\grading_table_viewed', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'view submission grading table',
            'view.php?id=' . $assign->get_course_module()->id,
            get_string('viewsubmissiongradingtable', 'assign'),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_submission_form_viewed() {
        global $PAGE;

        $this->setUser($this->students[0]);

        $assign = $this->create_instance();

                $PAGE->set_url('/a_url');

                $sink = $this->redirectEvents();
        $assign->view('editsubmission');
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_assign\event\submission_form_viewed', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'view submit assignment form',
            'view.php?id=' . $assign->get_course_module()->id,
            get_string('editsubmission', 'assign'),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_submission_confirmation_form_viewed() {
        global $PAGE;

        $this->setUser($this->students[0]);

        $assign = $this->create_instance();

                $PAGE->set_url('/a_url');

                $sink = $this->redirectEvents();
        $assign->view('submit');
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_assign\event\submission_confirmation_form_viewed', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'view confirm submit assignment form',
            'view.php?id=' . $assign->get_course_module()->id,
            get_string('viewownsubmissionform', 'assign'),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_reveal_identities_confirmation_page_viewed() {
        global $PAGE;

                $this->setAdminUser();

        $assign = $this->create_instance();

                $PAGE->set_url('/a_url');

                $sink = $this->redirectEvents();
        $assign->view('revealidentities');
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_assign\event\reveal_identities_confirmation_page_viewed', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'view',
            'view.php?id=' . $assign->get_course_module()->id,
            get_string('viewrevealidentitiesconfirm', 'assign'),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_statement_accepted() {
                $this->setUser($this->students[0]);

                set_config('submissionreceipts', false, 'assign');

        $assign = $this->create_instance();

                $data = new stdClass();
        $data->submissionstatement = 'We are the Borg. You will be assimilated. Resistance is futile. - do you agree
            to these terms?';

                $sink = $this->redirectEvents();
        $assign->submit_for_grading($data, array());
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_assign\event\statement_accepted', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'submission statement accepted',
            'view.php?id=' . $assign->get_course_module()->id,
            get_string('submissionstatementacceptedlog',
                'mod_assign',
                fullname($this->students[0])),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);

                $submissionplugins = $assign->get_submission_plugins();
        foreach ($submissionplugins as $plugin) {
            if ($plugin->get_type() === 'onlinetext') {
                $plugin->enable();
                break;
            }
        }

                $data = new stdClass();
        $data->onlinetext_editor = array(
            'text' => 'Online text',
            'format' => FORMAT_HTML,
            'itemid' => file_get_unused_draft_itemid()
        );
        $data->submissionstatement = 'We are the Borg. You will be assimilated. Resistance is futile. - do you agree
            to these terms?';

                $sink = $this->redirectEvents();
        $assign->save_submission($data, $notices);
        $events = $sink->get_events();
        $event = $events[2];

                $this->assertInstanceOf('\mod_assign\event\statement_accepted', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_batch_set_workflow_state_viewed() {
        $assign = $this->create_instance();

                $sink = $this->redirectEvents();
        $assign->testable_view_batch_set_workflow_state($this->students[0]->id);
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_assign\event\batch_set_workflow_state_viewed', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'view batch set marking workflow state',
            'view.php?id=' . $assign->get_course_module()->id,
            get_string('viewbatchsetmarkingworkflowstate', 'assign'),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_batch_set_marker_allocation_viewed() {
        $assign = $this->create_instance();

                $sink = $this->redirectEvents();
        $assign->testable_view_batch_markingallocation($this->students[0]->id);
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\mod_assign\event\batch_set_marker_allocation_viewed', $event);
        $this->assertEquals($assign->get_context(), $event->get_context());
        $expected = array(
            $assign->get_course()->id,
            'assign',
            'view batch set marker allocation',
            'view.php?id=' . $assign->get_course_module()->id,
            get_string('viewbatchmarkingallocation', 'assign'),
            $assign->get_course_module()->id
        );
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }
}
