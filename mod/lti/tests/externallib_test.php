<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/lti/lib.php');


class mod_lti_external_testcase extends externallib_advanced_testcase {

    
    public function setUp() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

                $this->course = $this->getDataGenerator()->create_course();
        $this->lti = $this->getDataGenerator()->create_module('lti',
            array('course' => $this->course->id, 'toolurl' => 'http://localhost/not/real/tool.php'));
        $this->context = context_module::instance($this->lti->cmid);
        $this->cm = get_coursemodule_from_instance('lti', $this->lti->id);

                $this->student = self::getDataGenerator()->create_user();
        $this->teacher = self::getDataGenerator()->create_user();

                $this->studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->getDataGenerator()->enrol_user($this->student->id, $this->course->id, $this->studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($this->teacher->id, $this->course->id, $this->teacherrole->id, 'manual');
    }

    
    public function test_get_tool_launch_data() {
        global $USER, $SITE;

        $result = mod_lti_external::get_tool_launch_data($this->lti->id);
        $result = external_api::clean_returnvalue(mod_lti_external::get_tool_launch_data_returns(), $result);

                self::assertEquals($this->lti->toolurl, $result['endpoint']);
        self::assertCount(36, $result['parameters']);

                $parameters = array();
        foreach ($result['parameters'] as $param) {
            $parameters[$param['name']] = $param['value'];
        }
        self::assertEquals($this->lti->resourcekey, $parameters['oauth_consumer_key']);
        self::assertEquals($this->course->fullname, $parameters['context_title']);
        self::assertEquals($this->course->shortname, $parameters['context_label']);
        self::assertEquals($USER->id, $parameters['user_id']);
        self::assertEquals($USER->firstname, $parameters['lis_person_name_given']);
        self::assertEquals($USER->lastname, $parameters['lis_person_name_family']);
        self::assertEquals(fullname($USER), $parameters['lis_person_name_full']);
        self::assertEquals($USER->username, $parameters['ext_user_username']);
        self::assertEquals("phpunit", $parameters['tool_consumer_instance_name']);
        self::assertEquals("PHPUnit test site", $parameters['tool_consumer_instance_description']);

    }

    
    public function test_mod_lti_get_ltis_by_courses() {
        global $DB;

                $course2 = self::getDataGenerator()->create_course();

                $record = new stdClass();
        $record->course = $course2->id;
        $lti2 = self::getDataGenerator()->create_module('lti', $record);

                $enrol = enrol_get_plugin('manual');
        $enrolinstances = enrol_get_instances($course2->id, true);
        foreach ($enrolinstances as $courseenrolinstance) {
            if ($courseenrolinstance->enrol == "manual") {
                $instance2 = $courseenrolinstance;
                break;
            }
        }
        $enrol->enrol_user($instance2, $this->student->id, $this->studentrole->id);

        self::setUser($this->student);

        $returndescription = mod_lti_external::get_ltis_by_courses_returns();

                        $expectedfields = array('id', 'coursemodule', 'course', 'name', 'intro', 'introformat', 'launchcontainer',
                                'showtitlelaunch', 'showdescriptionlaunch', 'icon', 'secureicon');

                $lti1 = $this->lti;
        $lti1->coursemodule = $lti1->cmid;
        $lti1->introformat = 1;
        $lti1->section = 0;
        $lti1->visible = true;
        $lti1->groupmode = 0;
        $lti1->groupingid = 0;

        $lti2->coursemodule = $lti2->cmid;
        $lti2->introformat = 1;
        $lti2->section = 0;
        $lti2->visible = true;
        $lti2->groupmode = 0;
        $lti2->groupingid = 0;

        foreach ($expectedfields as $field) {
                $expected1[$field] = $lti1->{$field};
                $expected2[$field] = $lti2->{$field};
        }

        $expectedltis = array($expected2, $expected1);

                $result = mod_lti_external::get_ltis_by_courses(array($course2->id, $this->course->id));
        $result = external_api::clean_returnvalue($returndescription, $result);

        $this->assertEquals($expectedltis, $result['ltis']);
        $this->assertCount(0, $result['warnings']);

                $result = mod_lti_external::get_ltis_by_courses();
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedltis, $result['ltis']);
        $this->assertCount(0, $result['warnings']);

                $enrol->unenrol_user($instance2, $this->student->id);
        array_shift($expectedltis);

