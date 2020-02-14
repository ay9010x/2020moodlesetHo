<?php



defined('MOODLE_INTERNAL') || die();

class auth_ldap_plugin_testcase extends advanced_testcase {

    public function test_auth_ldap() {
        global $CFG, $DB;

        if (!extension_loaded('ldap')) {
            $this->markTestSkipped('LDAP extension is not loaded.');
        }

        $this->resetAfterTest();

        require_once($CFG->dirroot.'/auth/ldap/auth.php');
        require_once($CFG->libdir.'/ldaplib.php');

        if (!defined('TEST_AUTH_LDAP_HOST_URL') or !defined('TEST_AUTH_LDAP_BIND_DN') or !defined('TEST_AUTH_LDAP_BIND_PW') or !defined('TEST_AUTH_LDAP_DOMAIN')) {
            $this->markTestSkipped('External LDAP test server not configured.');
        }

                $debuginfo = '';
        if (!$connection = ldap_connect_moodle(TEST_AUTH_LDAP_HOST_URL, 3, 'rfc2307', TEST_AUTH_LDAP_BIND_DN, TEST_AUTH_LDAP_BIND_PW, LDAP_DEREF_NEVER, $debuginfo, false)) {
            $this->markTestSkipped('Can not connect to LDAP test server: '.$debuginfo);
        }

        $this->enable_plugin();

                $topdn = 'dc=moodletest,'.TEST_AUTH_LDAP_DOMAIN;

        $this->recursive_delete($connection, TEST_AUTH_LDAP_DOMAIN, 'dc=moodletest');

        $o = array();
        $o['objectClass'] = array('dcObject', 'organizationalUnit');
        $o['dc']         = 'moodletest';
        $o['ou']         = 'MOODLETEST';
        if (!ldap_add($connection, 'dc=moodletest,'.TEST_AUTH_LDAP_DOMAIN, $o)) {
            $this->markTestSkipped('Can not create test LDAP container.');
        }

                $o = array();
        $o['objectClass'] = array('organizationalUnit');
        $o['ou']          = 'users';
        ldap_add($connection, 'ou='.$o['ou'].','.$topdn, $o);

        for ($i=1; $i<=5; $i++) {
            $this->create_ldap_user($connection, $topdn, $i);
        }

                $o = array();
        $o['objectClass'] = array('posixGroup');
        $o['cn']          = 'creators';
        $o['gidNumber']   = 1;
        $o['memberUid']   = array('username1', 'username2');
        ldap_add($connection, 'cn='.$o['cn'].','.$topdn, $o);

        $creatorrole = $DB->get_record('role', array('shortname'=>'coursecreator'));
        $this->assertNotEmpty($creatorrole);


                set_config('host_url', TEST_AUTH_LDAP_HOST_URL, 'auth/ldap');
        set_config('start_tls', 0, 'auth/ldap');
        set_config('ldap_version', 3, 'auth/ldap');
        set_config('ldapencoding', 'utf-8', 'auth/ldap');
        set_config('pagesize', '2', 'auth/ldap');
        set_config('bind_dn', TEST_AUTH_LDAP_BIND_DN, 'auth/ldap');
        set_config('bind_pw', TEST_AUTH_LDAP_BIND_PW, 'auth/ldap');
        set_config('user_type', 'rfc2307', 'auth/ldap');
        set_config('contexts', 'ou=users,'.$topdn, 'auth/ldap');
        set_config('search_sub', 0, 'auth/ldap');
        set_config('opt_deref', LDAP_DEREF_NEVER, 'auth/ldap');
        set_config('user_attribute', 'cn', 'auth/ldap');
        set_config('memberattribute', 'memberuid', 'auth/ldap');
        set_config('memberattribute_isdn', 0, 'auth/ldap');
        set_config('creators', 'cn=creators,'.$topdn, 'auth/ldap');
        set_config('removeuser', AUTH_REMOVEUSER_KEEP, 'auth/ldap');

        set_config('field_map_email', 'mail', 'auth/ldap');
        set_config('field_updatelocal_email', 'oncreate', 'auth/ldap');
        set_config('field_updateremote_email', '0', 'auth/ldap');
        set_config('field_lock_email', 'unlocked', 'auth/ldap');

        set_config('field_map_firstname', 'givenName', 'auth/ldap');
        set_config('field_updatelocal_firstname', 'oncreate', 'auth/ldap');
        set_config('field_updateremote_firstname', '0', 'auth/ldap');
        set_config('field_lock_firstname', 'unlocked', 'auth/ldap');

        set_config('field_map_lastname', 'sn', 'auth/ldap');
        set_config('field_updatelocal_lastname', 'oncreate', 'auth/ldap');
        set_config('field_updateremote_lastname', '0', 'auth/ldap');
        set_config('field_lock_lastname', 'unlocked', 'auth/ldap');


        $this->assertEquals(2, $DB->count_records('user'));
        $this->assertEquals(0, $DB->count_records('role_assignments'));

        
        $auth = get_auth_plugin('ldap');

        ob_start();
        $sink = $this->redirectEvents();
        $auth->sync_users(true);
        $events = $sink->get_events();
        $sink->close();
        ob_end_clean();

                $this->assertCount(7, $events);
        foreach ($events as $index => $event) {
            $usercreatedindex = array(0, 2, 4, 5, 6);
            $roleassignedindex = array (1, 3);
            if (in_array($index, $usercreatedindex)) {
                $this->assertInstanceOf('\core\event\user_created', $event);
            }
            if (in_array($index, $roleassignedindex)) {
                $this->assertInstanceOf('\core\event\role_assigned', $event);
            }
        }

        $this->assertEquals(5, $DB->count_records('user', array('auth'=>'ldap')));
        $this->assertEquals(2, $DB->count_records('role_assignments'));
        $this->assertEquals(2, $DB->count_records('role_assignments', array('roleid'=>$creatorrole->id)));

        for ($i=1; $i<=5; $i++) {
            $this->assertTrue($DB->record_exists('user', array('username'=>'username'.$i, 'email'=>'user'.$i.'@example.com', 'firstname'=>'Firstname'.$i, 'lastname'=>'Lastname'.$i)));
        }

        $this->delete_ldap_user($connection, $topdn, 1);

        ob_start();
        $sink = $this->redirectEvents();
        $auth->sync_users(true);
        $events = $sink->get_events();
        $sink->close();
        ob_end_clean();

                $this->assertCount(0, $events);

        $this->assertEquals(5, $DB->count_records('user', array('auth'=>'ldap')));
        $this->assertEquals(0, $DB->count_records('user', array('suspended'=>1)));
        $this->assertEquals(0, $DB->count_records('user', array('deleted'=>1)));
        $this->assertEquals(2, $DB->count_records('role_assignments'));
        $this->assertEquals(2, $DB->count_records('role_assignments', array('roleid'=>$creatorrole->id)));


        set_config('removeuser', AUTH_REMOVEUSER_SUSPEND, 'auth/ldap');

        
        $auth = get_auth_plugin('ldap');

        ob_start();
        $sink = $this->redirectEvents();
        $auth->sync_users(true);
        $events = $sink->get_events();
        $sink->close();
        ob_end_clean();

                $this->assertCount(1, $events);
        $event = reset($events);
        $this->assertInstanceOf('\core\event\user_updated', $event);

        $this->assertEquals(5, $DB->count_records('user', array('auth'=>'ldap')));
        $this->assertEquals(0, $DB->count_records('user', array('auth'=>'nologin', 'username'=>'username1')));
        $this->assertEquals(1, $DB->count_records('user', array('auth'=>'ldap', 'suspended'=>'1', 'username'=>'username1')));
        $this->assertEquals(0, $DB->count_records('user', array('deleted'=>1)));
        $this->assertEquals(2, $DB->count_records('role_assignments'));
        $this->assertEquals(2, $DB->count_records('role_assignments', array('roleid'=>$creatorrole->id)));

        $this->create_ldap_user($connection, $topdn, 1);

        ob_start();
        $sink = $this->redirectEvents();
        $auth->sync_users(true);
        $events = $sink->get_events();
        $sink->close();
        ob_end_clean();

                $this->assertCount(1, $events);
        $event = reset($events);
        $this->assertInstanceOf('\core\event\user_updated', $event);

        $this->assertEquals(5, $DB->count_records('user', array('auth'=>'ldap')));
        $this->assertEquals(0, $DB->count_records('user', array('suspended'=>1)));
        $this->assertEquals(0, $DB->count_records('user', array('deleted'=>1)));
        $this->assertEquals(2, $DB->count_records('role_assignments'));
        $this->assertEquals(2, $DB->count_records('role_assignments', array('roleid'=>$creatorrole->id)));

        $DB->set_field('user', 'auth', 'nologin', array('username'=>'username1'));

        ob_start();
        $sink = $this->redirectEvents();
        $auth->sync_users(true);
        $events = $sink->get_events();
        $sink->close();
        ob_end_clean();

                $this->assertCount(1, $events);
        $event = reset($events);
        $this->assertInstanceOf('\core\event\user_updated', $event);

        $this->assertEquals(5, $DB->count_records('user', array('auth'=>'ldap')));
        $this->assertEquals(0, $DB->count_records('user', array('suspended'=>1)));
        $this->assertEquals(0, $DB->count_records('user', array('deleted'=>1)));
        $this->assertEquals(2, $DB->count_records('role_assignments'));
        $this->assertEquals(2, $DB->count_records('role_assignments', array('roleid'=>$creatorrole->id)));

        set_config('removeuser', AUTH_REMOVEUSER_FULLDELETE, 'auth/ldap');

        
        $auth = get_auth_plugin('ldap');

        $this->delete_ldap_user($connection, $topdn, 1);

        ob_start();
        $sink = $this->redirectEvents();
        $auth->sync_users(true);
        $events = $sink->get_events();
        $sink->close();
        ob_end_clean();

                $this->assertCount(2, $events);
        $event = array_pop($events);
        $this->assertInstanceOf('\core\event\user_deleted', $event);
        $event = array_pop($events);
        $this->assertInstanceOf('\core\event\role_unassigned', $event);

        $this->assertEquals(5, $DB->count_records('user', array('auth'=>'ldap')));
        $this->assertEquals(0, $DB->count_records('user', array('username'=>'username1')));
        $this->assertEquals(0, $DB->count_records('user', array('suspended'=>1)));
        $this->assertEquals(1, $DB->count_records('user', array('deleted'=>1)));
        $this->assertEquals(1, $DB->count_records('role_assignments'));
        $this->assertEquals(1, $DB->count_records('role_assignments', array('roleid'=>$creatorrole->id)));

        $this->create_ldap_user($connection, $topdn, 1);

        ob_start();
        $sink = $this->redirectEvents();
        $auth->sync_users(true);
        $events = $sink->get_events();
        $sink->close();
        ob_end_clean();

                $this->assertCount(2, $events);
        $event = array_pop($events);
        $this->assertInstanceOf('\core\event\role_assigned', $event);
        $event = array_pop($events);
        $this->assertInstanceOf('\core\event\user_created', $event);

        $this->assertEquals(6, $DB->count_records('user', array('auth'=>'ldap')));
        $this->assertEquals(1, $DB->count_records('user', array('username'=>'username1')));
        $this->assertEquals(0, $DB->count_records('user', array('suspended'=>1)));
        $this->assertEquals(1, $DB->count_records('user', array('deleted'=>1)));
        $this->assertEquals(2, $DB->count_records('role_assignments'));
        $this->assertEquals(2, $DB->count_records('role_assignments', array('roleid'=>$creatorrole->id)));


        $this->recursive_delete($connection, TEST_AUTH_LDAP_DOMAIN, 'dc=moodletest');
        ldap_close($connection);
    }

    
    public function test_ldap_user_loggedin_event() {
        global $CFG, $DB, $USER;

        require_once($CFG->dirroot . '/auth/ldap/auth.php');

        $this->resetAfterTest();

        $this->assertFalse(isloggedin());
        $user = $DB->get_record('user', array('username'=>'admin'));

                        $ldap = new auth_plugin_ldap();

                set_cache_flag($ldap->pluginconfig . '/ntlmsess', sesskey(), $user->username, AUTH_NTLMTIMEOUT);

                update_internal_user_password($user, sesskey());

                $sink = $this->redirectEvents();
                        @$ldap->ntlmsso_finish();
        $events = $sink->get_events();
        $sink->close();

                $this->assertCount(1, $events);
        $event = reset($events);
        $this->assertInstanceOf('\core\event\user_loggedin', $event);
        $this->assertEquals('user', $event->objecttable);
        $this->assertEquals('2', $event->objectid);
        $this->assertEquals(context_system::instance()->id, $event->contextid);
        $expectedlog = array(SITEID, 'user', 'login', 'view.php?id=' . $USER->id . '&course=' . SITEID, $user->id,
            0, $user->id);
        $this->assertEventLegacyLogData($expectedlog, $event);
    }

    
    public function test_ldap_user_signup() {
        global $CFG, $DB;

                $user = array(
            'username' => 'usersignuptest1',
            'password' => 'Moodle2014!',
            'idnumber' => 'idsignuptest1',
            'firstname' => 'First Name User Test 1',
            'lastname' => 'Last Name User Test 1',
            'middlename' => 'Middle Name User Test 1',
            'lastnamephonetic' => '最後のお名前のテスト一号',
            'firstnamephonetic' => 'お名前のテスト一号',
            'alternatename' => 'Alternate Name User Test 1',
            'email' => 'usersignuptest1@example.com',
            'description' => 'This is a description for user 1',
            'city' => 'Perth',
            'country' => 'AU',
            'mnethostid' => $CFG->mnet_localhost_id,
            'auth' => 'ldap'
            );

        if (!extension_loaded('ldap')) {
            $this->markTestSkipped('LDAP extension is not loaded.');
        }

        $this->resetAfterTest();

        require_once($CFG->dirroot.'/auth/ldap/auth.php');
        require_once($CFG->libdir.'/ldaplib.php');

        if (!defined('TEST_AUTH_LDAP_HOST_URL') or !defined('TEST_AUTH_LDAP_BIND_DN') or !defined('TEST_AUTH_LDAP_BIND_PW') or !defined('TEST_AUTH_LDAP_DOMAIN')) {
            $this->markTestSkipped('External LDAP test server not configured.');
        }

                $debuginfo = '';
        if (!$connection = ldap_connect_moodle(TEST_AUTH_LDAP_HOST_URL, 3, 'rfc2307', TEST_AUTH_LDAP_BIND_DN, TEST_AUTH_LDAP_BIND_PW, LDAP_DEREF_NEVER, $debuginfo, false)) {
            $this->markTestSkipped('Can not connect to LDAP test server: '.$debuginfo);
        }

        $this->enable_plugin();

                $topdn = 'dc=moodletest,'.TEST_AUTH_LDAP_DOMAIN;

        $this->recursive_delete($connection, TEST_AUTH_LDAP_DOMAIN, 'dc=moodletest');

        $o = array();
        $o['objectClass'] = array('dcObject', 'organizationalUnit');
        $o['dc']         = 'moodletest';
        $o['ou']         = 'MOODLETEST';
        if (!ldap_add($connection, 'dc=moodletest,'.TEST_AUTH_LDAP_DOMAIN, $o)) {
            $this->markTestSkipped('Can not create test LDAP container.');
        }

                $o = array();
        $o['objectClass'] = array('organizationalUnit');
        $o['ou']          = 'users';
        ldap_add($connection, 'ou='.$o['ou'].','.$topdn, $o);

                set_config('host_url', TEST_AUTH_LDAP_HOST_URL, 'auth/ldap');
        set_config('start_tls', 0, 'auth/ldap');
        set_config('ldap_version', 3, 'auth/ldap');
        set_config('ldapencoding', 'utf-8', 'auth/ldap');
        set_config('pagesize', '2', 'auth/ldap');
        set_config('bind_dn', TEST_AUTH_LDAP_BIND_DN, 'auth/ldap');
        set_config('bind_pw', TEST_AUTH_LDAP_BIND_PW, 'auth/ldap');
        set_config('user_type', 'rfc2307', 'auth/ldap');
        set_config('contexts', 'ou=users,'.$topdn, 'auth/ldap');
        set_config('search_sub', 0, 'auth/ldap');
        set_config('opt_deref', LDAP_DEREF_NEVER, 'auth/ldap');
        set_config('user_attribute', 'cn', 'auth/ldap');
        set_config('memberattribute', 'memberuid', 'auth/ldap');
        set_config('memberattribute_isdn', 0, 'auth/ldap');
        set_config('creators', 'cn=creators,'.$topdn, 'auth/ldap');
        set_config('removeuser', AUTH_REMOVEUSER_KEEP, 'auth/ldap');

        set_config('field_map_email', 'mail', 'auth/ldap');
        set_config('field_updatelocal_email', 'oncreate', 'auth/ldap');
        set_config('field_updateremote_email', '0', 'auth/ldap');
        set_config('field_lock_email', 'unlocked', 'auth/ldap');

        set_config('field_map_firstname', 'givenName', 'auth/ldap');
        set_config('field_updatelocal_firstname', 'oncreate', 'auth/ldap');
        set_config('field_updateremote_firstname', '0', 'auth/ldap');
        set_config('field_lock_firstname', 'unlocked', 'auth/ldap');

        set_config('field_map_lastname', 'sn', 'auth/ldap');
        set_config('field_updatelocal_lastname', 'oncreate', 'auth/ldap');
        set_config('field_updateremote_lastname', '0', 'auth/ldap');
        set_config('field_lock_lastname', 'unlocked', 'auth/ldap');
        set_config('passtype', 'md5', 'auth/ldap');
        set_config('create_context', 'ou=users,'.$topdn, 'auth/ldap');

        $this->assertEquals(2, $DB->count_records('user'));
        $this->assertEquals(0, $DB->count_records('role_assignments'));

        
        $auth = get_auth_plugin('ldap');

        $sink = $this->redirectEvents();
        $mailsink = $this->redirectEmails();
        $auth->user_signup((object)$user, false);
        $this->assertEquals(1, $mailsink->count());
        $events = $sink->get_events();
        $sink->close();

                $this->assertCount(2, $events);

                $dbuser = $DB->get_record('user', array('username' => $user['username']));
        $user['id'] = $dbuser->id;

                $event = array_pop($events);
        $this->assertInstanceOf('\core\event\user_created', $event);
        $this->assertEquals($user['id'], $event->objectid);
        $this->assertEquals('user_created', $event->get_legacy_eventname());
        $this->assertEquals(context_user::instance($user['id']), $event->get_context());
        $expectedlogdata = array(SITEID, 'user', 'add', '/view.php?id='.$event->objectid, fullname($dbuser));
        $this->assertEventLegacyLogData($expectedlogdata, $event);

                $event = array_pop($events);
        $this->assertInstanceOf('\core\event\user_password_updated', $event);
        $this->assertEventContextNotUsed($event);

                ldap_delete($connection, 'cn='.$user['username'].',ou=users,'.$topdn);
    }

