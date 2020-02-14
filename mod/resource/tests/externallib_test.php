<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');


class mod_resource_external_testcase extends externallib_advanced_testcase {

    
    public function test_view_resource() {
        global $DB;

        $this->resetAfterTest(true);

        $this->setAdminUser();
                $course = $this->getDataGenerator()->create_course();
        $resource = $this->getDataGenerator()->create_module('resource', array('course' => $course->id));
        $context = context_module::instance($resource->cmid);
        $cm = get_coursemodule_from_instance('resource', $resource->id);

                try {
            mod_resource_external::view_resource(0);
            $this->fail('Exception expected due to invalid mod_resource instance id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('invalidrecord', $e->errorcode);
        }

                $user = self::getDataGenerator()->create_user();
        $this->setUser($user);
        try {
            mod_resource_external::view_resource($resource->id);
            $this->fail('Exception expected due to not enrolled user.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

                $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $studentrole->id);

                $sink = $this->redirectEvents();

        $result = mod_resource_external::view_resource($resource->id);
        $result = external_api::clean_returnvalue(mod_resource_external::view_resource_returns(), $result);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_resource\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $moodleurl = new \moodle_url('/mod/resource/view.php', array('id' => $cm->id));
        $this->assertEquals($moodleurl, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

                        assign_capability('mod/resource:view', CAP_PROHIBIT, $studentrole->id, $context->id);
                accesslib_clear_all_caches_for_unit_testing();
        course_modinfo::clear_instance_cache();

        try {
            mod_resource_external::view_resource($resource->id);
            $this->fail('Exception expected due to missing capability.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

    }
}
