<?php



defined('MOODLE_INTERNAL') || die();


class core_session_redis_testcase extends advanced_testcase {

    
    protected $keyprefix = null;
    
    protected $redis = null;

    public function setUp() {
        global $CFG;

        if (!extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension not loaded.');
        }
        if (!defined('TEST_SESSION_REDIS_HOST')) {
            $this->markTestSkipped('Session test server not set. define: TEST_SESSION_REDIS_HOST');
        }

        $this->resetAfterTest();

        $this->keyprefix = 'phpunit'.rand(1, 100000);

        $CFG->session_redis_host = TEST_SESSION_REDIS_HOST;
        $CFG->session_redis_prefix = $this->keyprefix;

                        $CFG->session_redis_acquire_lock_timeout = 1;
        $CFG->session_redis_lock_expire = 70;

        $this->redis = new Redis();
        $this->redis->connect(TEST_SESSION_REDIS_HOST);
    }

    public function tearDown() {
        if (!extension_loaded('redis') || !defined('TEST_SESSION_REDIS_HOST')) {
            return;
        }

        $list = $this->redis->keys($this->keyprefix.'*');
        foreach ($list as $keyname) {
            $this->redis->del($keyname);
        }
        $this->redis->close();
    }

    public function test_normal_session_start_stop_works() {
        $sess = new \core\session\redis();
        $sess->init();
        $this->assertTrue($sess->handler_open('Not used', 'Not used'));
        $this->assertSame('', $sess->handler_read('sess1'));
        $this->assertTrue($sess->handler_write('sess1', 'DATA'));
        $this->assertTrue($sess->handler_close());

                $this->assertTrue($sess->handler_open('Not used', 'Not used'));
        $this->assertSame('DATA', $sess->handler_read('sess1'));
        $this->assertTrue($sess->handler_write('sess1', 'DATA-new'));
        $this->assertTrue($sess->handler_close());
        $this->assertSessionNoLocks();
    }

    public function test_session_blocks_with_existing_session() {
        $sess = new \core\session\redis();
        $sess->init();
        $this->assertTrue($sess->handler_open('Not used', 'Not used'));
        $this->assertSame('', $sess->handler_read('sess1'));
        $this->assertTrue($sess->handler_write('sess1', 'DATA'));
        $this->assertTrue($sess->handler_close());

                $this->assertTrue($sess->handler_open('Not used', 'Not used'));
        $this->assertSame('DATA', $sess->handler_read('sess1'));

        $sessblocked = new \core\session\redis();
        $sessblocked->init();
        $this->assertTrue($sessblocked->handler_open('Not used', 'Not used'));

                $errorlog = tempnam(sys_get_temp_dir(), "rediserrorlog");
        $this->iniSet('error_log', $errorlog);
        try {
            $sessblocked->handler_read('sess1');
            $this->fail('Session lock must fail to be obtained.');
        } catch (\core\session\exception $e) {
            $this->assertContains("Unable to obtain session lock", $e->getMessage());
            $this->assertContains('Cannot obtain session lock for sid: sess1', file_get_contents($errorlog));
        }

        $this->assertTrue($sessblocked->handler_close());
        $this->assertTrue($sess->handler_write('sess1', 'DATA-new'));
        $this->assertTrue($sess->handler_close());
        $this->assertSessionNoLocks();
    }

    public function test_session_is_destroyed_when_it_does_not_exist() {
        $sess = new \core\session\redis();
        $sess->init();
        $this->assertTrue($sess->handler_open('Not used', 'Not used'));
        $this->assertTrue($sess->handler_destroy('sess-destroy'));
        $this->assertSessionNoLocks();
    }

    public function test_session_is_destroyed_when_we_have_it_open() {
        $sess = new \core\session\redis();
        $sess->init();
        $this->assertTrue($sess->handler_open('Not used', 'Not used'));
        $this->assertSame('', $sess->handler_read('sess-destroy'));
        $this->assertTrue($sess->handler_destroy('sess-destroy'));
        $this->assertTrue($sess->handler_close());
        $this->assertSessionNoLocks();
    }

