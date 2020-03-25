<?php



defined('MOODLE_INTERNAL') || die();


class core_eventslib_testcase extends advanced_testcase {

    const DEBUGGING_MSG = 'Events API using $handlers array has been deprecated in favour of Events 2 API, please use it instead.';

    
    protected function setUp() {
        parent::setUp();
                eventslib_sample_function_handler('reset');
        eventslib_sample_handler_class::static_method('reset');

        $this->resetAfterTest();
    }

    
    public function test_events_update_definition__install() {
        global $DB;

        events_update_definition('unittest');
        $this->assertDebuggingCalled(self::DEBUGGING_MSG, DEBUG_DEVELOPER);

        $dbcount = $DB->count_records('events_handlers', array('component'=>'unittest'));
        $handlers = array();
        require(__DIR__.'/fixtures/events.php');
        $this->assertCount($dbcount, $handlers, 'Equal number of handlers in file and db: %s');
    }

    
    public function test_events_update_definition__uninstall() {
        global $DB;

        events_update_definition('unittest');
        $this->assertDebuggingCalled(self::DEBUGGING_MSG, DEBUG_DEVELOPER);

        events_uninstall('unittest');
        $this->assertEquals(0, $DB->count_records('events_handlers', array('component'=>'unittest')), 'All handlers should be uninstalled: %s');
    }

    
    public function test_events_update_definition__update() {
        global $DB;

        events_update_definition('unittest');
        $this->assertDebuggingCalled(self::DEBUGGING_MSG, DEBUG_DEVELOPER);

                $handler = $DB->get_record('events_handlers', array('component'=>'unittest', 'eventname'=>'test_instant'));

        $original = $handler->handlerfunction;

                $DB->set_field('events_handlers', 'handlerfunction', serialize('some_other_function_handler'), array('id'=>$handler->id));

                events_update_definition('unittest');
        $this->assertDebuggingCalled(self::DEBUGGING_MSG, DEBUG_DEVELOPER);
        $handler = $DB->get_record('events_handlers', array('component'=>'unittest', 'eventname'=>'test_instant'));
        $this->assertSame($handler->handlerfunction, $original, 'update should sync db with file definition: %s');
    }

    
    public function test_events_is_registered() {

        events_update_definition('unittest');
        $this->assertDebuggingCalled(self::DEBUGGING_MSG, DEBUG_DEVELOPER);

        $this->assertTrue(events_is_registered('test_instant', 'unittest'));
        $this->assertDebuggingCalled('events_is_registered() has been deprecated along with all Events 1 API in favour of Events 2' .
            ' API, please use it instead.', DEBUG_DEVELOPER);
    }

    
    public function test_events_trigger_legacy_instant() {

        events_update_definition('unittest');
        $this->assertDebuggingCalled(self::DEBUGGING_MSG, DEBUG_DEVELOPER);

        $this->assertEquals(0, events_trigger_legacy('test_instant', 'ok'));
        $this->assertDebuggingCalled(self::DEBUGGING_MSG, DEBUG_DEVELOPER);
        $this->assertEquals(0, events_trigger_legacy('test_instant', 'ok'));
        $this->assertDebuggingCalled(self::DEBUGGING_MSG, DEBUG_DEVELOPER);
        $this->assertEquals(2, eventslib_sample_function_handler('status'));
    }

    
    public function test_events_trigger__cron() {

        events_update_definition('unittest');
        $this->assertDebuggingCalled(self::DEBUGGING_MSG, DEBUG_DEVELOPER);

        $this->assertEquals(0, events_trigger_legacy('test_cron', 'ok'));
        $this->assertEquals(0, eventslib_sample_handler_class::static_method('status'));
        events_cron('test_cron');
                $this->assertDebuggingCalledCount(2, array(self::DEBUGGING_MSG, self::DEBUGGING_MSG),
            array(DEBUG_DEVELOPER, DEBUG_DEVELOPER));
        $this->assertEquals(1, eventslib_sample_handler_class::static_method('status'));
    }

    
    public function test_events_pending_count() {

        events_update_definition('unittest');
        $this->assertDebuggingCalled(self::DEBUGGING_MSG, DEBUG_DEVELOPER);

        events_trigger_legacy('test_cron', 'ok');
        $this->assertDebuggingNotCalled();
        events_trigger_legacy('test_cron', 'ok');
        $this->assertDebuggingNotCalled();
        events_cron('test_cron');
                $this->assertDebuggingCalledCount(3);
        $this->assertEquals(0, events_pending_count('test_cron'), 'all messages should be already dequeued: %s');
        $this->assertDebuggingCalled('events_pending_count() has been deprecated along with all Events 1 API in favour of Events 2' .
            ' API, please use it instead.', DEBUG_DEVELOPER);
    }

    
    public function test_events_trigger__failed_instant() {
        global $CFG;

        events_update_definition('unittest');
        $this->assertDebuggingCalled(self::DEBUGGING_MSG, DEBUG_DEVELOPER);

        $olddebug = $CFG->debug;

        $this->assertEquals(1, events_trigger_legacy('test_instant', 'fail'), 'fail first event: %s');
        $this->assertDebuggingCalled(self::DEBUGGING_MSG, DEBUG_DEVELOPER);
        $this->assertEquals(1, events_trigger_legacy('test_instant', 'ok'), 'this one should fail too: %s');
        $this->assertDebuggingNotCalled();

        $this->assertEquals(0, events_cron('test_instant'), 'all events should stay in queue: %s');
                $this->assertDebuggingCalledCount(3);

        $this->assertEquals(2, events_pending_count('test_instant'), 'two events should in queue: %s');
        $this->assertDebuggingCalled('events_pending_count() has been deprecated along with all Events 1 API in favour of Events 2' .
            ' API, please use it instead.', DEBUG_DEVELOPER);

        $this->assertEquals(0, eventslib_sample_function_handler('status'), 'verify no event dispatched yet: %s');
        eventslib_sample_function_handler('ignorefail');         $this->assertEquals(1, events_trigger_legacy('test_instant', 'ok'), 'this one should go to queue directly: %s');
        $this->assertDebuggingNotCalled();

        $this->assertEquals(3, events_pending_count('test_instant'), 'three events should in queue: %s');
        $this->assertDebuggingCalled('events_pending_count() has been deprecated along with all Events 1 API in favour of Events 2' .
            ' API, please use it instead.', DEBUG_DEVELOPER);

        $this->assertEquals(0, eventslib_sample_function_handler('status'), 'verify previous event was not dispatched: %s');
        $this->assertEquals(3, events_cron('test_instant'), 'all events should be dispatched: %s');
                $this->assertDebuggingCalledCount(4);

        $this->assertEquals(3, eventslib_sample_function_handler('status'), 'verify three events were dispatched: %s');
        $this->assertEquals(0, events_pending_count('test_instant'), 'no events should in queue: %s');
        $this->assertDebuggingCalled('events_pending_count() has been deprecated along with all Events 1 API in favour of Events 2' .
            ' API, please use it instead.', DEBUG_DEVELOPER);

        $this->assertEquals(0, events_trigger_legacy('test_instant', 'ok'), 'this event should be dispatched immediately: %s');
        $this->assertDebuggingCalled(self::DEBUGGING_MSG, DEBUG_DEVELOPER);

        $this->assertEquals(4, eventslib_sample_function_handler('status'), 'verify event was dispatched: %s');
        $this->assertEquals(0, events_pending_count('test_instant'), 'no events should in queue: %s');
        $this->assertDebuggingCalled('events_pending_count() has been deprecated along with all Events 1 API in favour of Events 2' .
            ' API, please use it instead.', DEBUG_DEVELOPER);
    }

    
    public function test_events_trigger_debugging() {

        events_update_definition('unittest');
        $this->assertDebuggingCalled(self::DEBUGGING_MSG, DEBUG_DEVELOPER);

        $this->assertEquals(0, events_trigger('test_instant', 'ok'));
        $debugmessages = array('events_trigger() is deprecated, please use new events instead', self::DEBUGGING_MSG);
        $this->assertDebuggingCalledCount(2, $debugmessages, array(DEBUG_DEVELOPER, DEBUG_DEVELOPER));
    }
}


