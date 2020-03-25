<?php





abstract class advanced_testcase extends base_testcase {
    
    private $resetAfterTest;

    
    private $testdbtransaction;

    
    private $currenttimestart;

    
    final public function __construct($name = null, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);

        $this->setBackupGlobals(false);
        $this->setBackupStaticAttributes(false);
        $this->setRunTestInSeparateProcess(false);
    }

    
    final public function runBare() {
        global $DB;

        if (phpunit_util::$lastdbwrites != $DB->perf_get_writes()) {
                        $this->testdbtransaction = null;

        } else if ($DB->get_dbfamily() === 'postgres' or $DB->get_dbfamily() === 'mssql') {
                        $this->testdbtransaction = $DB->start_delegated_transaction();
        }

        try {
            $this->setCurrentTimeStart();
            parent::runBare();
                        $DB = phpunit_util::get_global_backup('DB');

                        $debugerror = phpunit_util::display_debugging_messages(true);
            $this->resetDebugging();
            if (!empty($debugerror)) {
                trigger_error('Unexpected debugging() call detected.'."\n".$debugerror, E_USER_NOTICE);
            }

        } catch (Exception $ex) {
            $e = $ex;
        } catch (Throwable $ex) {
                        $e = $ex;
        }

        if (isset($e)) {
                        self::resetAllData();
            throw $e;
        }

        if (!$this->testdbtransaction or $this->testdbtransaction->is_disposed()) {
            $this->testdbtransaction = null;
        }

        if ($this->resetAfterTest === true) {
            if ($this->testdbtransaction) {
                $DB->force_transaction_rollback();
                phpunit_util::reset_all_database_sequences();
                phpunit_util::$lastdbwrites = $DB->perf_get_writes();             }
            self::resetAllData(null);

        } else if ($this->resetAfterTest === false) {
            if ($this->testdbtransaction) {
                $this->testdbtransaction->allow_commit();
            }
            
        } else {
                        if ($this->testdbtransaction) {
                try {
                    $this->testdbtransaction->allow_commit();
                } catch (dml_transaction_exception $e) {
                    self::resetAllData();
                    throw new coding_exception('Invalid transaction state detected in test '.$this->getName());
                }
            }
            self::resetAllData(true);
        }

                if ($DB->is_transaction_started()) {
            self::resetAllData();
            if ($this->getStatus() == PHPUnit_Runner_BaseTestRunner::STATUS_PASSED
                or $this->getStatus() == PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED
                or $this->getStatus() == PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE) {
                throw new coding_exception('Test '.$this->getName().' did not close database transaction');
            }
        }
    }

    
    protected function createFlatXMLDataSet($xmlFile) {
        return new PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet($xmlFile);
    }

    
    protected function createXMLDataSet($xmlFile) {
        return new PHPUnit_Extensions_Database_DataSet_XmlDataSet($xmlFile);
    }

    
    protected function createCsvDataSet($files, $delimiter = ',', $enclosure = '"', $escape = '"') {
        $dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet($delimiter, $enclosure, $escape);
        foreach($files as $table=>$file) {
            $dataSet->addTable($table, $file);
        }
        return $dataSet;
    }

    
    protected function createArrayDataSet(array $data) {
        return new phpunit_ArrayDataSet($data);
    }

    
    protected function loadDataSet(PHPUnit_Extensions_Database_DataSet_IDataSet $dataset) {
        global $DB;

        $structure = phpunit_util::get_tablestructure();

        foreach($dataset->getTableNames() as $tablename) {
            $table = $dataset->getTable($tablename);
            $metadata = $dataset->getTableMetaData($tablename);
            $columns = $metadata->getColumns();

            $doimport = false;
            if (isset($structure[$tablename]['id']) and $structure[$tablename]['id']->auto_increment) {
                $doimport = in_array('id', $columns);
            }

            for($r=0; $r<$table->getRowCount(); $r++) {
                $record = $table->getRow($r);
                if ($doimport) {
                    $DB->import_record($tablename, $record);
                } else {
                    $DB->insert_record($tablename, $record);
                }
            }
            if ($doimport) {
                $DB->get_manager()->reset_sequence(new xmldb_table($tablename));
            }
        }
    }

    
    public function preventResetByRollback() {
        if ($this->testdbtransaction and !$this->testdbtransaction->is_disposed()) {
            $this->testdbtransaction->allow_commit();
            $this->testdbtransaction = null;
        }
    }

    
    public function resetAfterTest($reset = true) {
        $this->resetAfterTest = $reset;
    }

    
    public function getDebuggingMessages() {
        return phpunit_util::get_debugging_messages();
    }

    
    public function resetDebugging() {
        phpunit_util::reset_debugging();
    }

    
    public function assertDebuggingCalled($debugmessage = null, $debuglevel = null, $message = '') {
        $debugging = $this->getDebuggingMessages();
        $debugdisplaymessage = "\n".phpunit_util::display_debugging_messages(true);
        $this->resetDebugging();

        $count = count($debugging);

        if ($count == 0) {
            if ($message === '') {
                $message = 'Expectation failed, debugging() not triggered.';
            }
            $this->fail($message);
        }
        if ($count > 1) {
            if ($message === '') {
                $message = 'Expectation failed, debugging() triggered '.$count.' times.'.$debugdisplaymessage;
            }
            $this->fail($message);
        }
        $this->assertEquals(1, $count);

        $message .= $debugdisplaymessage;
        $debug = reset($debugging);
        if ($debugmessage !== null) {
            $this->assertSame($debugmessage, $debug->message, $message);
        }
        if ($debuglevel !== null) {
            $this->assertSame($debuglevel, $debug->level, $message);
        }
    }

    
    public function assertDebuggingCalledCount($expectedcount, $debugmessages = array(), $debuglevels = array(), $message = '') {
        if (!is_int($expectedcount)) {
            throw new coding_exception('assertDebuggingCalledCount $expectedcount argument should be an integer.');
        }

        $debugging = $this->getDebuggingMessages();
        $message .= "\n".phpunit_util::display_debugging_messages(true);
        $this->resetDebugging();

        $this->assertEquals($expectedcount, count($debugging), $message);

        if ($debugmessages) {
            if (!is_array($debugmessages) || count($debugmessages) != $expectedcount) {
                throw new coding_exception('assertDebuggingCalledCount $debugmessages should contain ' . $expectedcount . ' messages');
            }
            foreach ($debugmessages as $key => $debugmessage) {
                $this->assertSame($debugmessage, $debugging[$key]->message, $message);
            }
        }

        if ($debuglevels) {
            if (!is_array($debuglevels) || count($debuglevels) != $expectedcount) {
                throw new coding_exception('assertDebuggingCalledCount $debuglevels should contain ' . $expectedcount . ' messages');
            }
            foreach ($debuglevels as $key => $debuglevel) {
                $this->assertSame($debuglevel, $debugging[$key]->level, $message);
            }
        }
    }

    
    public function assertDebuggingNotCalled($message = '') {
        $debugging = $this->getDebuggingMessages();
        $count = count($debugging);

        if ($message === '') {
            $message = 'Expectation failed, debugging() was triggered.';
        }
        $message .= "\n".phpunit_util::display_debugging_messages(true);
        $this->resetDebugging();
        $this->assertEquals(0, $count, $message);
    }

    
    public function assertEventLegacyData($expected, \core\event\base $event, $message = '') {
        $legacydata = phpunit_event_mock::testable_get_legacy_eventdata($event);
        if ($message === '') {
            $message = 'Event legacy data does not match expected value.';
        }
        $this->assertEquals($expected, $legacydata, $message);
    }

    
    public function assertEventLegacyLogData($expected, \core\event\base $event, $message = '') {
        $legacydata = phpunit_event_mock::testable_get_legacy_logdata($event);
        if ($message === '') {
            $message = 'Event legacy log data does not match expected value.';
        }
        $this->assertEquals($expected, $legacydata, $message);
    }

    
    public function assertEventContextNotUsed(\core\event\base $event, $message = '') {
                $eventcontext = phpunit_event_mock::testable_get_event_context($event);
        phpunit_event_mock::testable_set_event_context($event, false);
        if ($message === '') {
            $message = 'Event should not use context property of event in any method.';
        }

                $event->get_url();
        $event->get_description();
        $event->get_legacy_eventname();
        phpunit_event_mock::testable_get_legacy_eventdata($event);
        phpunit_event_mock::testable_get_legacy_logdata($event);

                phpunit_event_mock::testable_set_event_context($event, $eventcontext);
    }

    
    public function setCurrentTimeStart() {
        $this->currenttimestart = time();
        return $this->currenttimestart;
    }

    
    public function assertTimeCurrent($time, $message = '') {
        $msg =  ($message === '') ? 'Time is lower that allowed start value' : $message;
        $this->assertGreaterThanOrEqual($this->currenttimestart, $time, $msg);
        $msg =  ($message === '') ? 'Time is in the future' : $message;
        $this->assertLessThanOrEqual(time(), $time, $msg);
    }

    
    public function redirectMessages() {
        return phpunit_util::start_message_redirection();
    }

    
    public function redirectEmails() {
        return phpunit_util::start_phpmailer_redirection();
    }

    
    public function redirectEvents() {
        return phpunit_util::start_event_redirection();
    }

    
    public static function tearDownAfterClass() {
        self::resetAllData();
    }


    
    public static function resetAllData($detectchanges = false) {
        phpunit_util::reset_all_data($detectchanges);
    }

    
    public static function setUser($user = null) {
        global $CFG, $DB;

        if (is_object($user)) {
            $user = clone($user);
        } else if (!$user) {
            $user = new stdClass();
            $user->id = 0;
            $user->mnethostid = $CFG->mnet_localhost_id;
        } else {
            $user = $DB->get_record('user', array('id'=>$user));
        }
        unset($user->description);
        unset($user->access);
        unset($user->preference);

                \core\session\manager::init_empty_session();

        \core\session\manager::set_user($user);
    }

    
    public static function setAdminUser() {
        self::setUser(2);
    }

    
    public static function setGuestUser() {
        self::setUser(1);
    }

    
    public static function setTimezone($servertimezone = 'Australia/Perth', $defaultphptimezone = 'Australia/Perth') {
        global $CFG;
        $CFG->timezone = $servertimezone;
        core_date::phpunit_override_default_php_timezone($defaultphptimezone);
        core_date::set_default_server_timezone();
    }

    
    public static function getDataGenerator() {
        return phpunit_util::get_data_generator();
    }

    
    public function getExternalTestFileUrl($path, $https = false) {
        $path = ltrim($path, '/');
        if ($path) {
            $path = '/'.$path;
        }
        if ($https) {
            if (defined('TEST_EXTERNAL_FILES_HTTPS_URL')) {
                if (!TEST_EXTERNAL_FILES_HTTPS_URL) {
                    $this->markTestSkipped('Tests using external https test files are disabled');
                }
                return TEST_EXTERNAL_FILES_HTTPS_URL.$path;
            }
            return 'https://download.moodle.org/unittest'.$path;
        }

        if (defined('TEST_EXTERNAL_FILES_HTTP_URL')) {
            if (!TEST_EXTERNAL_FILES_HTTP_URL) {
                $this->markTestSkipped('Tests using external http test files are disabled');
            }
            return TEST_EXTERNAL_FILES_HTTP_URL.$path;
        }
        return 'http://download.moodle.org/unittest'.$path;
    }

    
    public function recurseFolders($path, $callback, $fileregexp = '/.*/', $exclude = false, $ignorefolders = array()) {
        $files = scandir($path);

        foreach ($files as $file) {
            $filepath = $path .'/'. $file;
            if (strpos($file, '.') === 0) {
                                continue;
            } else if (is_dir($filepath)) {
                if (!in_array($filepath, $ignorefolders)) {
                    $this->recurseFolders($filepath, $callback, $fileregexp, $exclude, $ignorefolders);
                }
            } else if ($exclude xor preg_match($fileregexp, $filepath)) {
                $this->$callback($filepath);
            }
        }
    }

    
    public function waitForSecond() {
        $starttime = time();
        while (time() == $starttime) {
            usleep(50000);
        }
    }
}
