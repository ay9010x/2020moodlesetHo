<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');


class mod_imscp_external_testcase extends externallib_advanced_testcase {

    
    public function test_view_imscp() {
        global $DB;

        $this->resetAfterTest(true);

        $this->setAdminUser();
                $course = $this->getDataGenerator()->create_course();
        $imscp = $this->getDataGenerator()->create_module('imscp', array('course' => $course->id));
        $context = context_module::instance($imscp->cmid);
        $cm = get_coursemodule_from_instance('imscp', $imscp->id);

                try {
            mod_imscp_external::view_imscp(0);
            $this->fail('Exception expected due to invalid mod_imscp instance id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('invalidrecord', $e->errorcode);
        }

                $user = self::getDataGenerator()->create_user();
        $this->setUser($user);
        try {
            mod_imscp_external::view_imscp($imscp->id);
            $this->fail('Exception expected due to not enrolled user.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

                $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $studentrole->id);

                $sink = $this->redirectEvents();

        $result = mod_imscp_external::view_imscp($imscp->id);
        $result = external_api::clean_returnvalue(mod_imscp_external::view_imscp_returns(), $result);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_imscp\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $moodleurl = new \moodle_url('/mod/imscp/view.php', array('id' => $cm->id));
        $this->assertEquals($moodleurl, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

                        assign_capability('mod/imscp:view', CAP_PROHIBIT, $studentrole->id, $context->id);
                accesslib_clear_all_caches_for_unit_testing();
        course_modinfo::clear_instance_cache();

        try {
            mod_imscp_external::view_imscp($imscp->id);
            $this->fail('Exception expected due to missing capability.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

    }

    
    public function test_get_imscps_by_courses() {
        global $DB, $USER;
        $this->resetAfterTest(true);
                $this->setAdminUser();
        $course1 = self::getDataGenerator()->create_course();
        $imscpoptions1 = array(
          'course' => $course1->id,
          'name' => 'First IMSCP'
        );
        $imscp1 = self::getDataGenerator()->create_module('imscp', $imscpoptions1);
        $course2 = self::getDataGenerator()->create_course();

        $imscpoptions2 = array(
          'course' => $course2->id,
          'name' => 'Second IMSCP'
        );
        $imscp2 = self::getDataGenerator()->create_module('imscp', $imscpoptions2);
        $student1 = $this->getDataGenerator()->create_user();

        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

                self::getDataGenerator()->enrol_user($student1->id,  $course1->id, $studentrole->id);

        $this->setUser($student1);
        $imscps = mod_imscp_external::get_imscps_by_courses(array());
        $imscps = external_api::clean_returnvalue(mod_imscp_external::get_imscps_by_courses_returns(), $imscps);
        $this->assertCount(1, $imscps['imscps']);
        $this->assertEquals('First IMSCP', $imscps['imscps'][0]['name']);
                $this->assertFalse(isset($imscps['imscps'][0]['section']));

                        $imscps = mod_imscp_external::get_imscps_by_courses(array($course2->id));
        $imscps = external_api::clean_returnvalue(mod_imscp_external::get_imscps_by_courses_returns(), $imscps);
        $this->assertCount(0, $imscps['imscps']);
        $this->assertEquals(1, $imscps['warnings'][0]['warningcode']);

                $this->setAdminUser();
                $imscps = mod_imscp_external::get_imscps_by_courses(array($course2->id));
        $imscps = external_api::clean_returnvalue(mod_imscp_external::get_imscps_by_courses_returns(), $imscps);
        $this->assertCount(1, $imscps['imscps']);
        $this->assertEquals('Second IMSCP', $imscps['imscps'][0]['name']);
                $this->assertEquals(0, $imscps['imscps'][0]['section']);

                $this->setUser($student1);
        $contextcourse1 = context_course::instance($course1->id);
                assign_capability('mod/imscp:view', CAP_PROHIBIT, $studentrole->id, $contextcourse1->id);
                accesslib_clear_all_caches_for_unit_testing();
        course_modinfo::clear_instance_cache();

        $imscps = mod_imscp_external::get_imscps_by_courses(array($course1->id));
        $imscps = external_api::clean_returnvalue(mod_imscp_external::get_imscps_by_courses_returns(), $imscps);
        $this->assertCount(0, $imscps['imscps']);
    }
}
