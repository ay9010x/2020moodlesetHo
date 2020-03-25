<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

class enrol_meta_plugin_testcase extends advanced_testcase {

    protected function enable_plugin() {
        $enabled = enrol_get_plugins(true);
        $enabled['meta'] = true;
        $enabled = array_keys($enabled);
        set_config('enrol_plugins_enabled', implode(',', $enabled));
    }

    protected function disable_plugin() {
        $enabled = enrol_get_plugins(true);
        unset($enabled['meta']);
        $enabled = array_keys($enabled);
        set_config('enrol_plugins_enabled', implode(',', $enabled));
    }

    protected function is_meta_enrolled($user, $enrol, $role = null) {
        global $DB;

        if (!$DB->record_exists('user_enrolments', array('enrolid'=>$enrol->id, 'userid'=>$user->id))) {
            return false;
        }

        if ($role === null) {
            return true;
        }

        return $this->has_role($user, $enrol, $role);
    }

    protected function has_role($user, $enrol, $role) {
        global $DB;

        $context = context_course::instance($enrol->courseid);

        if ($role === false) {
            if ($DB->record_exists('role_assignments', array('contextid'=>$context->id, 'userid'=>$user->id, 'component'=>'enrol_meta', 'itemid'=>$enrol->id))) {
                return false;
            }
        } else if (!$DB->record_exists('role_assignments', array('contextid'=>$context->id, 'userid'=>$user->id, 'roleid'=>$role->id, 'component'=>'enrol_meta', 'itemid'=>$enrol->id))) {
            return false;
        }

        return true;
    }

    public function test_sync() {
        global $CFG, $DB;

        $this->resetAfterTest(true);

        $metalplugin = enrol_get_plugin('meta');
        $manplugin = enrol_get_plugin('manual');

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $user4 = $this->getDataGenerator()->create_user();
        $user5 = $this->getDataGenerator()->create_user();

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $course3 = $this->getDataGenerator()->create_course();
        $course4 = $this->getDataGenerator()->create_course();
        $manual1 = $DB->get_record('enrol', array('courseid'=>$course1->id, 'enrol'=>'manual'), '*', MUST_EXIST);
        $manual2 = $DB->get_record('enrol', array('courseid'=>$course2->id, 'enrol'=>'manual'), '*', MUST_EXIST);
        $manual3 = $DB->get_record('enrol', array('courseid'=>$course3->id, 'enrol'=>'manual'), '*', MUST_EXIST);
        $manual4 = $DB->get_record('enrol', array('courseid'=>$course4->id, 'enrol'=>'manual'), '*', MUST_EXIST);

        $student = $DB->get_record('role', array('shortname'=>'student'));
        $teacher = $DB->get_record('role', array('shortname'=>'teacher'));
        $manager = $DB->get_record('role', array('shortname'=>'manager'));

        $this->disable_plugin();

        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, $student->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course1->id, $student->id);
        $this->getDataGenerator()->enrol_user($user3->id, $course1->id, 0);
        $this->getDataGenerator()->enrol_user($user4->id, $course1->id, $teacher->id);
        $this->getDataGenerator()->enrol_user($user5->id, $course1->id, $manager->id);

        $this->getDataGenerator()->enrol_user($user1->id, $course2->id, $student->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course2->id, $teacher->id);

        $this->assertEquals(7, $DB->count_records('user_enrolments'));
        $this->assertEquals(6, $DB->count_records('role_assignments'));

        set_config('syncall', 0, 'enrol_meta');
        set_config('nosyncroleids', $manager->id, 'enrol_meta');

        require_once($CFG->dirroot.'/enrol/meta/locallib.php');

        enrol_meta_sync(null, false);
        $this->assertEquals(7, $DB->count_records('user_enrolments'));
        $this->assertEquals(6, $DB->count_records('role_assignments'));

        $this->enable_plugin();
        enrol_meta_sync(null, false);
        $this->assertEquals(7, $DB->count_records('user_enrolments'));
        $this->assertEquals(6, $DB->count_records('role_assignments'));

                $this->disable_plugin();
        $e1 = $metalplugin->add_instance($course3, array('customint1'=>$course1->id));
        $e2 = $metalplugin->add_instance($course3, array('customint1'=>$course2->id));
        $e3 = $metalplugin->add_instance($course4, array('customint1'=>$course2->id));
        $enrol1 = $DB->get_record('enrol', array('id'=>$e1));
        $enrol2 = $DB->get_record('enrol', array('id'=>$e2));
        $enrol3 = $DB->get_record('enrol', array('id'=>$e3));
        $this->enable_plugin();

