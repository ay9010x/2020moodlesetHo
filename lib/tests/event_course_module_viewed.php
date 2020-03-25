<?php



defined('MOODLE_INTERNAL') || die();
require_once(__DIR__.'/fixtures/event_fixtures.php');


class core_event_course_module_viewed_testcase extends advanced_testcase {

    
    public function test_event_attributes() {

        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $record = new stdClass();
        $record->course = $course->id;
        $feed = $this->getDataGenerator()->create_module('feedback', $record);
        $cm = get_coursemodule_from_instance('feedback', $feed->id);
        $context = context_module::instance($cm->id);

                $sink = $this->redirectEvents();
        $pageevent = \core_tests\event\course_module_viewed::create(array(
            'context' => $context,
            'courseid' => $course->id,
            'objectid' => $feed->id
        ));
        $pageevent->trigger();
        $result = $sink->get_events();
        $event = reset($result);
        $sink->close();

                $legacydata = array($course->id, 'feedback', 'view', 'view.php?id=' . $cm->id, $feed->id, $cm->id);
        $this->assertEventLegacyLogData($legacydata, $event);
        $this->assertSame('feedback', $event->objecttable);
        $url = new moodle_url('/mod/feedback/view.php', array('id' => $cm->id));
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);

    }

    
    public function test_event_validations() {

                try {
            \core_tests\event\course_module_viewed_noinit::create(array(
                'contextid' => 1,
                'courseid' => 2,
                'objectid' => 3 ));
        } catch (coding_exception $e) {
            $this->assertContains("course_module_viewed event must define objectid and object table.", $e->getMessage());
        }

        try {
            \core_tests\event\course_module_viewed::create(array(
                'contextid' => 1,
                'courseid' => 2,
            ));
        } catch (coding_exception $e) {
            $this->assertContains("course_module_viewed event must define objectid and object table.", $e->getMessage());
        }
    }
}
