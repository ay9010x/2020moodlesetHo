<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/enrol/manual/externallib.php');

class enrol_manual_externallib_testcase extends externallib_advanced_testcase {

    
    public function test_enrol_users() {
        global $DB;

        $this->resetAfterTest(true);

        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);

        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();

        $context1 = context_course::instance($course1->id);
        $context2 = context_course::instance($course2->id);
        $instance1 = $DB->get_record('enrol', array('courseid' => $course1->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $instance2 = $DB->get_record('enrol', array('courseid' => $course2->id, 'enrol' => 'manual'), '*', MUST_EXIST);

                $roleid = $this->assignUserCapability('enrol/manual:enrol', $context1->id);
        $this->assignUserCapability('moodle/course:view', $context1->id, $roleid);
        $this->assignUserCapability('moodle/role:assign', $context1->id, $roleid);
        $this->assignUserCapability('enrol/manual:enrol', $context2->id, $roleid);
        $this->assignUserCapability('moodle/course:view', $context2->id, $roleid);
        $this->assignUserCapability('moodle/role:assign', $context2->id, $roleid);

        allow_assign($roleid, 3);

                enrol_manual_external::enrol_users(array(
            array('roleid' => 3, 'userid' => $user1->id, 'courseid' => $course1->id),
            array('roleid' => 3, 'userid' => $user2->id, 'courseid' => $course1->id),
        ));

        $this->assertEquals(2, $DB->count_records('user_enrolments', array('enrolid' => $instance1->id)));
        $this->assertEquals(0, $DB->count_records('user_enrolments', array('enrolid' => $instance2->id)));
        $this->assertTrue(is_enrolled($context1, $user1));
        $this->assertTrue(is_enrolled($context1, $user2));

                $DB->delete_records('user_enrolments');
        $this->unassignUserCapability('enrol/manual:enrol', $context1->id, $roleid);
        try {
            enrol_manual_external::enrol_users(array(
                array('roleid' => 3, 'userid' => $user1->id, 'courseid' => $course1->id),
            ));
            $this->fail('Exception expected if not having capability to enrol');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('required_capability_exception', $e);
            $this->assertSame('nopermissions', $e->errorcode);
        }
        $this->assignUserCapability('enrol/manual:enrol', $context1->id, $roleid);
        $this->assertEquals(0, $DB->count_records('user_enrolments'));

                try {
            enrol_manual_external::enrol_users(array(
                array('roleid' => 1, 'userid' => $user1->id, 'courseid' => $course1->id),
            ));
            $this->fail('Exception expected if not allowed to assign role.');
        } catch (moodle_exception $e) {
            $this->assertSame('wsusercannotassign', $e->errorcode);
        }
        $this->assertEquals(0, $DB->count_records('user_enrolments'));

                $DB->delete_records('user_enrolments');
        $DB->delete_records('enrol', array('courseid' => $course2->id));
        try {
            enrol_manual_external::enrol_users(array(
                array('roleid' => 3, 'userid' => $user1->id, 'courseid' => $course1->id),
                array('roleid' => 3, 'userid' => $user1->id, 'courseid' => $course2->id),
            ));
            $this->fail('Exception expected if course does not have manual instance');
        } catch (moodle_exception $e) {
            $this->assertSame('wsnoinstance', $e->errorcode);
        }
    }

    
    public function test_unenrol_user_single() {
        global $CFG, $DB;
        require_once($CFG->libdir . '/enrollib.php');
        $this->resetAfterTest(true);
                $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);         $enrol = enrol_get_plugin('manual');
                $course = self::getDataGenerator()->create_course();
        $coursecontext = context_course::instance($course->id);
        $enrolinstance = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'manual'), '*', MUST_EXIST);
                $roleid = $this->assignUserCapability('enrol/manual:enrol', $coursecontext);
        $this->assignUserCapability('enrol/manual:unenrol', $coursecontext, $roleid);
        $this->assignUserCapability('moodle/course:view', $coursecontext, $roleid);
        $this->assignUserCapability('moodle/role:assign', $coursecontext, $roleid);
                $student = $this->getDataGenerator()->create_user();
        $enrol->enrol_user($enrolinstance, $student->id);
        $this->assertTrue(is_enrolled($coursecontext, $student));
                enrol_manual_external::unenrol_users(array(
            array('userid' => $student->id, 'courseid' => $course->id),
        ));
        $this->assertFalse(is_enrolled($coursecontext, $student));
    }

    
    public function test_unenrol_user_multiple() {
        global $CFG, $DB;
        require_once($CFG->libdir . '/enrollib.php');
        $this->resetAfterTest(true);
                $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);                 $course = self::getDataGenerator()->create_course();
        $coursecontext = context_course::instance($course->id);
        $enrolinstance = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'manual'), '*', MUST_EXIST);
                $roleid = $this->assignUserCapability('enrol/manual:enrol', $coursecontext);
        $this->assignUserCapability('enrol/manual:unenrol', $coursecontext, $roleid);
        $this->assignUserCapability('moodle/course:view', $coursecontext, $roleid);
        $this->assignUserCapability('moodle/role:assign', $coursecontext, $roleid);
        $enrol = enrol_get_plugin('manual');
                $student1 = $this->getDataGenerator()->create_user();
        $enrol->enrol_user($enrolinstance, $student1->id);
        $this->assertTrue(is_enrolled($coursecontext, $student1));
        $student2 = $this->getDataGenerator()->create_user();
        $enrol->enrol_user($enrolinstance, $student2->id);
        $this->assertTrue(is_enrolled($coursecontext, $student2));
                enrol_manual_external::unenrol_users(array(
            array('userid' => $student1->id, 'courseid' => $course->id),
            array('userid' => $student2->id, 'courseid' => $course->id),
        ));
        $this->assertFalse(is_enrolled($coursecontext, $student1));
        $this->assertFalse(is_enrolled($coursecontext, $student2));
    }

    
    public function test_unenrol_user_error_no_capability() {
        global $CFG, $DB;
        require_once($CFG->libdir . '/enrollib.php');
        $this->resetAfterTest(true);
                $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);                 $course = self::getDataGenerator()->create_course();
        $coursecontext = context_course::instance($course->id);
        $enrolinstance = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $enrol = enrol_get_plugin('manual');
                $student = $this->getDataGenerator()->create_user();
        $enrol->enrol_user($enrolinstance, $student->id);
        $this->assertTrue(is_enrolled($coursecontext, $student));
                try {
            enrol_manual_external::unenrol_users(array(
                array('userid' => $student->id, 'courseid' => $course->id),
            ));
            $this->fail('Exception expected: User cannot log in to the course');
        } catch (Exception $ex) {
            $this->assertTrue($ex instanceof require_login_exception);
        }
                $roleid = $this->assignUserCapability('moodle/course:view', $coursecontext);
        try {
            enrol_manual_external::unenrol_users(array(
                array('userid' => $student->id, 'courseid' => $course->id),
            ));
            $this->fail('Exception expected: User cannot log in to the course');
        } catch (Exception $ex) {
            $this->assertTrue($ex instanceof required_capability_exception);
        }
                $this->assignUserCapability('enrol/manual:unenrol', $coursecontext, $roleid);
        enrol_manual_external::unenrol_users(array(
            array('userid' => $student->id, 'courseid' => $course->id),
        ));
        $this->assertFalse(is_enrolled($coursecontext, $student));
    }

    
    public function test_unenrol_user_error_not_exist() {
        global $CFG, $DB;
        require_once($CFG->libdir . '/enrollib.php');
        $this->resetAfterTest(true);
                $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);         $enrol = enrol_get_plugin('manual');
                $course = self::getDataGenerator()->create_course();
        $coursecontext = context_course::instance($course->id);
        $enrolinstance = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'manual'), '*', MUST_EXIST);
                $roleid = $this->assignUserCapability('enrol/manual:enrol', $coursecontext);
        $this->assignUserCapability('enrol/manual:unenrol', $coursecontext, $roleid);
        $this->assignUserCapability('moodle/course:view', $coursecontext, $roleid);
        $this->assignUserCapability('moodle/role:assign', $coursecontext, $roleid);
                $student = $this->getDataGenerator()->create_user();
        $enrol->enrol_user($enrolinstance, $student->id);
        $this->assertTrue(is_enrolled($coursecontext, $student));
        try {
            enrol_manual_external::unenrol_users(array(
                array('userid' => $student->id + 1, 'courseid' => $course->id),
            ));
            $this->fail('Exception expected: invalid student id');
        } catch (Exception $ex) {
            $this->assertTrue($ex instanceof invalid_parameter_exception);
        }
        $DB->delete_records('enrol', array('id' => $enrolinstance->id));
        try {
            enrol_manual_external::unenrol_users(array(
                array('userid' => $student->id + 1, 'courseid' => $course->id),
            ));
            $this->fail('Exception expected: invalid student id');
        } catch (Exception $ex) {
            $this->assertTrue($ex instanceof moodle_exception);
        }
    }
}
