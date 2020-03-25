<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');


class enrol_guest_external_testcase extends externallib_advanced_testcase {

    
    public function test_get_instance_info() {
        global $DB;

        $this->resetAfterTest(true);

                $guestplugin = enrol_get_plugin('guest');
        $this->assertNotEmpty($guestplugin);

        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

        $coursedata = new stdClass();
        $coursedata->visible = 0;
        $course = self::getDataGenerator()->create_course($coursedata);

        $student = self::getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, $studentrole->id, 'manual');

                $instance = $guestplugin->add_instance($course, array('status' => ENROL_INSTANCE_ENABLED,
                                                                'name' => 'Test instance',
                                                                'customint6' => 1,
                                                                'roleid' => $studentrole->id));

        $this->setAdminUser();
        $result = enrol_guest_external::get_instance_info($instance);
        $result = external_api::clean_returnvalue(enrol_guest_external::get_instance_info_returns(), $result);

        $this->assertEquals($instance, $result['instanceinfo']['id']);
        $this->assertEquals($course->id, $result['instanceinfo']['courseid']);
        $this->assertEquals('guest', $result['instanceinfo']['type']);
        $this->assertEquals('Test instance', $result['instanceinfo']['name']);
        $this->assertTrue($result['instanceinfo']['status']);
        $this->assertFalse($result['instanceinfo']['passwordrequired']);

        $DB->set_field('enrol', 'status', ENROL_INSTANCE_DISABLED, array('id' => $instance));

        $result = enrol_guest_external::get_instance_info($instance);
        $result = external_api::clean_returnvalue(enrol_guest_external::get_instance_info_returns(), $result);
        $this->assertEquals($instance, $result['instanceinfo']['id']);
        $this->assertEquals($course->id, $result['instanceinfo']['courseid']);
        $this->assertEquals('guest', $result['instanceinfo']['type']);
        $this->assertEquals('Test instance', $result['instanceinfo']['name']);
        $this->assertFalse($result['instanceinfo']['status']);
        $this->assertFalse($result['instanceinfo']['passwordrequired']);

        $DB->set_field('enrol', 'status', ENROL_INSTANCE_ENABLED, array('id' => $instance));

                $user = self::getDataGenerator()->create_user();
        $this->setUser($user);
        try {
            enrol_guest_external::get_instance_info($instance);
        } catch (moodle_exception $e) {
            $this->assertEquals('coursehidden', $e->errorcode);
        }

                $DB->set_field('course', 'visible', 1, array('id' => $course->id));
        $this->setUser($student);
        $result = enrol_guest_external::get_instance_info($instance);
        $result = external_api::clean_returnvalue(enrol_guest_external::get_instance_info_returns(), $result);

        $this->assertEquals($instance, $result['instanceinfo']['id']);
        $this->assertEquals($course->id, $result['instanceinfo']['courseid']);
        $this->assertEquals('guest', $result['instanceinfo']['type']);
        $this->assertEquals('Test instance', $result['instanceinfo']['name']);
        $this->assertTrue($result['instanceinfo']['status']);
        $this->assertFalse($result['instanceinfo']['passwordrequired']);
    }
}
