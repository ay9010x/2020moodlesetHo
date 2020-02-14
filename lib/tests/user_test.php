<?php




class core_user_testcase extends advanced_testcase {

    
    protected function setUp() {
        $this->resetAfterTest(true);
    }

    public function test_get_user() {
        global $CFG;


                $user = $this->getDataGenerator()->create_user();
        $this->assertEquals($user, core_user::get_user($user->id, '*', MUST_EXIST));

                $CFG->noreplyuserid = null;
        $noreplyuser = core_user::get_noreply_user();
        $this->assertEquals(1, $noreplyuser->emailstop);
        $this->assertFalse(core_user::is_real_user($noreplyuser->id));
        $this->assertEquals($CFG->noreplyaddress, $noreplyuser->email);
        $this->assertEquals(get_string('noreplyname'), $noreplyuser->firstname);

                core_user::reset_internal_users();
        $CFG->noreplyuserid = $user->id;
        $noreplyuser = core_user::get_noreply_user();
        $this->assertEquals(1, $noreplyuser->emailstop);
        $this->assertTrue(core_user::is_real_user($noreplyuser->id));

                core_user::reset_internal_users();
        $CFG->supportemail = null;
        $CFG->noreplyuserid = null;
        $supportuser = core_user::get_support_user();
        $adminuser = get_admin();
        $this->assertEquals($adminuser, $supportuser);
        $this->assertTrue(core_user::is_real_user($supportuser->id));

                core_user::reset_internal_users();
        $CFG->supportemail = 'test@example.com';
        $supportuser = core_user::get_support_user();
        $this->assertEquals(core_user::SUPPORT_USER, $supportuser->id);
        $this->assertFalse(core_user::is_real_user($supportuser->id));

                core_user::reset_internal_users();
        $CFG->supportuserid = $user->id;
        $supportuser = core_user::get_support_user();
        $this->assertEquals($user, $supportuser);
        $this->assertTrue(core_user::is_real_user($supportuser->id));
    }

    
    public function test_get_user_by_username() {
        $record = array();
        $record['username'] = 'johndoe';
        $record['email'] = 'johndoe@example.com';
        $record['timecreated'] = time();

                $userexpected = $this->getDataGenerator()->create_user($record);

                $this->assertEquals($userexpected, core_user::get_user_by_username('johndoe'));

                $this->assertEquals((object) $record, core_user::get_user_by_username('johndoe', 'username,email,timecreated'));

                $this->assertFalse(core_user::get_user_by_username('johndoe', 'username,email,timecreated', 2));

                $record['mnethostid'] = 2;
        $userexpected2 = $this->getDataGenerator()->create_user($record);

                $this->assertEquals($userexpected2, core_user::get_user_by_username('johndoe', '*', 2));

                $this->assertFalse(core_user::get_user_by_username('janedoe'));
    }

    
    public function test_require_active_user() {
        global $DB;

                $userexpected = $this->getDataGenerator()->create_user();

                core_user::require_active_user($userexpected, true, true);

                $DB->set_field('user', 'confirmed', 0, array('id' => $userexpected->id));
        try {
            core_user::require_active_user($userexpected);
        } catch (moodle_exception $e) {
            $this->assertEquals('usernotconfirmed', $e->errorcode);
        }
        $DB->set_field('user', 'confirmed', 1, array('id' => $userexpected->id));

                $DB->set_field('user', 'auth', 'nologin', array('id' => $userexpected->id));
        try {
            core_user::require_active_user($userexpected, false, true);
        } catch (moodle_exception $e) {
            $this->assertEquals('suspended', $e->errorcode);
        }
                core_user::require_active_user($userexpected);
        $DB->set_field('user', 'auth', 'manual', array('id' => $userexpected->id));

                $DB->set_field('user', 'suspended', 1, array('id' => $userexpected->id));
        try {
            core_user::require_active_user($userexpected, true);
        } catch (moodle_exception $e) {
            $this->assertEquals('suspended', $e->errorcode);
        }
                core_user::require_active_user($userexpected);

                delete_user($userexpected);
        try {
            core_user::require_active_user($userexpected);
        } catch (moodle_exception $e) {
            $this->assertEquals('userdeleted', $e->errorcode);
        }

                $noreplyuser = core_user::get_noreply_user();
        try {
            core_user::require_active_user($noreplyuser, true);
        } catch (moodle_exception $e) {
            $this->assertEquals('invaliduser', $e->errorcode);
        }

                $guestuser = $DB->get_record('user', array('username' => 'guest'));
        try {
            core_user::require_active_user($guestuser, true);
        } catch (moodle_exception $e) {
            $this->assertEquals('guestsarenotallowed', $e->errorcode);
        }

    }

    
    public function test_get_property_definition() {
                $properties = core_user::get_property_definition('id');
        $this->assertEquals($properties['type'], PARAM_INT);
        $properties = core_user::get_property_definition('username');
        $this->assertEquals($properties['type'], PARAM_USERNAME);

                try {
            core_user::get_property_definition('fullname');
        } catch (coding_exception $e) {
            $this->assertRegExp('/Invalid property requested./', $e->getMessage());
        }

                try {
            core_user::get_property_definition('');
        } catch (coding_exception $e) {
            $this->assertRegExp('/Invalid property requested./', $e->getMessage());
        }
    }

    
    public function test_validate() {

                $record = array('username' => 's10', 'firstname' => 'Bebe Stevens');
        $validation = core_user::validate((object)$record);

                $this->assertTrue($validation);

                $record = array('username' => 's1', 'firstname' => 'Eric Cartman', 'country' => 'UU', 'theme' => 'beise');

                $validation = core_user::validate((object)$record);
        $this->assertArrayHasKey('country', $validation);
        $this->assertArrayHasKey('theme', $validation);
        $this->assertCount(2, $validation);

                $record = array('username' => 's3', 'firstname' => 'Kyle<script>alert(1);<script> Broflovski');

                $validation = core_user::validate((object)$record);
        $this->assertCount(1, $validation);
        $this->assertArrayHasKey('firstname', $validation);
    }

    
    public function test_clean_data() {
        $this->resetAfterTest(false);

        $user = new stdClass();
        $user->firstname = 'John <script>alert(1)</script> Doe';
        $user->username = 'john%#&~%*_doe';
        $user->email = ' john@testing.com ';
        $user->deleted = 'no';
        $user->description = '<b>A description <script>alert(123);</script>about myself.</b>';
        $usercleaned = core_user::clean_data($user);

                $this->assertEquals('John alert(1) Doe', $usercleaned->firstname);
        $this->assertEquals('john@testing.com', $usercleaned->email);
        $this->assertEquals(0, $usercleaned->deleted);
        $this->assertEquals('<b>A description <script>alert(123);</script>about myself.</b>', $user->description);
        $this->assertEquals('john_doe', $user->username);

                $user->userfullname = 'John Doe';
        core_user::clean_data($user);
        $this->assertDebuggingCalled("The property 'userfullname' could not be cleaned.");
    }

    
    public function test_clean_field() {

                $user = new stdClass();
        $user->firstname = 'John <script>alert(1)</script> Doe';
        $user->username = 'john%#&~%*_doe';
        $user->email = ' john@testing.com ';
        $user->deleted = 'no';
        $user->description = '<b>A description <script>alert(123);</script>about myself.</b>';
        $user->userfullname = 'John Doe';

                $this->assertEquals('John alert(1) Doe', core_user::clean_field($user->firstname, 'firstname'));
        $this->assertEquals('john_doe', core_user::clean_field($user->username, 'username'));
        $this->assertEquals('john@testing.com', core_user::clean_field($user->email, 'email'));
        $this->assertEquals(0, core_user::clean_field($user->deleted, 'deleted'));
        $this->assertEquals('<b>A description <script>alert(123);</script>about myself.</b>', core_user::clean_field($user->description, 'description'));

                core_user::clean_field($user->userfullname, 'fullname');
        $this->assertDebuggingCalled("The property 'fullname' could not be cleaned.");
    }

    
    public function test_get_property_type() {

                $type = core_user::get_property_type('username');
        $this->assertEquals(PARAM_USERNAME, $type);
        $type = core_user::get_property_type('email');
        $this->assertEquals(PARAM_RAW_TRIMMED, $type);
        $type = core_user::get_property_type('timezone');
        $this->assertEquals(PARAM_TIMEZONE, $type);

                $nonexistingproperty = 'userfullname';
        $this->setExpectedException('coding_exception', 'Invalid property requested: ' . $nonexistingproperty);
        core_user::get_property_type($nonexistingproperty);
        $nonexistingproperty = 'mobilenumber';
        $this->setExpectedException('coding_exception', 'Invalid property requested: ' . $nonexistingproperty);
        core_user::get_property_type($nonexistingproperty);
    }

    
    public function test_get_property_null() {
                $property = core_user::get_property_null('username');
        $this->assertEquals(NULL_NOT_ALLOWED, $property);
        $property = core_user::get_property_null('password');
        $this->assertEquals(NULL_NOT_ALLOWED, $property);
        $property = core_user::get_property_null('imagealt');
        $this->assertEquals(NULL_ALLOWED, $property);
        $property = core_user::get_property_null('middlename');
        $this->assertEquals(NULL_ALLOWED, $property);

                $nonexistingproperty = 'lastnamefonetic';
        $this->setExpectedException('coding_exception', 'Invalid property requested: ' . $nonexistingproperty);
        core_user::get_property_null($nonexistingproperty);
        $nonexistingproperty = 'midlename';
        $this->setExpectedException('coding_exception', 'Invalid property requested: ' . $nonexistingproperty);
        core_user::get_property_null($nonexistingproperty);
    }

    
    public function test_get_property_choices() {

                $choices = core_user::get_property_choices('country');
        $this->assertArrayHasKey('AU', $choices);
        $this->assertArrayHasKey('BR', $choices);
        $this->assertArrayNotHasKey('WW', $choices);
        $this->assertArrayNotHasKey('TX', $choices);

                $choices = core_user::get_property_choices('lang');
        $this->assertArrayHasKey('en', $choices);
        $this->assertArrayNotHasKey('ww', $choices);
        $this->assertArrayNotHasKey('yy', $choices);

                $choices = core_user::get_property_choices('theme');
        $this->assertArrayHasKey('base', $choices);
        $this->assertArrayHasKey('clean', $choices);
        $this->assertArrayNotHasKey('unknowntheme', $choices);
        $this->assertArrayNotHasKey('wrongtheme', $choices);

                $nonexistingproperty = 'language';
        $this->setExpectedException('coding_exception', 'Invalid property requested: ' . $nonexistingproperty);
        core_user::get_property_null($nonexistingproperty);
        $nonexistingproperty = 'coutries';
        $this->setExpectedException('coding_exception', 'Invalid property requested: ' . $nonexistingproperty);
        core_user::get_property_null($nonexistingproperty);
    }

    
    public function test_get_property_default() {
        global $CFG;
        $this->resetAfterTest();

        $country = core_user::get_property_default('country');
        $this->assertEquals($CFG->country, $country);
        set_config('country', 'AU');
        core_user::reset_caches();
        $country = core_user::get_property_default('country');
        $this->assertEquals($CFG->country, $country);

        $lang = core_user::get_property_default('lang');
        $this->assertEquals($CFG->lang, $lang);
        set_config('lang', 'en');
        $lang = core_user::get_property_default('lang');
        $this->assertEquals($CFG->lang, $lang);

        $this->setTimezone('Europe/London', 'Pacific/Auckland');
        core_user::reset_caches();
        $timezone = core_user::get_property_default('timezone');
        $this->assertEquals('Europe/London', $timezone);
        $this->setTimezone('99', 'Pacific/Auckland');
        core_user::reset_caches();
        $timezone = core_user::get_property_default('timezone');
        $this->assertEquals('Pacific/Auckland', $timezone);

        $this->setExpectedException('coding_exception', 'Invalid property requested, or the property does not has a default value.');
        core_user::get_property_default('firstname');
    }

    
    public function test_get_noreply_user() {
        global $CFG;

                $langfolder = $CFG->dataroot . '/lang/xx';
        check_dir_exists($langfolder);
        $langconfig = "<?php\n\defined('MOODLE_INTERNAL') || die();";
        file_put_contents($langfolder . '/langconfig.php', $langconfig);
        $langconfig = "<?php\n\$string['noreplyname'] = 'XXX';";
        file_put_contents($langfolder . '/moodle.php', $langconfig);

        $CFG->lang='en';
        $enuser = \core_user::get_noreply_user();

        $CFG->lang='xx';
        $xxuser = \core_user::get_noreply_user();

        $this->assertNotEquals($enuser, $xxuser);
    }

}
