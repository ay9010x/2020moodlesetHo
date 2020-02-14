<?php



defined('MOODLE_INTERNAL') || die();


class core_webservice_events_testcase extends advanced_testcase {

    public function setUp() {
        $this->resetAfterTest();
    }

    public function test_function_called() {
                        
        $sink = $this->redirectEvents();

        $fakelogdata = array(1, 'B', true, null);
        $params = array(
            'other' => array(
                'function' => 'A function'
            )
        );
        $event = \core\event\webservice_function_called::create($params);
        $event->set_legacy_logdata($fakelogdata);
        $event->trigger();

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

        $this->assertEquals(context_system::instance(), $event->get_context());
        $this->assertEquals('A function', $event->other['function']);
        $this->assertEventLegacyLogData($fakelogdata, $event);
        $this->assertEventContextNotUsed($event);
    }

    public function test_login_failed() {
                        
        $sink = $this->redirectEvents();

        $fakelogdata = array(1, 'B', true, null);
        $params = array(
            'other' => array(
                'reason' => 'Unit Test',
                'method' => 'Some method',
                'tokenid' => '123'
            )
        );
        $event = \core\event\webservice_login_failed::create($params);
        $event->set_legacy_logdata($fakelogdata);
        $event->trigger();

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

        $this->assertEquals(context_system::instance(), $event->get_context());
        $this->assertEquals($params['other']['reason'], $event->other['reason']);
        $this->assertEquals($params['other']['method'], $event->other['method']);
        $this->assertEquals($params['other']['tokenid'], $event->other['tokenid']);
        $this->assertEventLegacyLogData($fakelogdata, $event);

                $params['other']['token'] = 'I should not be set';
        try {
            $event = \core\event\webservice_login_failed::create($params);
            $this->fail('The token cannot be allowed in \core\event\webservice_login_failed');
        } catch (coding_exception $e) {
        }
        $this->assertEventContextNotUsed($event);
    }

    public function test_service_created() {
        global $CFG, $DB;

                        
        $sink = $this->redirectEvents();

                $service = (object) array(
            'name' => 'Test',
            'enabled' => 1,
            'requiredcapability' => '',
            'restrictedusers' => 0,
            'component' => null,
            'timecreated' => time(),
            'timemodified' => time(),
            'shortname' => null,
            'downloadfiles' => 0,
            'uploadfiles' => 0
        );
        $service->id = $DB->insert_record('external_services', $service);

                $params = array(
            'objectid' => $service->id,
        );
        $event = \core\event\webservice_service_created::create($params);
        $event->add_record_snapshot('external_services', $service);
        $event->trigger();

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertEquals(context_system::instance(), $event->get_context());
        $this->assertEquals($service->id, $event->objectid);
        $returnurl = $CFG->wwwroot . "/" . $CFG->admin . "/settings.php?section=externalservices";
        $expected = array(SITEID, 'webservice', 'add', $returnurl, get_string('addservice', 'webservice', $service));
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    public function test_service_updated() {
        global $CFG, $DB;

                        
        $sink = $this->redirectEvents();

                $service = (object) array(
            'name' => 'Test',
            'enabled' => 1,
            'requiredcapability' => '',
            'restrictedusers' => 0,
            'component' => null,
            'timecreated' => time(),
            'timemodified' => time(),
            'shortname' => null,
            'downloadfiles' => 0,
            'uploadfiles' => 0
        );
        $service->id = $DB->insert_record('external_services', $service);

                $params = array(
            'objectid' => $service->id,
        );
        $event = \core\event\webservice_service_updated::create($params);
        $event->add_record_snapshot('external_services', $service);
        $event->trigger();

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertEquals(context_system::instance(), $event->get_context());
        $this->assertEquals($service->id, $event->objectid);
        $returnurl = $CFG->wwwroot . "/" . $CFG->admin . "/settings.php?section=externalservices";
        $expected = array(SITEID, 'webservice', 'edit', $returnurl, get_string('editservice', 'webservice', $service));
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    public function test_service_deleted() {
        global $CFG, $DB;

                        
        $sink = $this->redirectEvents();

                $service = (object) array(
            'name' => 'Test',
            'enabled' => 1,
            'requiredcapability' => '',
            'restrictedusers' => 0,
            'component' => null,
            'timecreated' => time(),
            'timemodified' => time(),
            'shortname' => null,
            'downloadfiles' => 0,
            'uploadfiles' => 0
        );
        $service->id = $DB->insert_record('external_services', $service);

                $params = array(
            'objectid' => $service->id,
        );
        $event = \core\event\webservice_service_deleted::create($params);
        $event->add_record_snapshot('external_services', $service);
        $event->trigger();

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertEquals(context_system::instance(), $event->get_context());
        $this->assertEquals($service->id, $event->objectid);
        $returnurl = $CFG->wwwroot . "/" . $CFG->admin . "/settings.php?section=externalservices";
        $expected = array(SITEID, 'webservice', 'delete', $returnurl, get_string('deleteservice', 'webservice', $service));
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    public function test_service_user_added() {
        global $CFG;

                        
        $sink = $this->redirectEvents();

        $params = array(
            'objectid' => 1,
            'relateduserid' => 2
        );
        $event = \core\event\webservice_service_user_added::create($params);
        $event->trigger();

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

        $this->assertEquals(context_system::instance(), $event->get_context());
        $this->assertEquals(1, $event->objectid);
        $this->assertEquals(2, $event->relateduserid);
        $expected = array(SITEID, 'core', 'assign', $CFG->admin . '/webservice/service_users.php?id=' . $params['objectid'],
            'add', '', $params['relateduserid']);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    public function test_service_user_removed() {
        global $CFG;

                        
        $sink = $this->redirectEvents();

        $params = array(
            'objectid' => 1,
            'relateduserid' => 2
        );
        $event = \core\event\webservice_service_user_removed::create($params);
        $event->trigger();

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

        $this->assertEquals(context_system::instance(), $event->get_context());
        $this->assertEquals(1, $event->objectid);
        $this->assertEquals(2, $event->relateduserid);
        $expected = array(SITEID, 'core', 'assign', $CFG->admin . '/webservice/service_users.php?id=' . $params['objectid'],
            'remove', '', $params['relateduserid']);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    public function test_token_created() {
                        
        $sink = $this->redirectEvents();

        $params = array(
            'objectid' => 1,
            'relateduserid' => 2,
            'other' => array(
                'auto' => true
            )
        );
        $event = \core\event\webservice_token_created::create($params);
        $event->trigger();

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

        $this->assertEquals(context_system::instance(), $event->get_context());
        $this->assertEquals(1, $event->objectid);
        $this->assertEquals(2, $event->relateduserid);
        $expected = array(SITEID, 'webservice', 'automatically create user token', '' , 'User ID: ' . 2);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    public function test_token_sent() {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

                        
        $sink = $this->redirectEvents();

        $params = array(
            'objectid' => 1,
            'other' => array(
                'auto' => true
            )
        );
        $event = \core\event\webservice_token_sent::create($params);
        $event->trigger();

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

        $this->assertEquals(context_system::instance(), $event->get_context());
        $this->assertEquals(1, $event->objectid);
        $expected = array(SITEID, 'webservice', 'sending requested user token', '' , 'User ID: ' . $user->id);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }
}