function eventslib_sample_function_handler($eventdata) {
    static $called = 0;
    static $ignorefail = false;

    if ($eventdata == 'status') {
        return $called;

    } else if ($eventdata == 'reset') {
        $called = 0;
        $ignorefail = false;
        return;

    } else if ($eventdata == 'fail') {
        if ($ignorefail) {
            $called++;
            return true;
        } else {
            return false;
        }

    } else if ($eventdata == 'ignorefail') {
        $ignorefail = true;
        return;

    } else if ($eventdata == 'ok') {
        $called++;
        return true;
    }

    print_error('invalideventdata', '', '', $eventdata);
}



class eventslib_sample_handler_class {
    public static function static_method($eventdata) {
        static $called = 0;
        static $ignorefail = false;

        if ($eventdata == 'status') {
            return $called;

        } else if ($eventdata == 'reset') {
            $called = 0;
            $ignorefail = false;
            return;

        } else if ($eventdata == 'fail') {
            if ($ignorefail) {
                $called++;
                return true;
            } else {
                return false;
            }

        } else if ($eventdata == 'ignorefail') {
            $ignorefail = true;
            return;

        } else if ($eventdata == 'ok') {
            $called++;
            return true;
        }

        print_error('invalideventdata', '', '', $eventdata);
    }
}
