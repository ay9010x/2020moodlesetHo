<?php



defined('MOODLE_INTERNAL') || die();


class enrol_paypal_testcase extends advanced_testcase {

    protected function enable_plugin() {
        $enabled = enrol_get_plugins(true);
        $enabled['paypal'] = true;
        $enabled = array_keys($enabled);
        set_config('enrol_plugins_enabled', implode(',', $enabled));
    }

    protected function disable_plugin() {
        $enabled = enrol_get_plugins(true);
        unset($enabled['paypal']);
        $enabled = array_keys($enabled);
        set_config('enrol_plugins_enabled', implode(',', $enabled));
    }

    public function test_basics() {
        $this->assertFalse(enrol_is_enabled('paypal'));
        $plugin = enrol_get_plugin('paypal');
        $this->assertInstanceOf('enrol_paypal_plugin', $plugin);
        $this->assertEquals(ENROL_EXT_REMOVED_SUSPENDNOROLES, get_config('enrol_paypal', 'expiredaction'));
    }

    public function test_sync_nothing() {
        $this->resetAfterTest();

        $this->enable_plugin();
        $paypalplugin = enrol_get_plugin('paypal');

                $paypalplugin->sync(new null_progress_trace());
    }

    public function test_expired() {
        global $DB;
        $this->resetAfterTest();

        
        $paypalplugin = enrol_get_plugin('paypal');
        
        $manualplugin = enrol_get_plugin('manual');
        $this->assertNotEmpty($manualplugin);

        $now = time();
        $trace = new null_progress_trace();
        $this->enable_plugin();


        
        $studentrole = $DB->get_record('role', array('shortname'=>'student'));
        $this->assertNotEmpty($studentrole);
        $teacherrole = $DB->get_record('role', array('shortname'=>'teacher'));
        $this->assertNotEmpty($teacherrole);
        $managerrole = $DB->get_record('role', array('shortname'=>'manager'));
        $this->assertNotEmpty($managerrole);

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $user4 = $this->getDataGenerator()->create_user();

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $context1 = context_course::instance($course1->id);
        $context2 = context_course::instance($course2->id);

        $data = array('roleid'=>$studentrole->id, 'courseid'=>$course1->id);
        $id = $paypalplugin->add_instance($course1, $data);
        $instance1  = $DB->get_record('enrol', array('id'=>$id));
        $data = array('roleid'=>$studentrole->id, 'courseid'=>$course2->id);
        $id = $paypalplugin->add_instance($course2, $data);
        $instance2 = $DB->get_record('enrol', array('id'=>$id));
        $data = array('roleid'=>$teacherrole->id, 'courseid'=>$course2->id);
        $id = $paypalplugin->add_instance($course2, $data);
        $instance3 = $DB->get_record('enrol', array('id'=>$id));

        $maninstance1 = $DB->get_record('enrol', array('courseid'=>$course2->id, 'enrol'=>'manual'), '*', MUST_EXIST);

        $manualplugin->enrol_user($maninstance1, $user3->id, $studentrole->id);

        $this->assertEquals(1, $DB->count_records('user_enrolments'));
        $this->assertEquals(1, $DB->count_records('role_assignments'));
        $this->assertEquals(1, $DB->count_records('role_assignments', array('roleid'=>$studentrole->id)));

        $paypalplugin->enrol_user($instance1, $user1->id, $studentrole->id);
        $paypalplugin->enrol_user($instance1, $user2->id, $studentrole->id);
        $paypalplugin->enrol_user($instance1, $user3->id, $studentrole->id, 0, $now-60);

        $paypalplugin->enrol_user($instance2, $user1->id, $studentrole->id, 0, 0);
        $paypalplugin->enrol_user($instance2, $user2->id, $studentrole->id, 0, $now-60*60);
        $paypalplugin->enrol_user($instance2, $user3->id, $studentrole->id, 0, $now+60*60);

        $paypalplugin->enrol_user($instance3, $user1->id, $teacherrole->id, $now-60*60*24*7, $now-60);
        $paypalplugin->enrol_user($instance3, $user4->id, $teacherrole->id);

        role_assign($managerrole->id, $user3->id, $context1->id);

        $this->assertEquals(9, $DB->count_records('user_enrolments'));
        $this->assertEquals(9, $DB->count_records('role_assignments'));
        $this->assertEquals(6, $DB->count_records('role_assignments', array('roleid'=>$studentrole->id)));
        $this->assertEquals(2, $DB->count_records('role_assignments', array('roleid'=>$teacherrole->id)));
        $this->assertEquals(1, $DB->count_records('role_assignments', array('roleid'=>$managerrole->id)));

        
        $paypalplugin->set_config('expiredaction', ENROL_EXT_REMOVED_KEEP);
        $code = $paypalplugin->sync($trace);
        $this->assertSame(0, $code);
        $this->assertEquals(9, $DB->count_records('user_enrolments'));
        $this->assertEquals(9, $DB->count_records('role_assignments'));


        $paypalplugin->set_config('expiredaction', ENROL_EXT_REMOVED_SUSPENDNOROLES);
        $paypalplugin->sync($trace);
        $this->assertEquals(9, $DB->count_records('user_enrolments'));
        $this->assertEquals(6, $DB->count_records('role_assignments'));
        $this->assertEquals(4, $DB->count_records('role_assignments', array('roleid'=>$studentrole->id)));
        $this->assertEquals(1, $DB->count_records('role_assignments', array('roleid'=>$teacherrole->id)));
        $this->assertFalse($DB->record_exists('role_assignments', array('contextid'=>$context1->id, 'userid'=>$user3->id, 'roleid'=>$studentrole->id)));
        $this->assertFalse($DB->record_exists('role_assignments', array('contextid'=>$context2->id, 'userid'=>$user2->id, 'roleid'=>$studentrole->id)));
        $this->assertFalse($DB->record_exists('role_assignments', array('contextid'=>$context2->id, 'userid'=>$user1->id, 'roleid'=>$teacherrole->id)));
        $this->assertTrue($DB->record_exists('role_assignments', array('contextid'=>$context2->id, 'userid'=>$user1->id, 'roleid'=>$studentrole->id)));


        $paypalplugin->set_config('expiredaction', ENROL_EXT_REMOVED_UNENROL);
        role_assign($studentrole->id, $user3->id, $context1->id);
        role_assign($studentrole->id, $user2->id, $context2->id);
        role_assign($teacherrole->id, $user1->id, $context2->id);
        $this->assertEquals(9, $DB->count_records('user_enrolments'));
        $this->assertEquals(9, $DB->count_records('role_assignments'));
        $this->assertEquals(6, $DB->count_records('role_assignments', array('roleid'=>$studentrole->id)));
        $this->assertEquals(2, $DB->count_records('role_assignments', array('roleid'=>$teacherrole->id)));
        $paypalplugin->sync($trace);
        $this->assertEquals(6, $DB->count_records('user_enrolments'));
        $this->assertFalse($DB->record_exists('user_enrolments', array('enrolid'=>$instance1->id, 'userid'=>$user3->id)));
        $this->assertFalse($DB->record_exists('user_enrolments', array('enrolid'=>$instance2->id, 'userid'=>$user2->id)));
        $this->assertFalse($DB->record_exists('user_enrolments', array('enrolid'=>$instance3->id, 'userid'=>$user1->id)));
        $this->assertEquals(5, $DB->count_records('role_assignments'));
        $this->assertEquals(4, $DB->count_records('role_assignments', array('roleid'=>$studentrole->id)));
        $this->assertEquals(1, $DB->count_records('role_assignments', array('roleid'=>$teacherrole->id)));
    }
}