<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/enrol/externallib.php');


class core_enrol_externallib_testcase extends externallib_advanced_testcase {

    
    public function get_enrolled_users_visibility_provider() {
        return array(
            'Course without groups, default behavior (not filtering by cap, group, active)' =>
            array(
                'settings' => array(
                    'coursegroupmode' => NOGROUPS,
                    'withcapability' => null,
                    'groupid' => null,
                    'onlyactive' => false,
                    'allowedcaps' => array(),
                ),
                'results' => array(                     'user0' => array('canview' => array('user0', 'user1', 'user2', 'user2su', 'user31', 'user32', 'userall')),
                    'user1' => array('canview' => array('user0', 'user1', 'user2', 'user2su', 'user31', 'user32', 'userall')),
                    'user2' => array('canview' => array('user0', 'user1', 'user2', 'user2su', 'user31', 'user32', 'userall')),
                    'user31' => array('canview' => array('user0', 'user1', 'user2', 'user2su', 'user31', 'user32', 'userall')),
                    'userall' => array('canview' => array('user0', 'user1', 'user2', 'user2su', 'user31', 'user32', 'userall')),
                ),
            ),

            'Course with visible groups, default behavior (not filtering by cap, group, active)' =>
            array(
                'settings' => array(
                    'coursegroupmode' => VISIBLEGROUPS,
                    'withcapability' => null,
                    'groupid' => null,
                    'onlyactive' => false,
                    'allowedcaps' => array(),
                ),
                'results' => array(                     'user0' => array('canview' => array('user0', 'user1', 'user2', 'user2su', 'user31', 'user32', 'userall')),
                    'user1' => array('canview' => array('user0', 'user1', 'user2', 'user2su', 'user31', 'user32', 'userall')),
                    'user2' => array('canview' => array('user0', 'user1', 'user2', 'user2su', 'user31', 'user32', 'userall')),
                    'user31' => array('canview' => array('user0', 'user1', 'user2', 'user2su', 'user31', 'user32', 'userall')),
                    'userall' => array('canview' => array('user0', 'user1', 'user2', 'user2su', 'user31', 'user32', 'userall')),
                ),
            ),

            'Course with separate groups, default behavior (not filtering by cap, group, active)' =>
            array(
                'settings' => array(
                    'coursegroupmode' => SEPARATEGROUPS,
                    'withcapability' => null,
                    'groupid' => null,
                    'onlyactive' => false,
                    'allowedcaps' => array(),
                ),
                'results' => array(                     'user0' => array('canview' => array()),                     'user1' => array('canview' => array('user1', 'userall')),
                    'user2' => array('canview' => array('user2', 'user2su', 'userall')),
                    'user31' => array('canview' => array('user31', 'user32', 'userall')),
                    'userall' => array('canview' => array('user1', 'user2', 'user2su', 'user31', 'user32', 'userall')),
                ),
            ),

            'Course with separate groups, default behavior (not filtering but having moodle/site:accessallgroups)' =>
            array(
                'settings' => array(
                    'coursegroupmode' => VISIBLEGROUPS,
                    'withcapability' => null,
                    'groupid' => null,
                    'onlyactive' => false,
                    'allowedcaps' => array('moodle/site:accessallgroups'),
                ),
                'results' => array(                     'user0' => array('canview' => array('user0', 'user1', 'user2', 'user2su', 'user31', 'user32', 'userall')),
                    'user1' => array('canview' => array('user0', 'user1', 'user2', 'user2su', 'user31', 'user32', 'userall')),
                    'user2' => array('canview' => array('user0', 'user1', 'user2', 'user2su', 'user31', 'user32', 'userall')),
                    'user31' => array('canview' => array('user0', 'user1', 'user2', 'user2su', 'user31', 'user32', 'userall')),
                    'userall' => array('canview' => array('user0', 'user1', 'user2', 'user2su', 'user31', 'user32', 'userall')),
                ),
            ),

            'Course with separate groups, filtering onlyactive (missing moodle/course:enrolreview)' =>
            array(
                'settings' => array(
                    'coursegroupmode' => SEPARATEGROUPS,
                    'withcapability' => null,
                    'groupid' => null,
                    'onlyactive' => true,
                    'allowedcaps' => array(),
                ),
                'results' => array(                     'user2' => array('exception' => array(
                        'type' => 'required_capability_exception',
                        'message' => 'Review course enrolments')),
                    'userall' => array('exception' => array(
                        'type' => 'required_capability_exception',
                        'message' => 'Review course enrolments')),
                ),
            ),

            'Course with separate groups, filtering onlyactive (having moodle/course:enrolreview)' =>
            array(
                'settings' => array(
                    'coursegroupmode' => SEPARATEGROUPS,
                    'withcapability' => null,
                    'groupid' => null,
                    'onlyactive' => true,
                    'allowedcaps' => array('moodle/course:enrolreview'),
                ),
                'results' => array(                     'user2' => array('canview' => array('user2', 'userall')),
                    'user31' => array('canview' => array('user31', 'user32', 'userall')),
                    'userall' => array('canview' => array('user1', 'user2', 'user31', 'user32', 'userall')),
                ),
            ),

            'Course with separate groups, filtering by groupid (not having moodle/site:accessallgroups)' =>
            array(
                'settings' => array(
                    'coursegroupmode' => SEPARATEGROUPS,
                    'withcapability' => null,
                    'groupid' => 'group2',
                    'onlyactive' => false,
                    'allowedcaps' => array(),
                ),
                'results' => array(                     'user0' => array('exception' => array(
                        'type' => 'required_capability_exception',
                        'message' => 'Access all groups')),
                    'user1' => array('exception' => array(
                        'type' => 'required_capability_exception',
                        'message' => 'Access all groups')),
                    'user2' => array('canview' => array('user2', 'user2su', 'userall')),
                    'userall' => array('canview' => array('user2', 'user2su', 'userall')),
                ),
            ),

            'Course with separate groups, filtering by groupid (having moodle/site:accessallgroups)' =>
            array(
                'settings' => array(
                    'coursegroupmode' => SEPARATEGROUPS,
                    'withcapability' => null,
                    'groupid' => 'group2',
                    'onlyactive' => false,
                    'allowedcaps' => array('moodle/site:accessallgroups'),
                ),
                'results' => array(                     'user0' => array('canview' => array('user2', 'user2su', 'userall')),
                    'user1' => array('canview' => array('user2', 'user2su', 'userall')),
                    'user2' => array('canview' => array('user2', 'user2su', 'userall')),
                    'userall' => array('canview' => array('user2', 'user2su', 'userall')),
                ),
            ),

            'Course with separate groups, filtering by withcapability (not having moodle/role:review)' =>
            array(
                'settings' => array(
                    'coursegroupmode' => SEPARATEGROUPS,
                    'withcapability' => 'moodle/course:bulkmessaging',
                    'groupid' => null,
                    'onlyactive' => false,
                    'allowedcaps' => array(),
                ),
                'results' => array(                     'user0' => array('exception' => array(
                        'type' => 'required_capability_exception',
                        'message' => 'Review permissions for others')),
                    'user1' => array('exception' => array(
                        'type' => 'required_capability_exception',
                        'message' => 'Review permissions for others')),
                    'user2' => array('exception' => array(
                        'type' => 'required_capability_exception',
                        'message' => 'Review permissions for others')),
                    'userall' => array('exception' => array(
                        'type' => 'required_capability_exception',
                        'message' => 'Review permissions for others')),
                ),
            ),

            'Course with separate groups, filtering by withcapability (having moodle/role:review)' =>
            array(
                'settings' => array(
                    'coursegroupmode' => SEPARATEGROUPS,
                    'withcapability' => 'moodle/course:bulkmessaging',
                    'groupid' => null,
                    'onlyactive' => false,
                    'allowedcaps' => array('moodle/role:review'),
                ),
                'results' => array(                     'user0' => array('canview' => array()),
                    'user1' => array('canview' => array()),
                    'user2' => array('canview' => array()),
                    'userall' => array('canview' => array()),
                ),
            ),

            'Course with separate groups, filtering by withcapability (having moodle/role:review)' =>
            array(
                'settings' => array(
                    'coursegroupmode' => SEPARATEGROUPS,
                    'withcapability' => 'moodle/course:bulkmessaging',
                    'groupid' => null,
                    'onlyactive' => false,
                    'allowedcaps' => array('moodle/role:review', 'moodle/course:bulkmessaging'),
                ),
                'results' => array(                     'user0' => array('canview' => array()),
                    'user1' => array('canview' => array('user1')),
                    'user2' => array('canview' => array('user2')),
                    'userall' => array('canview' => array('user1', 'user2', 'userall')),
                ),
            ),
        );
    }

    
    public function test_get_enrolled_users_visibility($settings, $results) {

        global $USER;

        $this->resetAfterTest();

                $course = $this->getDataGenerator()->create_course(array('groupmode' => $settings['coursegroupmode']));
        $coursecontext = context_course::instance($course->id);
        $user0 = $this->getDataGenerator()->create_user(array('username' => 'user0'));             $user1 = $this->getDataGenerator()->create_user(array('username' => 'user1'));             $user2 = $this->getDataGenerator()->create_user(array('username' => 'user2'));             $user2su = $this->getDataGenerator()->create_user(array('username' => 'user2su'));         $user31 = $this->getDataGenerator()->create_user(array('username' => 'user31'));           $user32 = $this->getDataGenerator()->create_user(array('username' => 'user32'));           $userall = $this->getDataGenerator()->create_user(array('username' => 'userall')); 
                $createdusers = array();
        foreach (array($user0, $user1, $user2, $user2su, $user31, $user32, $userall) as $createduser) {
            $createdusers[$createduser->id] = $createduser->username;
        }

                $this->getDataGenerator()->enrol_user($user0->id, $course->id);
        $this->getDataGenerator()->enrol_user($user1->id, $course->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id);
        $this->getDataGenerator()->enrol_user($user2su->id, $course->id, null, 'manual', 0, 0, ENROL_USER_SUSPENDED);
        $this->getDataGenerator()->enrol_user($user31->id, $course->id);
        $this->getDataGenerator()->enrol_user($user32->id, $course->id);
        $this->getDataGenerator()->enrol_user($userall->id, $course->id);

                $group1 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $group2 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $group3 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));

                $this->getDataGenerator()->create_group_member(array('groupid' => $group1->id, 'userid' => $user1->id));
        $this->getDataGenerator()->create_group_member(array('groupid' => $group2->id, 'userid' => $user2->id));
        $this->getDataGenerator()->create_group_member(array('groupid' => $group2->id, 'userid' => $user2su->id));
        $this->getDataGenerator()->create_group_member(array('groupid' => $group3->id, 'userid' => $user31->id));
        $this->getDataGenerator()->create_group_member(array('groupid' => $group3->id, 'userid' => $user32->id));
        $this->getDataGenerator()->create_group_member(array('groupid' => $group1->id, 'userid' => $userall->id));
        $this->getDataGenerator()->create_group_member(array('groupid' => $group2->id, 'userid' => $userall->id));
        $this->getDataGenerator()->create_group_member(array('groupid' => $group3->id, 'userid' => $userall->id));

                $roleid = $this->getDataGenerator()->create_role();
                if (!empty($settings['allowedcaps'])) {
            foreach ($settings['allowedcaps'] as $capability) {
                assign_capability($capability, CAP_ALLOW, $roleid, $coursecontext);
            }
        }

                foreach ($results as $user => $expectations) {
                        $canview = array();
            $exception = null;
                        if (isset($expectations['canview'])) {
                foreach ($expectations['canview'] as $canviewuser) {
                    $canview[] = $createdusers[${$canviewuser}->id];
                }
            } else if (isset($expectations['exception'])) {
                $exception = $expectations['exception'];
                $this->setExpectedException($exception['type'], $exception['message']);
            } else {
                                $this->markTestIncomplete('Incomplete, only canview and exception are supported');
            }
                        $this->setUser(${$user});
            role_assign($roleid, $USER->id, $coursecontext);

                        $groupid = 0;
            if (isset($settings['groupid'])) {
                $groupid = ${$settings['groupid']}->id;
            }

                        $options = array(
                array('name' => 'withcapability', 'value' => $settings['withcapability']),
                array('name' => 'groupid', 'value' => $groupid),
                array('name' => 'onlyactive', 'value' => $settings['onlyactive']),
                array('name' => 'userfields', 'value' => 'id')
            );
            $enrolledusers = core_enrol_external::get_enrolled_users($course->id, $options);

                        $enrolledusers = external_api::clean_returnvalue(core_enrol_external::get_enrolled_users_returns(), $enrolledusers);

                        $viewed = array();
                        foreach ($enrolledusers as $enrolleduser) {
                $viewed[] = $createdusers[$enrolleduser['id']];
            }
                        $this->assertEquals($canview, $viewed, "Problem checking visible users for '{$createdusers[$USER->id]}'", 0, 1, true);
        }
    }

    
    public function test_get_users_courses() {
        global $USER;

        $this->resetAfterTest(true);

        $coursedata1 = array(
            'fullname'         => '<b>Course 1</b>',                            'shortname'         => '<b>Course 1</b>',                           'summary'          => 'Lightwork Course 1 description',
            'summaryformat'    => FORMAT_MOODLE,
            'lang'             => 'en',
            'enablecompletion' => true,
            'showgrades'       => true
        );

        $course1 = self::getDataGenerator()->create_course($coursedata1);
        $course2 = self::getDataGenerator()->create_course();
        $courses = array($course1, $course2);

                        $roleid = null;
        $contexts = array();
        foreach ($courses as $course) {
            $contexts[$course->id] = context_course::instance($course->id);
            $roleid = $this->assignUserCapability('moodle/course:viewparticipants',
                    $contexts[$course->id]->id, $roleid);

            $this->getDataGenerator()->enrol_user($USER->id, $course->id, $roleid, 'manual');
        }

                $enrolledincourses = core_enrol_external::get_users_courses($USER->id);

                $enrolledincourses = external_api::clean_returnvalue(core_enrol_external::get_users_courses_returns(), $enrolledincourses);

                $this->assertEquals(2, count($enrolledincourses));

                list($course1->summary, $course1->summaryformat) =
             external_format_text($course1->summary, $course1->summaryformat, $contexts[$course1->id]->id, 'course', 'summary', 0);

                        $course1->fullname = external_format_string($course1->fullname, $contexts[$course1->id]->id);
        $course1->shortname = external_format_string($course1->shortname, $contexts[$course1->id]->id);
        foreach ($enrolledincourses as $courseenrol) {
            if ($courseenrol['id'] == $course1->id) {
                foreach ($coursedata1 as $fieldname => $value) {
                    $this->assertEquals($courseenrol[$fieldname], $course1->$fieldname);
                }
            }
        }
    }

    
    public function test_get_course_enrolment_methods() {
        global $DB;

        $this->resetAfterTest(true);

                $selfplugin = enrol_get_plugin('self');
        $this->assertNotEmpty($selfplugin);
        $manualplugin = enrol_get_plugin('manual');
        $this->assertNotEmpty($manualplugin);

        $studentrole = $DB->get_record('role', array('shortname'=>'student'));
        $this->assertNotEmpty($studentrole);

        $course1 = self::getDataGenerator()->create_course();
        $coursedata = new stdClass();
        $coursedata->visible = 0;
        $course2 = self::getDataGenerator()->create_course($coursedata);

                $instanceid1 = $selfplugin->add_instance($course1, array('status' => ENROL_INSTANCE_ENABLED,
                                                                'name' => 'Test instance 1',
                                                                'customint6' => 1,
                                                                'roleid' => $studentrole->id));
        $instanceid2 = $selfplugin->add_instance($course1, array('status' => ENROL_INSTANCE_DISABLED,
                                                                'name' => 'Test instance 2',
                                                                'roleid' => $studentrole->id));

        $instanceid3 = $manualplugin->add_instance($course1, array('status' => ENROL_INSTANCE_ENABLED,
                                                                'name' => 'Test instance 3'));

        $enrolmentmethods = $DB->get_records('enrol', array('courseid' => $course1->id, 'status' => ENROL_INSTANCE_ENABLED));
        $this->assertCount(2, $enrolmentmethods);

        $this->setAdminUser();

                $enrolmentmethods = core_enrol_external::get_course_enrolment_methods($course1->id);
        $enrolmentmethods = external_api::clean_returnvalue(core_enrol_external::get_course_enrolment_methods_returns(),
                                                            $enrolmentmethods);
                        $this->assertCount(1, $enrolmentmethods);

        $enrolmentmethod = $enrolmentmethods[0];
        $this->assertEquals($course1->id, $enrolmentmethod['courseid']);
        $this->assertEquals('self', $enrolmentmethod['type']);
        $this->assertTrue($enrolmentmethod['status']);
        $this->assertFalse(isset($enrolmentmethod['wsfunction']));

        $instanceid4 = $selfplugin->add_instance($course2, array('status' => ENROL_INSTANCE_ENABLED,
                                                                'name' => 'Test instance 4',
                                                                'roleid' => $studentrole->id,
                                                                'customint6' => 1,
                                                                'password' => 'test'));
        $enrolmentmethods = core_enrol_external::get_course_enrolment_methods($course2->id);
        $enrolmentmethods = external_api::clean_returnvalue(core_enrol_external::get_course_enrolment_methods_returns(),
                                                            $enrolmentmethods);
        $this->assertCount(1, $enrolmentmethods);

        $enrolmentmethod = $enrolmentmethods[0];
        $this->assertEquals($course2->id, $enrolmentmethod['courseid']);
        $this->assertEquals('self', $enrolmentmethod['type']);
        $this->assertTrue($enrolmentmethod['status']);
        $this->assertEquals('enrol_self_get_instance_info', $enrolmentmethod['wsfunction']);

                $user = self::getDataGenerator()->create_user();
        $this->setUser($user);
        try {
            core_enrol_external::get_course_enrolment_methods($course2->id);
        } catch (moodle_exception $e) {
            $this->assertEquals('coursehidden', $e->errorcode);
        }
    }

    public function get_enrolled_users_setup($capability) {
        global $USER;

        $this->resetAfterTest(true);

        $return = new stdClass();

        $return->course = self::getDataGenerator()->create_course();
        $return->user1 = self::getDataGenerator()->create_user();
        $return->user2 = self::getDataGenerator()->create_user();
        $return->user3 = self::getDataGenerator()->create_user();
        $this->setUser($return->user3);

                $return->context = context_course::instance($return->course->id);
        $return->roleid = $this->assignUserCapability($capability, $return->context->id);
        $this->assignUserCapability('moodle/user:viewdetails', $return->context->id, $return->roleid);

                $this->getDataGenerator()->enrol_user($return->user1->id, $return->course->id, $return->roleid, 'manual');
        $this->getDataGenerator()->enrol_user($return->user2->id, $return->course->id, $return->roleid, 'manual');
        $this->getDataGenerator()->enrol_user($return->user3->id, $return->course->id, $return->roleid, 'manual');

        return $return;
    }

    
    public function test_get_enrolled_users_without_parameters() {
        $capability = 'moodle/course:viewparticipants';
        $data = $this->get_enrolled_users_setup($capability);

                $enrolledusers = core_enrol_external::get_enrolled_users($data->course->id);

                $enrolledusers = external_api::clean_returnvalue(core_enrol_external::get_enrolled_users_returns(), $enrolledusers);

                $this->assertEquals(3, count($enrolledusers));
        $this->assertArrayHasKey('email', $enrolledusers[0]);
    }

    
    public function test_get_enrolled_users_with_parameters() {
        $capability = 'moodle/course:viewparticipants';
        $data = $this->get_enrolled_users_setup($capability);

                $enrolledusers = core_enrol_external::get_enrolled_users($data->course->id, array(
            array('name' => 'limitfrom', 'value' => 2),
            array('name' => 'limitnumber', 'value' => 1),
            array('name' => 'userfields', 'value' => 'id')
        ));

                $enrolledusers = external_api::clean_returnvalue(core_enrol_external::get_enrolled_users_returns(), $enrolledusers);

                $this->assertCount(1, $enrolledusers);
        $this->assertEquals($data->user3->id, $enrolledusers[0]['id']);
        $this->assertArrayHasKey('id', $enrolledusers[0]);
        $this->assertArrayNotHasKey('email', $enrolledusers[0]);
    }

    
    public function test_get_enrolled_users_without_capability() {
        $capability = 'moodle/course:viewparticipants';
        $data = $this->get_enrolled_users_setup($capability);

                $this->unassignUserCapability($capability, $data->context->id, $data->roleid);
        $this->setExpectedException('moodle_exception');
        $categories = core_enrol_external::get_enrolled_users($data->course->id);
    }

    public function get_enrolled_users_with_capability_setup($capability) {
        global $USER, $DB;

        $this->resetAfterTest(true);

        $return = new stdClass();

                $return->course = self::getDataGenerator()->create_course();
        $context = context_course::instance($return->course->id);

                $return->teacher = self::getDataGenerator()->create_user();
        $return->student1 = self::getDataGenerator()->create_user();
        $return->student2 = self::getDataGenerator()->create_user();

                $fakestudentroleid = create_role('Fake student role', 'fakestudent', 'Fake student role', 'student');
        assign_capability($capability, CAP_PROHIBIT, $fakestudentroleid, $context->id);

                                        $editingteacherroleid = $DB->get_field('role', 'id', array('shortname' => 'editingteacher'));
        $studentroleid = $DB->get_field('role', 'id', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($return->teacher->id, $return->course->id, $editingteacherroleid);
        $this->getDataGenerator()->enrol_user($return->student1->id, $return->course->id, $studentroleid);
        $this->getDataGenerator()->enrol_user($return->student2->id, $return->course->id, $fakestudentroleid);

                $this->setUser($return->teacher);

                accesslib_clear_all_caches_for_unit_testing();

        return $return;
    }

    
    public function test_get_enrolled_users_with_capability_without_parameters() {
        $capability = 'moodle/course:viewparticipants';
        $data = $this->get_enrolled_users_with_capability_setup($capability);

        $result = core_enrol_external::get_enrolled_users_with_capability(
            array(
                'coursecapabilities' => array(
                    'courseid' => $data->course->id,
                    'capabilities' => array(
                        $capability,
                    ),
                ),
            ),
            array()
        );

                $result = external_api::clean_returnvalue(core_enrol_external::get_enrolled_users_with_capability_returns(), $result);

                $expecteduserlist = $result[0];
        $this->assertEquals($data->course->id, $expecteduserlist['courseid']);
        $this->assertEquals($capability, $expecteduserlist['capability']);
        $this->assertEquals(2, count($expecteduserlist['users']));
    }

    
    public function test_get_enrolled_users_with_capability_with_parameters () {
        $capability = 'moodle/course:viewparticipants';
        $data = $this->get_enrolled_users_with_capability_setup($capability);

        $result = core_enrol_external::get_enrolled_users_with_capability(
            array(
                'coursecapabilities' => array(
                    'courseid' => $data->course->id,
                    'capabilities' => array(
                        $capability,
                    ),
                ),
            ),
            array(
                array('name' => 'limitfrom', 'value' => 1),
                array('name' => 'limitnumber', 'value' => 1),
                array('name' => 'userfields', 'value' => 'id')
            )
        );

                $result = external_api::clean_returnvalue(core_enrol_external::get_enrolled_users_with_capability_returns(), $result);

                $expecteduserlist = $result[0]['users'];
        $expecteduser = reset($expecteduserlist);
        $this->assertEquals(1, count($expecteduserlist));
        $this->assertEquals($data->student1->id, $expecteduser['id']);
    }

}