                $result = mod_lti_external::get_ltis_by_courses();
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedltis, $result['ltis']);

                $result = mod_lti_external::get_ltis_by_courses(array($course2->id));
        $this->assertCount(1, $result['warnings']);
        $this->assertEquals('1', $result['warnings'][0]['warningcode']);
        $this->assertEquals($course2->id, $result['warnings'][0]['itemid']);

                self::setUser($this->teacher);

        $additionalfields = array('timecreated', 'timemodified', 'typeid', 'toolurl', 'securetoolurl',
                        'instructorchoicesendname', 'instructorchoicesendemailaddr', 'instructorchoiceallowroster',
                        'instructorchoiceallowsetting', 'instructorcustomparameters', 'instructorchoiceacceptgrades', 'grade',
                        'resourcekey', 'password', 'debuglaunch', 'servicesalt', 'visible', 'groupmode', 'groupingid');

        foreach ($additionalfields as $field) {
                $expectedltis[0][$field] = $lti1->{$field};
        }

        $result = mod_lti_external::get_ltis_by_courses();
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedltis, $result['ltis']);

                self::setAdminUser();

        $result = mod_lti_external::get_ltis_by_courses(array($this->course->id));
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedltis, $result['ltis']);

                $this->setUser($this->student);
        $contextcourse1 = context_course::instance($this->course->id);
                assign_capability('mod/lti:view', CAP_PROHIBIT, $this->studentrole->id, $contextcourse1->id);
                accesslib_clear_all_caches_for_unit_testing();
        course_modinfo::clear_instance_cache();

        $ltis = mod_lti_external::get_ltis_by_courses(array($this->course->id));
        $ltis = external_api::clean_returnvalue(mod_lti_external::get_ltis_by_courses_returns(), $ltis);
        $this->assertCount(0, $ltis['ltis']);
    }

    
    public function test_view_lti() {
        global $DB;

                try {
            mod_lti_external::view_lti(0);
            $this->fail('Exception expected due to invalid mod_lti instance id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('invalidrecord', $e->errorcode);
        }

                $usernotenrolled = self::getDataGenerator()->create_user();
        $this->setUser($usernotenrolled);
        try {
            mod_lti_external::view_lti($this->lti->id);
            $this->fail('Exception expected due to not enrolled user.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

                $this->setUser($this->student);

                $sink = $this->redirectEvents();

        $result = mod_lti_external::view_lti($this->lti->id);
        $result = external_api::clean_returnvalue(mod_lti_external::view_lti_returns(), $result);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_lti\event\course_module_viewed', $event);
        $this->assertEquals($this->context, $event->get_context());
        $moodlelti = new \moodle_url('/mod/lti/view.php', array('id' => $this->cm->id));
        $this->assertEquals($moodlelti, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

                        assign_capability('mod/lti:view', CAP_PROHIBIT, $this->studentrole->id, $this->context->id);
                accesslib_clear_all_caches_for_unit_testing();
        course_modinfo::clear_instance_cache();

        try {
            mod_lti_external::view_lti($this->lti->id);
            $this->fail('Exception expected due to missing capability.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

    }

    
    public function test_mod_lti_create_tool_proxy() {
        $capabilities = ['AA', 'BB'];
        $proxy = mod_lti_external::create_tool_proxy('Test proxy', $this->getExternalTestFileUrl('/test.html'), $capabilities, []);
        $this->assertEquals('Test proxy', $proxy->name);
        $this->assertEquals($this->getExternalTestFileUrl('/test.html'), $proxy->regurl);
        $this->assertEquals(LTI_TOOL_PROXY_STATE_PENDING, $proxy->state);
        $this->assertEquals(implode("\n", $capabilities), $proxy->capabilityoffered);
    }

    
    public function test_mod_lti_create_tool_proxy_duplicateurl() {
        $this->setExpectedException('moodle_exception');
        $proxy = mod_lti_external::create_tool_proxy('Test proxy 1', $this->getExternalTestFileUrl('/test.html'), array(), array());
        $proxy = mod_lti_external::create_tool_proxy('Test proxy 2', $this->getExternalTestFileUrl('/test.html'), array(), array());
    }

    
    public function test_mod_lti_create_tool_proxy_without_capability() {
        self::setUser($this->teacher);
        $this->setExpectedException('required_capability_exception');
        $proxy = mod_lti_external::create_tool_proxy('Test proxy', $this->getExternalTestFileUrl('/test.html'), array(), array());
    }

    
    public function test_mod_lti_delete_tool_proxy() {
        $proxy = mod_lti_external::create_tool_proxy('Test proxy', $this->getExternalTestFileUrl('/test.html'), array(), array());
        $this->assertNotEmpty(lti_get_tool_proxy($proxy->id));

        $proxy = mod_lti_external::delete_tool_proxy($proxy->id);
        $this->assertEquals('Test proxy', $proxy->name);
        $this->assertEquals($this->getExternalTestFileUrl('/test.html'), $proxy->regurl);
        $this->assertEquals(LTI_TOOL_PROXY_STATE_PENDING, $proxy->state);
        $this->assertEmpty(lti_get_tool_proxy($proxy->id));
    }

    
    public function test_mod_lti_get_tool_proxy_registration_request() {
        $proxy = mod_lti_external::create_tool_proxy('Test proxy', $this->getExternalTestFileUrl('/test.html'), array(), array());
        $request = mod_lti_external::get_tool_proxy_registration_request($proxy->id);
        $this->assertEquals('ToolProxyRegistrationRequest', $request['lti_message_type']);
        $this->assertEquals('LTI-2p0', $request['lti_version']);
    }

    
    public function test_mod_lti_get_tool_types() {
                $proxy = mod_lti_external::create_tool_proxy('Test proxy', $this->getExternalTestFileUrl('/test.html'), array(), array());

                $type = new stdClass();
        $data = new stdClass();
        $type->state = LTI_TOOL_STATE_CONFIGURED;
        $type->name = "Test tool";
        $type->description = "Example description";
        $type->toolproxyid = $proxy->id;
        $type->baseurl = $this->getExternalTestFileUrl('/test.html');
        $typeid = lti_add_type($type, $data);

        $types = mod_lti_external::get_tool_types($proxy->id);
        $this->assertEquals(1, count($types));
        $type = $types[0];
        $this->assertEquals('Test tool', $type['name']);
        $this->assertEquals('Example description', $type['description']);
    }

    
    public function test_mod_lti_create_tool_type() {
        $type = mod_lti_external::create_tool_type($this->getExternalTestFileUrl('/ims_cartridge_basic_lti_link.xml'), '', '');
        $this->assertEquals('Example tool', $type['name']);
        $this->assertEquals('Example tool description', $type['description']);
        $this->assertEquals($this->getExternalTestFileUrl('/test.jpg', true), $type['urls']['icon']);
        $typeentry = lti_get_type($type['id']);
        $this->assertEquals('http://www.example.com/lti/provider.php', $typeentry->baseurl);
        $config = lti_get_type_config($type['id']);
        $this->assertTrue(isset($config['sendname']));
        $this->assertTrue(isset($config['sendemailaddr']));
        $this->assertTrue(isset($config['acceptgrades']));
        $this->assertTrue(isset($config['forcessl']));
    }

    
    public function test_mod_lti_create_tool_type_nonexistant_file() {
        $this->setExpectedException('moodle_exception');
        $type = mod_lti_external::create_tool_type($this->getExternalTestFileUrl('/doesntexist.xml'), '', '');
    }

    
    public function test_mod_lti_create_tool_type_bad_file() {
        $this->setExpectedException('moodle_exception');
        $type = mod_lti_external::create_tool_type($this->getExternalTestFileUrl('/rsstest.xml'), '', '');
    }

    
    public function test_mod_lti_create_tool_type_without_capability() {
        self::setUser($this->teacher);
        $this->setExpectedException('required_capability_exception');
        $type = mod_lti_external::create_tool_type($this->getExternalTestFileUrl('/ims_cartridge_basic_lti_link.xml'), '', '');
    }

    
    public function test_mod_lti_update_tool_type() {
        $type = mod_lti_external::create_tool_type($this->getExternalTestFileUrl('/ims_cartridge_basic_lti_link.xml'), '', '');
        $type = mod_lti_external::update_tool_type($type['id'], 'New name', 'New description', LTI_TOOL_STATE_PENDING);
        $this->assertEquals('New name', $type['name']);
        $this->assertEquals('New description', $type['description']);
        $this->assertEquals('Pending', $type['state']['text']);
    }

    
    public function test_mod_lti_delete_tool_type() {
        $type = mod_lti_external::create_tool_type($this->getExternalTestFileUrl('/ims_cartridge_basic_lti_link.xml'), '', '');
        $this->assertNotEmpty(lti_get_type($type['id']));
        $type = mod_lti_external::delete_tool_type($type['id']);
        $this->assertEmpty(lti_get_type($type['id']));
    }

    
    public function test_mod_lti_delete_tool_type_without_capability() {
        $type = mod_lti_external::create_tool_type($this->getExternalTestFileUrl('/ims_cartridge_basic_lti_link.xml'), '', '');
        $this->assertNotEmpty(lti_get_type($type['id']));
        $this->setExpectedException('required_capability_exception');
        self::setUser($this->teacher);
        $type = mod_lti_external::delete_tool_type($type['id']);
    }

    
    public function test_mod_lti_is_cartridge() {
        $result = mod_lti_external::is_cartridge($this->getExternalTestFileUrl('/ims_cartridge_basic_lti_link.xml'));
        $this->assertTrue($result['iscartridge']);
        $result = mod_lti_external::is_cartridge($this->getExternalTestFileUrl('/test.html'));
        $this->assertFalse($result['iscartridge']);
    }
}
