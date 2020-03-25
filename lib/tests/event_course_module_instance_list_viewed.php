<?php



defined('MOODLE_INTERNAL') || die();
require_once(__DIR__.'/fixtures/event_mod_fixtures.php');


class core_event_course_module_instance_list_viewed_testcase extends advanced_testcase {

    
    public function test_event_attributes() {

        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);

                $sink = $this->redirectEvents();
        $event = \mod_unittests\event\course_module_instance_list_viewed::create(array(
             'context' => $context,
        ));
        $event->trigger();
        $result = $sink->get_events();
        $event = reset($result);
        $sink->close();

                $legacydata = array($course->id, 'unittests', 'view all', 'index.php?id=' . $course->id, '');
        $this->assertEventLegacyLogData($legacydata, $event);
        $url = new moodle_url('/mod/unittests/index.php', array('id' => $course->id));
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);

    }

    
    public function test_event_validations() {
        try {
            \mod_unittests\event\course_module_instance_list_viewed::create(array('context' => context_system::instance()));
            $this->fail('Event validation should not allow course_module_instance_list_viewed event to be triggered without outside
                    course context');
        } catch (Exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }
    }
}
