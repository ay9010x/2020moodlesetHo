<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/enrol/externallib.php');


class core_enrol_role_external_testcase extends externallib_advanced_testcase {

    
    protected function setUp() {
        global $CFG;
        require_once($CFG->dirroot . '/enrol/externallib.php');
    }

    
    public function test_assign_roles() {
        global $USER;

        $this->resetAfterTest(true);

        $course = self::getDataGenerator()->create_course();

                $context = context_course::instance($course->id);
        $roleid = $this->assignUserCapability('moodle/role:assign', $context->id);
        $this->assignUserCapability('moodle/course:view', $context->id, $roleid);

                        role_assign(1, $USER->id, context_system::instance()->id);

                $users = get_role_users(3, $context);
        $this->assertEquals(count($users), 0);

                core_role_external::assign_roles(array(
            array('roleid' => 3, 'userid' => $USER->id, 'contextid' => $context->id)));

                $users = get_role_users(3, $context);
        $this->assertEquals(count($users), 1);

                role_unassign(3, $USER->id, $context->id);
        $users = get_role_users(3, $context);
        $this->assertEquals(count($users), 0);

                core_role_external::assign_roles(array(
            array('roleid' => 3, 'userid' => $USER->id, 'contextlevel' => "course", 'instanceid' => $course->id)));
        $users = get_role_users(3, $context);
        $this->assertEquals(count($users), 1);

                $this->unassignUserCapability('moodle/role:assign', $context->id, $roleid);
        $this->setExpectedException('moodle_exception');
        $categories = core_role_external::assign_roles(
            array('roleid' => 3, 'userid' => $USER->id, 'contextid' => $context->id));
    }

    
    public function test_unassign_roles() {
        global $USER;

        $this->resetAfterTest(true);

        $course = self::getDataGenerator()->create_course();

                $context = context_course::instance($course->id);
        $roleid = $this->assignUserCapability('moodle/role:assign', $context->id);
        $this->assignUserCapability('moodle/course:view', $context->id, $roleid);

                        role_assign(1, $USER->id, context_system::instance()->id);

                role_assign(3, $USER->id, $context->id);

                $users = get_role_users(3, $context);
        $this->assertEquals(count($users), 1);

                core_role_external::unassign_roles(array(
            array('roleid' => 3, 'userid' => $USER->id, 'contextid' => $context->id)));

                $users = get_role_users(3, $context);
        $this->assertEquals(count($users), 0);

                role_assign(3, $USER->id, $context->id);
        $users = get_role_users(3, $context);
        $this->assertEquals(count($users), 1);

                core_role_external::unassign_roles(array(
            array('roleid' => 3, 'userid' => $USER->id, 'contextlevel' => "course", 'instanceid' => $course->id)));

                $users = get_role_users(3, $context);
        $this->assertEquals(count($users), 0);

                $this->unassignUserCapability('moodle/role:assign', $context->id, $roleid);
        $this->setExpectedException('moodle_exception');
        $categories = core_role_external::unassign_roles(
            array('roleid' => 3, 'userid' => $USER->id, 'contextid' => $context->id));
    }
}