        enrol_meta_sync($course4->id, false);
        $this->assertEquals(9, $DB->count_records('user_enrolments'));
        $this->assertEquals(8, $DB->count_records('role_assignments'));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol3, $student));
        $this->assertTrue($this->is_meta_enrolled($user2, $enrol3, $teacher));

        enrol_meta_sync(null, false);
        $this->assertEquals(14, $DB->count_records('user_enrolments'));
        $this->assertEquals(13, $DB->count_records('role_assignments'));

        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, $student));
        $this->assertTrue($this->is_meta_enrolled($user2, $enrol1, $student));
        $this->assertFalse($this->is_meta_enrolled($user3, $enrol1));
        $this->assertTrue($this->is_meta_enrolled($user4, $enrol1, $teacher));
        $this->assertFalse($this->is_meta_enrolled($user5, $enrol1));

        $this->assertTrue($this->is_meta_enrolled($user1, $enrol2, $student));
        $this->assertTrue($this->is_meta_enrolled($user2, $enrol2, $teacher));

        $this->assertTrue($this->is_meta_enrolled($user1, $enrol3, $student));
        $this->assertTrue($this->is_meta_enrolled($user2, $enrol3, $teacher));

        set_config('syncall', 1, 'enrol_meta');
        enrol_meta_sync(null, false);
        $this->assertEquals(16, $DB->count_records('user_enrolments'));
        $this->assertEquals(13, $DB->count_records('role_assignments'));

        $this->assertTrue($this->is_meta_enrolled($user3, $enrol1, false));
        $this->assertTrue($this->is_meta_enrolled($user5, $enrol1, false));

        $this->assertEquals(16, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->disable_plugin();
        $manplugin->unenrol_user($manual1, $user1->id);
        $manplugin->unenrol_user($manual2, $user1->id);

        $this->assertEquals(14, $DB->count_records('user_enrolments'));
        $this->assertEquals(11, $DB->count_records('role_assignments'));
        $this->assertEquals(14, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));

        $this->enable_plugin();

        set_config('unenrolaction', ENROL_EXT_REMOVED_SUSPEND, 'enrol_meta');
        enrol_meta_sync($course4->id, false);
        $this->assertEquals(14, $DB->count_records('user_enrolments'));
        $this->assertEquals(11, $DB->count_records('role_assignments'));
        $this->assertEquals(13, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol3, $student));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$enrol3->id, 'status'=>ENROL_USER_SUSPENDED, 'userid'=>$user1->id)));

        enrol_meta_sync(null, false);
        $this->assertEquals(14, $DB->count_records('user_enrolments'));
        $this->assertEquals(11, $DB->count_records('role_assignments'));
        $this->assertEquals(11, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, $student));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$enrol1->id, 'status'=>ENROL_USER_SUSPENDED, 'userid'=>$user1->id)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol2, $student));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$enrol2->id, 'status'=>ENROL_USER_SUSPENDED, 'userid'=>$user1->id)));

        set_config('unenrolaction', ENROL_EXT_REMOVED_SUSPENDNOROLES, 'enrol_meta');
        enrol_meta_sync($course4->id, false);
        $this->assertEquals(14, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));
        $this->assertEquals(11, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol3, false));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$enrol3->id, 'status'=>ENROL_USER_SUSPENDED, 'userid'=>$user1->id)));

        enrol_meta_sync(null, false);
        $this->assertEquals(14, $DB->count_records('user_enrolments'));
        $this->assertEquals(8, $DB->count_records('role_assignments'));
        $this->assertEquals(11, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, false));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$enrol1->id, 'status'=>ENROL_USER_SUSPENDED, 'userid'=>$user1->id)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol2, false));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$enrol2->id, 'status'=>ENROL_USER_SUSPENDED, 'userid'=>$user1->id)));

        set_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL, 'enrol_meta');
        enrol_meta_sync($course4->id, false);
        $this->assertEquals(13, $DB->count_records('user_enrolments'));
        $this->assertEquals(8, $DB->count_records('role_assignments'));
        $this->assertEquals(11, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertFalse($this->is_meta_enrolled($user1, $enrol3));

        enrol_meta_sync(null, false);
        $this->assertEquals(11, $DB->count_records('user_enrolments'));
        $this->assertEquals(8, $DB->count_records('role_assignments'));
        $this->assertEquals(11, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertFalse($this->is_meta_enrolled($user1, $enrol1));
        $this->assertFalse($this->is_meta_enrolled($user1, $enrol2));


        
        set_config('syncall', 1, 'enrol_meta');

        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, $student->id);
        $this->assertEquals(13, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));
        $this->assertEquals(13, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, $student));
        enrol_meta_sync(null, false);
        $this->assertEquals(13, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));
        $this->assertEquals(13, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, $student));

        $manplugin->unenrol_user($manual1, $user1->id);
        $this->assertEquals(11, $DB->count_records('user_enrolments'));
        $this->assertEquals(8, $DB->count_records('role_assignments'));
        $this->assertEquals(11, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertFalse($this->is_meta_enrolled($user1, $enrol1));
        enrol_meta_sync(null, false);
        $this->assertEquals(11, $DB->count_records('user_enrolments'));
        $this->assertEquals(8, $DB->count_records('role_assignments'));
        $this->assertEquals(11, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertFalse($this->is_meta_enrolled($user1, $enrol1));

        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, 0);
        $this->assertEquals(13, $DB->count_records('user_enrolments'));
        $this->assertEquals(8, $DB->count_records('role_assignments'));
        $this->assertEquals(13, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, false));
        enrol_meta_sync(null, false);
        $this->assertEquals(13, $DB->count_records('user_enrolments'));
        $this->assertEquals(8, $DB->count_records('role_assignments'));
        $this->assertEquals(13, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, false));

        $manplugin->unenrol_user($manual1, $user1->id);
        $this->assertEquals(11, $DB->count_records('user_enrolments'));
        $this->assertEquals(8, $DB->count_records('role_assignments'));
        $this->assertEquals(11, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertFalse($this->is_meta_enrolled($user1, $enrol1));
        enrol_meta_sync(null, false);
        $this->assertEquals(11, $DB->count_records('user_enrolments'));
        $this->assertEquals(8, $DB->count_records('role_assignments'));
        $this->assertEquals(11, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertFalse($this->is_meta_enrolled($user1, $enrol1));

        set_config('syncall', 0, 'enrol_meta');
        enrol_meta_sync(null, false);
        $this->assertEquals(9, $DB->count_records('user_enrolments'));
        $this->assertEquals(8, $DB->count_records('role_assignments'));
        $this->assertEquals(9, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertFalse($this->is_meta_enrolled($user1, $enrol1));

        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, 0);
        $this->assertEquals(10, $DB->count_records('user_enrolments'));
        $this->assertEquals(8, $DB->count_records('role_assignments'));
        $this->assertEquals(10, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertFalse($this->is_meta_enrolled($user1, $enrol1, $student));
        enrol_meta_sync(null, false);
        $this->assertEquals(10, $DB->count_records('user_enrolments'));
        $this->assertEquals(8, $DB->count_records('role_assignments'));
        $this->assertEquals(10, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertFalse($this->is_meta_enrolled($user1, $enrol1, $student));

        role_assign($teacher->id, $user1->id, context_course::instance($course1->id)->id);
        $this->assertEquals(11, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));
        $this->assertEquals(11, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, $teacher));
        enrol_meta_sync(null, false);
        $this->assertEquals(11, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));
        $this->assertEquals(11, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, $teacher));

        role_unassign($teacher->id, $user1->id, context_course::instance($course1->id)->id);
        $this->assertEquals(10, $DB->count_records('user_enrolments'));
        $this->assertEquals(8, $DB->count_records('role_assignments'));
        $this->assertEquals(10, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertFalse($this->is_meta_enrolled($user1, $enrol1, $student));
        enrol_meta_sync(null, false);
        $this->assertEquals(10, $DB->count_records('user_enrolments'));
        $this->assertEquals(8, $DB->count_records('role_assignments'));
        $this->assertEquals(10, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertFalse($this->is_meta_enrolled($user1, $enrol1, $student));

        $manplugin->unenrol_user($manual1, $user1->id);
        $this->assertEquals(9, $DB->count_records('user_enrolments'));
        $this->assertEquals(8, $DB->count_records('role_assignments'));
        $this->assertEquals(9, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertFalse($this->is_meta_enrolled($user1, $enrol1));

        set_config('syncall', 1, 'enrol_meta');
        set_config('unenrolaction', ENROL_EXT_REMOVED_SUSPEND, 'enrol_meta');
        enrol_meta_sync(null, false);
        $this->assertEquals(11, $DB->count_records('user_enrolments'));
        $this->assertEquals(8, $DB->count_records('role_assignments'));
        $this->assertEquals(11, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));

        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, $student->id);
        $this->assertEquals(13, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));
        $this->assertEquals(13, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, $student));
        enrol_meta_sync(null, false);
        $this->assertEquals(13, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));
        $this->assertEquals(13, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, $student));

        $manplugin->update_user_enrol($manual1, $user1->id, ENROL_USER_SUSPENDED);
        $this->assertEquals(13, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));
        $this->assertEquals(11, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, $student));
        enrol_meta_sync(null, false);
        $this->assertEquals(13, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));
        $this->assertEquals(11, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, $student));

        $manplugin->unenrol_user($manual1, $user1->id);
        $this->assertEquals(12, $DB->count_records('user_enrolments'));
        $this->assertEquals(9, $DB->count_records('role_assignments'));
        $this->assertEquals(11, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, $student));
        enrol_meta_sync(null, false);
        $this->assertEquals(12, $DB->count_records('user_enrolments'));
        $this->assertEquals(9, $DB->count_records('role_assignments'));
        $this->assertEquals(11, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, $student));

        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, $student->id);
        $this->assertEquals(13, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));
        $this->assertEquals(13, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, $student));
        enrol_meta_sync(null, false);
        $this->assertEquals(13, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));
        $this->assertEquals(13, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, $student));

        set_config('syncall', 1, 'enrol_meta');
        set_config('unenrolaction', ENROL_EXT_REMOVED_SUSPENDNOROLES, 'enrol_meta');
        enrol_meta_sync(null, false);
        $this->assertEquals(13, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));
        $this->assertEquals(13, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));

        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, $student->id);
        $this->assertEquals(13, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));
        $this->assertEquals(13, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, $student));
        enrol_meta_sync(null, false);
        $this->assertEquals(13, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));
        $this->assertEquals(13, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, $student));

        $manplugin->unenrol_user($manual1, $user1->id);
        $this->assertEquals(12, $DB->count_records('user_enrolments'));
        $this->assertEquals(8, $DB->count_records('role_assignments'));
        $this->assertEquals(11, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, false));
        enrol_meta_sync(null, false);
        $this->assertEquals(12, $DB->count_records('user_enrolments'));
        $this->assertEquals(8, $DB->count_records('role_assignments'));
        $this->assertEquals(11, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, false));

        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, $student->id);
        $this->assertEquals(13, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));
        $this->assertEquals(13, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, $student));
        enrol_meta_sync(null, false);
        $this->assertEquals(13, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));
        $this->assertEquals(13, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        $this->assertTrue($this->is_meta_enrolled($user1, $enrol1, $student));


        set_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL, 'enrol_meta');
        enrol_meta_sync(null, false);
        $this->assertEquals(13, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));
        $this->assertEquals(13, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));

        delete_course($course1, false);
        $this->assertEquals(3, $DB->count_records('user_enrolments'));
        $this->assertEquals(3, $DB->count_records('role_assignments'));
        $this->assertEquals(3, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        enrol_meta_sync(null, false);
        $this->assertEquals(3, $DB->count_records('user_enrolments'));
        $this->assertEquals(3, $DB->count_records('role_assignments'));
        $this->assertEquals(3, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));

        delete_course($course2, false);
        $this->assertEquals(0, $DB->count_records('user_enrolments'));
        $this->assertEquals(0, $DB->count_records('role_assignments'));
        $this->assertEquals(0, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));
        enrol_meta_sync(null, false);
        $this->assertEquals(0, $DB->count_records('user_enrolments'));
        $this->assertEquals(0, $DB->count_records('role_assignments'));
        $this->assertEquals(0, $DB->count_records('user_enrolments', array('status'=>ENROL_USER_ACTIVE)));

        delete_course($course3, false);
        delete_course($course4, false);

    }

    public function test_add_to_group() {
        global $CFG, $DB;

        require_once($CFG->dirroot.'/group/lib.php');

        $this->resetAfterTest(true);

        $metalplugin = enrol_get_plugin('meta');

        $user1 = $this->getDataGenerator()->create_user();
        $user4 = $this->getDataGenerator()->create_user();

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $course3 = $this->getDataGenerator()->create_course();
        $manualenrol1 = $DB->get_record('enrol', array('courseid' => $course1->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $manualenrol2 = $DB->get_record('enrol', array('courseid' => $course2->id, 'enrol' => 'manual'), '*', MUST_EXIST);

        $student = $DB->get_record('role', array('shortname' => 'student'));
        $teacher = $DB->get_record('role', array('shortname' => 'teacher'));

        $id = groups_create_group((object)array('name' => 'Group 1 in course 3', 'courseid' => $course3->id));
        $group31 = $DB->get_record('groups', array('id' => $id), '*', MUST_EXIST);
        $id = groups_create_group((object)array('name' => 'Group 2 in course 4', 'courseid' => $course3->id));
        $group32 = $DB->get_record('groups', array('id' => $id), '*', MUST_EXIST);

        $this->enable_plugin();

        $e1 = $metalplugin->add_instance($course3, array('customint1' => $course1->id, 'customint2' => $group31->id));
        $e2 = $metalplugin->add_instance($course3, array('customint1' => $course2->id, 'customint2' => $group32->id));

        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, $student->id);
        $this->getDataGenerator()->enrol_user($user4->id, $course1->id, $teacher->id);

        $this->getDataGenerator()->enrol_user($user1->id, $course2->id, $student->id);

                $this->assertTrue(groups_is_member($group31->id, $user1->id));
        $this->assertTrue($DB->record_exists('groups_members', array('groupid' => $group31->id, 'userid' => $user1->id,
            'component' => 'enrol_meta', 'itemid' => $e1)));
        $this->assertTrue(groups_is_member($group32->id, $user1->id));
        $this->assertTrue($DB->record_exists('groups_members', array('groupid' => $group32->id, 'userid' => $user1->id,
            'component' => 'enrol_meta', 'itemid' => $e2)));

        $this->assertTrue(groups_is_member($group31->id, $user4->id));
        $this->assertTrue($DB->record_exists('groups_members', array('groupid' => $group31->id, 'userid' => $user4->id,
            'component' => 'enrol_meta', 'itemid' => $e1)));

                enrol_meta_sync(null, false);
        $this->assertTrue(groups_is_member($group31->id, $user1->id));
        $this->assertTrue($DB->record_exists('groups_members', array('groupid' => $group31->id, 'userid' => $user1->id,
            'component' => 'enrol_meta', 'itemid' => $e1)));
        $this->assertTrue(groups_is_member($group32->id, $user1->id));
        $this->assertTrue($DB->record_exists('groups_members', array('groupid' => $group32->id, 'userid' => $user1->id,
            'component' => 'enrol_meta', 'itemid' => $e2)));

        $this->assertTrue(groups_is_member($group31->id, $user4->id));
        $this->assertTrue($DB->record_exists('groups_members', array('groupid' => $group31->id, 'userid' => $user4->id,
            'component' => 'enrol_meta', 'itemid' => $e1)));

        set_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL, 'enrol_meta');

                enrol_get_plugin('manual')->unenrol_user($manualenrol1, $user1->id);
        $this->assertFalse(groups_is_member($group31->id, $user1->id));
        $this->assertTrue(groups_is_member($group32->id, $user1->id));
        $this->assertTrue(is_enrolled(context_course::instance($course3->id), $user1, '', true));                 enrol_meta_sync(null, false);
        $this->assertFalse(groups_is_member($group31->id, $user1->id));
        $this->assertTrue(groups_is_member($group32->id, $user1->id));
        $this->assertTrue(is_enrolled(context_course::instance($course3->id), $user1, '', true));

                enrol_get_plugin('manual')->unenrol_user($manualenrol2, $user1->id);
        $this->assertFalse(groups_is_member($group32->id, $user1->id));
        $this->assertFalse(is_enrolled(context_course::instance($course3->id), $user1));

        set_config('unenrolaction', ENROL_EXT_REMOVED_SUSPENDNOROLES, 'enrol_meta');

                enrol_get_plugin('manual')->unenrol_user($manualenrol1, $user4->id);
        $this->assertTrue(groups_is_member($group31->id, $user4->id));
        $this->assertTrue(is_enrolled(context_course::instance($course3->id), $user4));
        $this->assertFalse(is_enrolled(context_course::instance($course3->id), $user4, '', true));
        enrol_meta_sync(null, false);
        $this->assertTrue(groups_is_member($group31->id, $user4->id));
        $this->assertTrue(is_enrolled(context_course::instance($course3->id), $user4));
        $this->assertFalse(is_enrolled(context_course::instance($course3->id), $user4, '', true));
    }

    
    public function test_add_to_group_with_member() {
        global $CFG, $DB;

        require_once($CFG->dirroot.'/group/lib.php');

        $this->resetAfterTest(true);

        $metalplugin = enrol_get_plugin('meta');

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $manualenrol1 = $DB->get_record('enrol', array('courseid' => $course1->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $manualenrol2 = $DB->get_record('enrol', array('courseid' => $course2->id, 'enrol' => 'manual'), '*', MUST_EXIST);

        $student = $DB->get_record('role', array('shortname' => 'student'));

        $groupid = groups_create_group((object)array('name' => 'Grp', 'courseid' => $course2->id));

        $this->enable_plugin();
        set_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL, 'enrol_meta');

                        enrol_get_plugin('manual')->enrol_user($manualenrol2, $user1->id, $student->id);
        groups_add_member($groupid, $user1->id);
        enrol_get_plugin('manual')->enrol_user($manualenrol2, $user2->id, $student->id);
        $this->assertTrue(groups_is_member($groupid, $user1->id));
        $this->assertFalse(groups_is_member($groupid, $user2->id));

                $metalplugin->add_instance($course2, array('customint1' => $course1->id, 'customint2' => $groupid));

        enrol_get_plugin('manual')->enrol_user($manualenrol1, $user1->id, $student->id);
        enrol_get_plugin('manual')->enrol_user($manualenrol1, $user2->id, $student->id);

                $this->assertTrue(groups_is_member($groupid, $user1->id));
        $this->assertTrue(groups_is_member($groupid, $user2->id));

                enrol_get_plugin('manual')->unenrol_user($manualenrol1, $user1->id);
        enrol_get_plugin('manual')->unenrol_user($manualenrol1, $user2->id);

                $this->assertTrue(groups_is_member($groupid, $user1->id));
        $this->assertFalse(groups_is_member($groupid, $user2->id));

                enrol_meta_sync();

        $this->assertTrue(groups_is_member($groupid, $user1->id));
        $this->assertFalse(groups_is_member($groupid, $user2->id));

    }

    
    public function test_user_enrolment_created_event() {
        global $DB;

        $this->resetAfterTest();

        $metaplugin = enrol_get_plugin('meta');
        $user1 = $this->getDataGenerator()->create_user();
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $student = $DB->get_record('role', array('shortname' => 'student'));

        $e1 = $metaplugin->add_instance($course2, array('customint1' => $course1->id));
        $enrol1 = $DB->get_record('enrol', array('id' => $e1));

                $sink = $this->redirectEvents();

        $metaplugin->enrol_user($enrol1, $user1->id, $student->id);
        $events = $sink->get_events();
        $sink->close();
        $event = array_shift($events);

                $dbuserenrolled = $DB->get_record('user_enrolments', array('userid' => $user1->id));
        $this->assertInstanceOf('\core\event\user_enrolment_created', $event);
        $this->assertEquals($dbuserenrolled->id, $event->objectid);
        $this->assertEquals('user_enrolled', $event->get_legacy_eventname());
        $expectedlegacyeventdata = $dbuserenrolled;
        $expectedlegacyeventdata->enrol = 'meta';
        $expectedlegacyeventdata->courseid = $course2->id;
        $this->assertEventLegacyData($expectedlegacyeventdata, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_user_enrolment_deleted_event() {
        global $DB;

        $this->resetAfterTest(true);

        $metalplugin = enrol_get_plugin('meta');
        $user1 = $this->getDataGenerator()->create_user();
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $student = $DB->get_record('role', array('shortname'=>'student'));

        $e1 = $metalplugin->add_instance($course2, array('customint1' => $course1->id));
        $enrol1 = $DB->get_record('enrol', array('id' => $e1));

                $metalplugin->enrol_user($enrol1, $user1->id, $student->id);
        $this->assertEquals(1, $DB->count_records('user_enrolments'));

                $sink = $this->redirectEvents();
        $metalplugin->unenrol_user($enrol1, $user1->id);
        $events = $sink->get_events();
        $sink->close();
        $event = array_pop($events);

        $this->assertEquals(0, $DB->count_records('user_enrolments'));
        $this->assertInstanceOf('\core\event\user_enrolment_deleted', $event);
        $this->assertEquals('user_unenrolled', $event->get_legacy_eventname());
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_user_enrolment_updated_event() {
        global $DB;

        $this->resetAfterTest(true);

        $metalplugin = enrol_get_plugin('meta');
        $user1 = $this->getDataGenerator()->create_user();
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $student = $DB->get_record('role', array('shortname'=>'student'));

        $e1 = $metalplugin->add_instance($course2, array('customint1' => $course1->id));
        $enrol1 = $DB->get_record('enrol', array('id' => $e1));

                $metalplugin->enrol_user($enrol1, $user1->id, $student->id);
        $this->assertEquals(1, $DB->count_records('user_enrolments'));

                $sink = $this->redirectEvents();
        $metalplugin->update_user_enrol($enrol1, $user1->id, ENROL_USER_SUSPENDED, null, time());
        $events = $sink->get_events();
        $sink->close();
        $event = array_shift($events);

                $dbuserenrolled = $DB->get_record('user_enrolments', array('userid' => $user1->id));
        $this->assertInstanceOf('\core\event\user_enrolment_updated', $event);
        $this->assertEquals($dbuserenrolled->id, $event->objectid);
        $this->assertEquals('user_enrol_modified', $event->get_legacy_eventname());
        $expectedlegacyeventdata = $dbuserenrolled;
        $expectedlegacyeventdata->enrol = 'meta';
        $expectedlegacyeventdata->courseid = $course2->id;
        $url = new \moodle_url('/enrol/editenrolment.php', array('ue' => $event->objectid));
        $this->assertEquals($url, $event->get_url());
        $this->assertEventLegacyData($expectedlegacyeventdata, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_enrol_meta_create_new_group() {
        global $DB;
        $this->resetAfterTest();
                $course = $this->getDataGenerator()->create_course(array('fullname' => 'Mathematics'));
        $course2 = $this->getDataGenerator()->create_course(array('fullname' => 'Physics'));
        $metacourse = $this->getDataGenerator()->create_course(array('fullname' => 'All sciences'));
                $groupid = enrol_meta_create_new_group($metacourse->id, $course->id);
                $group = $DB->get_record('groups', array('id' => $groupid));
                $this->assertEquals('Mathematics course', $group->name);
                $this->assertEquals($metacourse->id, $group->courseid);

                $groupdata = new stdClass();
        $groupdata->courseid = $metacourse->id;
        $groupdata->name = 'Physics course';
        groups_create_group($groupdata);
                $groupid = enrol_meta_create_new_group($metacourse->id, $course2->id);
        $groupinfo = $DB->get_record('groups', array('id' => $groupid));
                $this->assertEquals('Physics course (2)', $groupinfo->name);

                $groupid = enrol_meta_create_new_group($metacourse->id, $course2->id);
        $groupinfo = $DB->get_record('groups', array('id' => $groupid));
                $this->assertEquals('Physics course (3)', $groupinfo->name);
    }

    
    public function test_timeend() {
        global $CFG, $DB;

        $this->resetAfterTest(true);

        $timeinfuture = time() + DAYSECS;
        $timeinpast = time() - DAYSECS;

        $metalplugin = enrol_get_plugin('meta');
        $manplugin = enrol_get_plugin('manual');

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $user4 = $this->getDataGenerator()->create_user();
        $user5 = $this->getDataGenerator()->create_user();

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $course3 = $this->getDataGenerator()->create_course();
        $manual1 = $DB->get_record('enrol', array('courseid' => $course1->id, 'enrol' => 'manual'), '*', MUST_EXIST);

        $student = $DB->get_record('role', array('shortname' => 'student'));

        $this->enable_plugin();

                $meta2id = $metalplugin->add_instance($course2, array('customint1' => $course1->id));

        $expectedenrolments = array(
            $user1->id => array(0, 0, ENROL_USER_ACTIVE),
            $user2->id => array($timeinpast, 0, ENROL_USER_ACTIVE),
            $user3->id => array(0, $timeinfuture, ENROL_USER_ACTIVE),
            $user4->id => array($timeinpast, $timeinfuture, ENROL_USER_ACTIVE),
            $user5->id => array(0, 0, ENROL_USER_SUSPENDED),
        );
        foreach ($expectedenrolments as $userid => $data) {
            $expectedenrolments[$userid] = (object)(array('userid' => $userid) +
                    array_combine(array('timestart', 'timeend', 'status'), $data));
        }

                foreach ($expectedenrolments as $e) {
            $manplugin->enrol_user($manual1, $e->userid, $student->id, $e->timestart, $e->timeend, $e->status);
        }

        $enrolments = $DB->get_records('user_enrolments', array('enrolid' => $manual1->id), 'userid', 'userid, timestart, timeend, status');
        $this->assertEquals($expectedenrolments, $enrolments);

                $enrolments = $DB->get_records('user_enrolments', array('enrolid' => $meta2id), '', 'userid, timestart, timeend, status');
        $this->assertEquals($expectedenrolments, $enrolments);

                $meta3id = $metalplugin->add_instance($course3, array('customint1' => $course1->id));
        enrol_meta_sync($course3->id);

                $enrolments = $DB->get_records('user_enrolments', array('enrolid' => $meta3id), '', 'userid, timestart, timeend, status');
        $this->assertEquals($expectedenrolments, $enrolments);

                $expectedenrolments[$user2->id]->timestart = $timeinpast - 60;
        $expectedenrolments[$user3->id]->timeend = $timeinfuture + 60;
        $expectedenrolments[$user4->id]->status = ENROL_USER_SUSPENDED;
        $expectedenrolments[$user5->id]->status = ENROL_USER_ACTIVE;
        foreach ($expectedenrolments as $e) {
            $manplugin->update_user_enrol($manual1, $e->userid, $e->status, $e->timestart, $e->timeend);
        }

                $enrolments = $DB->get_records('user_enrolments', array('enrolid' => $meta2id), '', 'userid, timestart, timeend, status');
        $this->assertEquals($expectedenrolments, $enrolments);
        $enrolments = $DB->get_records('user_enrolments', array('enrolid' => $meta3id), '', 'userid, timestart, timeend, status');
        $this->assertEquals($expectedenrolments, $enrolments);

                $sink = $this->redirectEvents();
        $expectedenrolments[$user2->id]->timestart = $timeinpast;
        $expectedenrolments[$user3->id]->timeend = $timeinfuture;
        $expectedenrolments[$user4->id]->status = ENROL_USER_ACTIVE;
        $expectedenrolments[$user5->id]->status = ENROL_USER_SUSPENDED;
        foreach ($expectedenrolments as $e) {
            $manplugin->update_user_enrol($manual1, $e->userid, $e->status, $e->timestart, $e->timeend);
        }

                enrol_meta_sync($course3->id);

        $enrolments = $DB->get_records('user_enrolments', array('enrolid' => $meta2id), '', 'userid, timestart, timeend, status');
        $this->assertNotEquals($expectedenrolments, $enrolments);

        $enrolments = $DB->get_records('user_enrolments', array('enrolid' => $meta3id), '', 'userid, timestart, timeend, status');
        $this->assertEquals($expectedenrolments, $enrolments);

        $sink->close();

                $manplugin->update_status($manual1, ENROL_INSTANCE_DISABLED);
        $allsuspendedenrolemnts = array_combine(array_keys($expectedenrolments), array_fill(0, 5, ENROL_USER_SUSPENDED));
        $enrolmentstatuses = $DB->get_records_menu('user_enrolments', array('enrolid' => $meta2id), '', 'userid, status');
        $this->assertEquals($allsuspendedenrolemnts, $enrolmentstatuses);

        $manplugin->update_status($manual1, ENROL_INSTANCE_ENABLED);
        $enrolments = $DB->get_records('user_enrolments', array('enrolid' => $meta2id), '', 'userid, timestart, timeend, status');
        $this->assertEquals($expectedenrolments, $enrolments);

                $sink = $this->redirectEvents();
        $manplugin->update_status($manual1, ENROL_INSTANCE_DISABLED);
        enrol_meta_sync($course3->id);
        $enrolmentstatuses = $DB->get_records_menu('user_enrolments', array('enrolid' => $meta3id), '', 'userid, status');
        $this->assertEquals($allsuspendedenrolemnts, $enrolmentstatuses);

        $manplugin->update_status($manual1, ENROL_INSTANCE_ENABLED);
        enrol_meta_sync($course3->id);
        $enrolments = $DB->get_records('user_enrolments', array('enrolid' => $meta3id), '', 'userid, timestart, timeend, status');
        $this->assertEquals($expectedenrolments, $enrolments);
        $sink->close();
    }
}