    protected function create_ldap_user($connection, $topdn, $i) {
        $o = array();
        $o['objectClass']   = array('inetOrgPerson', 'organizationalPerson', 'person', 'posixAccount');
        $o['cn']            = 'username'.$i;
        $o['sn']            = 'Lastname'.$i;
        $o['givenName']     = 'Firstname'.$i;
        $o['uid']           = $o['cn'];
        $o['uidnumber']     = 2000+$i;
        $o['gidNumber']     = 1000+$i;
        $o['homeDirectory'] = '/';
        $o['mail']          = 'user'.$i.'@example.com';
        $o['userPassword']  = 'pass'.$i;
        ldap_add($connection, 'cn='.$o['cn'].',ou=users,'.$topdn, $o);
    }

    protected function delete_ldap_user($connection, $topdn, $i) {
        ldap_delete($connection, 'cn=username'.$i.',ou=users,'.$topdn);
    }

    protected function enable_plugin() {
        $auths = get_enabled_auth_plugins(true);
        if (!in_array('ldap', $auths)) {
            $auths[] = 'ldap';

        }
        set_config('auth', implode(',', $auths));
    }

    protected function recursive_delete($connection, $dn, $filter) {
        if ($res = ldap_list($connection, $dn, $filter, array('dn'))) {
            $info = ldap_get_entries($connection, $res);
            ldap_free_result($res);
            if ($info['count'] > 0) {
                if ($res = ldap_search($connection, "$filter,$dn", 'cn=*', array('dn'))) {
                    $info = ldap_get_entries($connection, $res);
                    ldap_free_result($res);
                    foreach ($info as $i) {
                        if (isset($i['dn'])) {
                            ldap_delete($connection, $i['dn']);
                        }
                    }
                }
                if ($res = ldap_search($connection, "$filter,$dn", 'ou=*', array('dn'))) {
                    $info = ldap_get_entries($connection, $res);
                    ldap_free_result($res);
                    foreach ($info as $i) {
                        if (isset($i['dn']) and $info[0]['dn'] != $i['dn']) {
                            ldap_delete($connection, $i['dn']);
                        }
                    }
                }
                ldap_delete($connection, "$filter,$dn");
            }
        }
    }
}
