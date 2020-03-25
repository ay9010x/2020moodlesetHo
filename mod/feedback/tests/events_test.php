<?php



global $CFG;


class mod_feedback_events_testcase extends advanced_testcase {

    
    private $eventuser;

    
    private $eventcourse;

    
    private $eventfeedback;

    
    private $eventcm;

    
    private $eventfeedbackitem;

    
    private $eventfeedbackcompleted;

    
    private $eventfeedbackvalue;

    public function setUp() {
        global $DB;

        $this->setAdminUser();
        $gen = $this->getDataGenerator();
        $this->eventuser = $gen->create_user();         $course = $gen->create_course();                 role_assign(1, $this->eventuser->id, context_course::instance($course->id));

                $record = new stdClass();
        $record->course = $course->id;
        $feedback = $gen->create_module('feedback', $record);
        $this->eventfeedback = $DB->get_record('feedback', array('id' => $feedback->id), '*', MUST_EXIST);         $this->eventcm = get_coursemodule_from_instance('feedback', $this->eventfeedback->id, false, MUST_EXIST);

                $item = new stdClass();
        $item->feedback = $this->eventfeedback->id;
        $item->type = 'numeric';
        $item->presentation = '0|0';
        $itemid = $DB->insert_record('feedback_item', $item);
        $this->eventfeedbackitem = $DB->get_record('feedback_item', array('id' => $itemid), '*', MUST_EXIST);

                $response = new stdClass();
        $response->feedback = $this->eventfeedback->id;
        $response->userid = $this->eventuser->id;
        $response->anonymous_response = FEEDBACK_ANONYMOUS_YES;
        $completedid = $DB->insert_record('feedback_completed', $response);
        $this->eventfeedbackcompleted = $DB->get_record('feedback_completed', array('id' => $completedid), '*', MUST_EXIST);

        $value = new stdClass();
        $value->course_id = $course->id;
        $value->item = $this->eventfeedbackitem->id;
        $value->completed = $this->eventfeedbackcompleted->id;
        $value->value = 25;         $valueid = $DB->insert_record('feedback_value', $value);
        $this->eventfeedbackvalue = $DB->get_record('feedback_value', array('id' => $valueid), '*', MUST_EXIST);
                $this->eventcourse = $DB->get_record('course', array('id' => $course->id), '*', MUST_EXIST);

    }

    
    public function test_response_deleted_event() {
        global $USER, $DB;
        $this->resetAfterTest();

                $sink = $this->redirectEvents();
        feedback_delete_completed($this->eventfeedbackcompleted->id);
        $events = $sink->get_events();
        $event = array_pop($events);         $sink->close();

                $this->assertInstanceOf('\mod_feedback\event\response_deleted', $event);
        $this->assertEquals($this->eventfeedbackcompleted->id, $event->objectid);
        $this->assertEquals($USER->id, $event->userid);
        $this->assertEquals($this->eventuser->id, $event->relateduserid);
        $this->assertEquals('feedback_completed', $event->objecttable);
        $this->assertEquals(null, $event->get_url());
        $this->assertEquals($this->eventfeedbackcompleted, $event->get_record_snapshot('feedback_completed', $event->objectid));
        $this->assertEquals($this->eventcourse, $event->get_record_snapshot('course', $event->courseid));
        $this->assertEquals($this->eventfeedback, $event->get_record_snapshot('feedback', $event->other['instanceid']));

                $arr = array($this->eventcourse->id, 'feedback', 'delete', 'view.php?id=' . $this->eventcm->id, $this->eventfeedback->id,
                $this->eventfeedback->id);
        $this->assertEventLegacyLogData($arr, $event);
        $this->assertEventContextNotUsed($event);

                $this->setUser($this->eventuser);
        $this->assertFalse($event->can_view());
        $this->assertDebuggingCalled();
        $this->setAdminUser();
        $this->assertTrue($event->can_view());
        $this->assertDebuggingCalled();

                $response = new stdClass();
        $response->feedback = $this->eventcm->instance;
        $response->userid = $this->eventuser->id;
        $response->anonymous_response = FEEDBACK_ANONYMOUS_NO;
        $completedid = $DB->insert_record('feedback_completed', $response);
        $DB->get_record('feedback_completed', array('id' => $completedid), '*', MUST_EXIST);
        $value = new stdClass();
        $value->course_id = $this->eventcourse->id;
        $value->item = $this->eventfeedbackitem->id;
        $value->completed = $completedid;
        $value->value = 25;         $DB->insert_record('feedback_valuetmp', $value);

                $sink = $this->redirectEvents();
        feedback_delete_completed($completedid);
        $events = $sink->get_events();
        $event = array_pop($events);         $sink->close();

                $this->setUser($this->eventuser);
        $this->assertTrue($event->can_view());
        $this->assertDebuggingCalled();
        $this->setAdminUser();
        $this->assertTrue($event->can_view());
        $this->assertDebuggingCalled();
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_response_deleted_event_exceptions() {

        $this->resetAfterTest();

        $context = context_module::instance($this->eventcm->id);

                try {
            \mod_feedback\event\response_submitted::create(array(
                'context'  => $context,
                'objectid' => $this->eventfeedbackcompleted->id,
                'relateduserid' => 2,
            ));
            $this->fail("Event validation should not allow \\mod_feedback\\event\\response_deleted to be triggered without
                    other['anonymous']");
        } catch (coding_exception $e) {
            $this->assertContains("The 'anonymous' value must be set in other.", $e->getMessage());
        }
    }

    
    public function test_response_submitted_event() {
        global $USER, $DB;
        $this->resetAfterTest();
        $this->setUser($this->eventuser);

                $response = new stdClass();
        $response->feedback = $this->eventcm->instance;
        $response->userid = $this->eventuser->id;
        $response->anonymous_response = FEEDBACK_ANONYMOUS_YES;
        $completedid = $DB->insert_record('feedback_completedtmp', $response);
        $completed = $DB->get_record('feedback_completedtmp', array('id' => $completedid), '*', MUST_EXIST);
        $value = new stdClass();
        $value->course_id = $this->eventcourse->id;
        $value->item = $this->eventfeedbackitem->id;
        $value->completed = $completedid;
        $value->value = 25;         $DB->insert_record('feedback_valuetmp', $value);

                $sink = $this->redirectEvents();
        $id = feedback_save_tmp_values($completed, false);
        $events = $sink->get_events();
        $event = array_pop($events);         $sink->close();

                $this->assertInstanceOf('\mod_feedback\event\response_submitted', $event);
        $this->assertEquals($id, $event->objectid);
        $this->assertEquals($USER->id, $event->userid);
        $this->assertEquals($USER->id, $event->relateduserid);
        $this->assertEquals('feedback_completed', $event->objecttable);
        $this->assertEquals(1, $event->anonymous);
        $this->assertEquals(FEEDBACK_ANONYMOUS_YES, $event->other['anonymous']);
        $this->setUser($this->eventuser);
        $this->assertFalse($event->can_view());
        $this->assertDebuggingCalled();
        $this->setAdminUser();
        $this->assertTrue($event->can_view());
        $this->assertDebuggingCalled();

                $this->assertEventLegacyLogData(null, $event);

                $response = new stdClass();
        $response->feedback = $this->eventcm->instance;
        $response->userid = $this->eventuser->id;
        $response->anonymous_response = FEEDBACK_ANONYMOUS_NO;
        $completedid = $DB->insert_record('feedback_completedtmp', $response);
        $completed = $DB->get_record('feedback_completedtmp', array('id' => $completedid), '*', MUST_EXIST);
        $value = new stdClass();
        $value->course_id = $this->eventcourse->id;
        $value->item = $this->eventfeedbackitem->id;
        $value->completed = $completedid;
        $value->value = 25;         $DB->insert_record('feedback_valuetmp', $value);

                $sink = $this->redirectEvents();
        feedback_save_tmp_values($completed, false);
        $events = $sink->get_events();
        $event = array_pop($events);         $sink->close();

                $arr = array($this->eventcourse->id, 'feedback', 'submit', 'view.php?id=' . $this->eventcm->id, $this->eventfeedback->id,
                     $this->eventcm->id, $this->eventuser->id);
        $this->assertEventLegacyLogData($arr, $event);

                $this->assertTrue($event->can_view());
        $this->assertDebuggingCalled();
        $this->setAdminUser();
        $this->assertTrue($event->can_view());
        $this->assertDebuggingCalled();
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_response_submitted_event_exceptions() {

        $this->resetAfterTest();

        $context = context_module::instance($this->eventcm->id);

                try {
            \mod_feedback\event\response_submitted::create(array(
                'context'  => $context,
                'objectid' => $this->eventfeedbackcompleted->id,
                'relateduserid' => 2,
                'anonymous' => 0,
                'other'    => array('cmid' => $this->eventcm->id, 'anonymous' => 2)
            ));
            $this->fail("Event validation should not allow \\mod_feedback\\event\\response_deleted to be triggered without
                    other['instanceid']");
        } catch (coding_exception $e) {
            $this->assertContains("The 'instanceid' value must be set in other.", $e->getMessage());
        }

                try {
            \mod_feedback\event\response_submitted::create(array(
                'context'  => $context,
                'objectid' => $this->eventfeedbackcompleted->id,
                'relateduserid' => 2,
                'anonymous' => 0,
                'other'    => array('instanceid' => $this->eventfeedback->id, 'anonymous' => 2)
            ));
            $this->fail("Event validation should not allow \\mod_feedback\\event\\response_deleted to be triggered without
                    other['cmid']");
        } catch (coding_exception $e) {
            $this->assertContains("The 'cmid' value must be set in other.", $e->getMessage());
        }

                try {
            \mod_feedback\event\response_submitted::create(array(
                 'context'  => $context,
                 'objectid' => $this->eventfeedbackcompleted->id,
                 'relateduserid' => 2,
                 'other'    => array('cmid' => $this->eventcm->id, 'instanceid' => $this->eventfeedback->id)
            ));
            $this->fail("Event validation should not allow \\mod_feedback\\event\\response_deleted to be triggered without
                    other['anonymous']");
        } catch (coding_exception $e) {
            $this->assertContains("The 'anonymous' value must be set in other.", $e->getMessage());
        }
    }
}