    public function test_multiple_sessions_do_not_interfere_with_each_other() {
        $sess1 = new \core\session\redis();
        $sess1->init();
        $sess2 = new \core\session\redis();
        $sess2->init();

                $this->assertTrue($sess1->handler_open('Not used', 'Not used'));
        $this->assertSame('', $sess1->handler_read('sess1'));
        $this->assertTrue($sess1->handler_write('sess1', 'DATA'));
        $this->assertTrue($sess1->handler_close());

                $this->assertTrue($sess2->handler_open('Not used', 'Not used'));
        $this->assertSame('', $sess2->handler_read('sess2'));
        $this->assertTrue($sess2->handler_write('sess2', 'DATA2'));
        $this->assertTrue($sess2->handler_close());

                $this->assertTrue($sess1->handler_open('Not used', 'Not used'));
        $this->assertSame('DATA', $sess1->handler_read('sess1'));
        $this->assertTrue($sess2->handler_open('Not used', 'Not used'));
        $this->assertSame('DATA2', $sess2->handler_read('sess2'));

                $this->assertTrue($sess1->handler_write('sess1', 'DATAX'));
        $this->assertTrue($sess2->handler_write('sess2', 'DATA2X'));

                $this->assertTrue($sess1->handler_open('Not used', 'Not used'));
        $this->assertTrue($sess2->handler_open('Not used', 'Not used'));
        $this->assertEquals('DATAX', $sess1->handler_read('sess1'));
        $this->assertEquals('DATA2X', $sess2->handler_read('sess2'));

                $this->assertTrue($sess1->handler_close());
        $this->assertTrue($sess2->handler_close());

                $this->assertSessionNoLocks();
    }

    public function test_multiple_sessions_work_with_a_single_instance() {
        $sess = new \core\session\redis();
        $sess->init();

                $this->assertTrue($sess->handler_open('Not used', 'Not used'));
        $this->assertSame('', $sess->handler_read('sess1'));
        $this->assertTrue($sess->handler_write('sess1', 'DATA'));
        $this->assertSame('', $sess->handler_read('sess2'));
        $this->assertTrue($sess->handler_write('sess2', 'DATA2'));
        $this->assertSame('DATA', $sess->handler_read('sess1'));
        $this->assertSame('DATA2', $sess->handler_read('sess2'));
        $this->assertTrue($sess->handler_destroy('sess2'));

        $this->assertTrue($sess->handler_close());
        $this->assertSessionNoLocks();

        $this->assertTrue($sess->handler_close());
    }

    public function test_session_exists_returns_valid_values() {
        $sess = new \core\session\redis();
        $sess->init();

        $this->assertTrue($sess->handler_open('Not used', 'Not used'));
        $this->assertSame('', $sess->handler_read('sess1'));

        $this->assertFalse($sess->session_exists('sess1'), 'Session must not exist yet, it has not been saved');
        $this->assertTrue($sess->handler_write('sess1', 'DATA'));
        $this->assertTrue($sess->session_exists('sess1'), 'Session must exist now.');
        $this->assertTrue($sess->handler_destroy('sess1'));
        $this->assertFalse($sess->session_exists('sess1'), 'Session should be destroyed.');
    }

    public function test_kill_sessions_removes_the_session_from_redis() {
        global $DB;

        $sess = new \core\session\redis();
        $sess->init();

        $this->assertTrue($sess->handler_open('Not used', 'Not used'));
        $this->assertTrue($sess->handler_write('sess1', 'DATA'));
        $this->assertTrue($sess->handler_write('sess2', 'DATA'));
        $this->assertTrue($sess->handler_write('sess3', 'DATA'));

        $sessiondata = new \stdClass();
        $sessiondata->userid = 2;
        $sessiondata->timecreated = time();
        $sessiondata->timemodified = time();

        $sessiondata->sid = 'sess1';
        $DB->insert_record('sessions', $sessiondata);
        $sessiondata->sid = 'sess2';
        $DB->insert_record('sessions', $sessiondata);
        $sessiondata->sid = 'sess3';
        $DB->insert_record('sessions', $sessiondata);

        $this->assertNotEquals('', $sess->handler_read('sess1'));
        $sess->kill_session('sess1');
        $this->assertEquals('', $sess->handler_read('sess1'));

        $this->assertEmpty($this->redis->keys($this->keyprefix.'sess1.lock'));

        $sess->kill_all_sessions();

        $this->assertEquals(3, $DB->count_records('sessions'), 'Moodle handles session database, plugin must not change it.');
        $this->assertSessionNoLocks();
        $this->assertEmpty($this->redis->keys($this->keyprefix.'*'), 'There should be no session data left.');
    }

    
    protected function assertSessionNoLocks() {
        $this->assertEmpty($this->redis->keys($this->keyprefix.'*.lock'));
    }
}
