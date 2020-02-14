<?php





abstract class database_driver_testcase extends base_testcase {
    
    private static $extradb = null;

    
    protected $tdb;

    
    final public function __construct($name = null, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);

        $this->setBackupGlobals(false);
        $this->setBackupStaticAttributes(false);
        $this->setRunTestInSeparateProcess(false);
    }

    public static function setUpBeforeClass() {
        global $CFG;
        parent::setUpBeforeClass();

        if (!defined('PHPUNIT_TEST_DRIVER')) {
                        return;
        }

        if (!isset($CFG->phpunit_extra_drivers[PHPUNIT_TEST_DRIVER])) {
            throw new exception('Can not find driver configuration options with index: '.PHPUNIT_TEST_DRIVER);
        }

        $dblibrary = empty($CFG->phpunit_extra_drivers[PHPUNIT_TEST_DRIVER]['dblibrary']) ? 'native' : $CFG->phpunit_extra_drivers[PHPUNIT_TEST_DRIVER]['dblibrary'];
        $dbtype = $CFG->phpunit_extra_drivers[PHPUNIT_TEST_DRIVER]['dbtype'];
        $dbhost = $CFG->phpunit_extra_drivers[PHPUNIT_TEST_DRIVER]['dbhost'];
        $dbname = $CFG->phpunit_extra_drivers[PHPUNIT_TEST_DRIVER]['dbname'];
        $dbuser = $CFG->phpunit_extra_drivers[PHPUNIT_TEST_DRIVER]['dbuser'];
        $dbpass = $CFG->phpunit_extra_drivers[PHPUNIT_TEST_DRIVER]['dbpass'];
        $prefix = $CFG->phpunit_extra_drivers[PHPUNIT_TEST_DRIVER]['prefix'];
        $dboptions = empty($CFG->phpunit_extra_drivers[PHPUNIT_TEST_DRIVER]['dboptions']) ? array() : $CFG->phpunit_extra_drivers[PHPUNIT_TEST_DRIVER]['dboptions'];

        $classname = "{$dbtype}_{$dblibrary}_moodle_database";
        require_once("$CFG->libdir/dml/$classname.php");
        $d = new $classname();
        if (!$d->driver_installed()) {
            throw new exception('Database driver for '.$classname.' is not installed');
        }

        $d->connect($dbhost, $dbuser, $dbpass, $dbname, $prefix, $dboptions);

        self::$extradb = $d;
    }

    protected function setUp() {
        global $DB;
        parent::setUp();

        if (self::$extradb) {
            $this->tdb = self::$extradb;
        } else {
            $this->tdb = $DB;
        }
    }

    protected function tearDown() {
                $dbman = $this->tdb->get_manager();
        $tables = $this->tdb->get_tables(false);
        foreach($tables as $tablename) {
            if (strpos($tablename, 'test_table') === 0) {
                $table = new xmldb_table($tablename);
                $dbman->drop_table($table);
            }
        }
        parent::tearDown();
    }

    public static function tearDownAfterClass() {
        if (self::$extradb) {
            self::$extradb->dispose();
            self::$extradb = null;
        }
        phpunit_util::reset_all_data(null);
        parent::tearDownAfterClass();
    }

    
    public function runBare() {
        try {
            parent::runBare();

        } catch (Exception $ex) {
            $e = $ex;
        } catch (Throwable $ex) {
                        $e = $ex;
        }

        if (isset($e)) {
            if ($this->tdb->is_transaction_started()) {
                $this->tdb->force_transaction_rollback();
            }
            $this->tearDown();
            throw $e;
        }
    }

    
    public function getDebuggingMessages() {
        return phpunit_util::get_debugging_messages();
    }

    
    public function resetDebugging() {
        phpunit_util::reset_debugging();
    }

    
    public function assertDebuggingCalled($debugmessage = null, $debuglevel = null, $message = '') {
        $debugging = $this->getDebuggingMessages();
        $count = count($debugging);

        if ($count == 0) {
            if ($message === '') {
                $message = 'Expectation failed, debugging() not triggered.';
            }
            $this->fail($message);
        }
        if ($count > 1) {
            if ($message === '') {
                $message = 'Expectation failed, debugging() triggered '.$count.' times.';
            }
            $this->fail($message);
        }
        $this->assertEquals(1, $count);

        $debug = reset($debugging);
        if ($debugmessage !== null) {
            $this->assertSame($debugmessage, $debug->message, $message);
        }
        if ($debuglevel !== null) {
            $this->assertSame($debuglevel, $debug->level, $message);
        }

        $this->resetDebugging();
    }

    
    public function assertDebuggingNotCalled($message = '') {
        $debugging = $this->getDebuggingMessages();
        $count = count($debugging);

        if ($message === '') {
            $message = 'Expectation failed, debugging() was triggered.';
        }
        $this->assertEquals(0, $count, $message);
    }
}
