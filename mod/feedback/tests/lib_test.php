<?php

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/mod/feedback/lib.php');


class mod_feedback_lib_testcase extends advanced_testcase {

    
    public function test_feedback_refresh_events() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $timeopen = time();
        $timeclose = time() + 86400;

        $course = $this->getDataGenerator()->create_course();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_feedback');
        $params['course'] = $course->id;
        $params['timeopen'] = $timeopen;
        $params['timeclose'] = $timeclose;
        $feedback = $generator->create_instance($params);
        $cm = get_coursemodule_from_instance('feedback', $feedback->id);
        $context = context_module::instance($cm->id);

                $this->assertTrue(feedback_refresh_events($course->id));
        $eventparams = array('modulename' => 'feedback', 'instance' => $feedback->id, 'eventtype' => 'open');
        $openevent = $DB->get_record('event', $eventparams, '*', MUST_EXIST);
        $this->assertEquals($openevent->timestart, $timeopen);

        $eventparams = array('modulename' => 'feedback', 'instance' => $feedback->id, 'eventtype' => 'close');
        $closeevent = $DB->get_record('event', $eventparams, '*', MUST_EXIST);
        $this->assertEquals($closeevent->timestart, $timeclose);
                $this->assertTrue(feedback_refresh_events('' . $course->id));
                $this->assertTrue(feedback_refresh_events());
        $eventparams = array('modulename' => 'feedback');
        $events = $DB->get_records('event', $eventparams);
        foreach ($events as $event) {
            if ($event->modulename === 'feedback' && $event->instance === $feedback->id && $event->eventtype === 'open') {
                $this->assertEquals($event->timestart, $timeopen);
            }
            if ($event->modulename === 'feedback' && $event->instance === $feedback->id && $event->eventtype === 'close') {
                $this->assertEquals($event->timestart, $timeclose);
            }
        }
    }
}
