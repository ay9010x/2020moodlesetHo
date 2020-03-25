<?php




require_once(__DIR__ . '/../../behat/behat_base.php');

use Behat\Testwork\Hook\Scope\BeforeSuiteScope,
    Behat\Testwork\Hook\Scope\AfterSuiteScope,
    Behat\Behat\Hook\Scope\BeforeFeatureScope,
    Behat\Behat\Hook\Scope\AfterFeatureScope,
    Behat\Behat\Hook\Scope\BeforeScenarioScope,
    Behat\Behat\Hook\Scope\AfterScenarioScope,
    Behat\Behat\Hook\Scope\BeforeStepScope,
    Behat\Behat\Hook\Scope\AfterStepScope,
    Behat\Mink\Exception\DriverException as DriverException,
    WebDriver\Exception\NoSuchWindow as NoSuchWindow,
    WebDriver\Exception\UnexpectedAlertOpen as UnexpectedAlertOpen,
    WebDriver\Exception\UnknownError as UnknownError,
    WebDriver\Exception\CurlExec as CurlExec,
    WebDriver\Exception\NoAlertOpenError as NoAlertOpenError;


class behat_hooks extends behat_base {

    
    protected static $lastbrowsersessionstart = 0;

    
    protected static $initprocessesfinished = false;

    
    protected static $currentstepexception = null;

    
    protected static $faildumpdirname = false;

    
    protected static $timings = array();

    
    public static function before_suite_hook(BeforeSuiteScope $scope) {
        try {
            self::before_suite($scope);
        } catch (behat_stop_exception $e) {
            echo $e->getMessage() . PHP_EOL;
            exit(1);
        }
    }

    
    public static function before_suite(BeforeSuiteScope $scope) {
        global $CFG;

                                define('BEHAT_TEST', 1);

        define('CLI_SCRIPT', 1);
                require_once(__DIR__ . '/../../../config.php');

                require_once(__DIR__ . '/../../behat/classes/behat_command.php');
        require_once(__DIR__ . '/../../behat/classes/behat_selectors.php');
        require_once(__DIR__ . '/../../behat/classes/behat_context_helper.php');
        require_once(__DIR__ . '/../../behat/classes/util.php');
        require_once(__DIR__ . '/../../testing/classes/test_lock.php');
        require_once(__DIR__ . '/../../testing/classes/nasty_strings.php');

                        
        if (!behat_util::is_test_mode_enabled()) {
            throw new behat_stop_exception('Behat only can run if test mode is enabled. More info in ' .
                behat_command::DOCS_URL . '#Running_tests');
        }

                        behat_util::clean_tables_updated_by_scenario_list();
        behat_util::reset_all_data();

                behat_util::check_server_status();

                if (!behat_util::is_test_data_updated()) {
            $commandpath = 'php admin/tool/behat/cli/init.php';
            throw new behat_stop_exception("Your behat test site is outdated, please run\n\n    " .
                    $commandpath . "\n\nfrom your moodle dirroot to drop and install the behat test site again.");
        }
                test_lock::acquire('behat');

                if (!empty($CFG->behat_restart_browser_after)) {
                        self::$lastbrowsersessionstart = time();
        }

        if (!empty($CFG->behat_faildump_path) && !is_writable($CFG->behat_faildump_path)) {
            throw new behat_stop_exception('You set $CFG->behat_faildump_path to a non-writable directory');
        }

                if (extension_loaded('pcntl')) {
            $disabled = explode(',', ini_get('disable_functions'));
            if (!in_array('pcntl_signal', $disabled)) {
                declare(ticks = 1);
            }
        }
    }

    
    public static function before_feature(BeforeFeatureScope $scope) {
        if (!defined('BEHAT_FEATURE_TIMING_FILE')) {
            return;
        }
        $file = $scope->getFeature()->getFile();
        self::$timings[$file] = microtime(true);
    }

    
    public static function after_feature(AfterFeatureScope $scope) {
        if (!defined('BEHAT_FEATURE_TIMING_FILE')) {
            return;
        }
        $file = $scope->getFeature()->getFile();
        self::$timings[$file] = microtime(true) - self::$timings[$file];
                if (self::$timings[$file] < 1) {
            unset(self::$timings[$file]);
        }
    }

    
    public static function after_suite(AfterSuiteScope $scope) {
        if (!defined('BEHAT_FEATURE_TIMING_FILE')) {
            return;
        }
        $realroot = realpath(__DIR__.'/../../../').'/';
        foreach (self::$timings as $k => $v) {
            $new = str_replace($realroot, '', $k);
            self::$timings[$new] = round($v, 1);
            unset(self::$timings[$k]);
        }
        if ($existing = @json_decode(file_get_contents(BEHAT_FEATURE_TIMING_FILE), true)) {
            self::$timings = array_merge($existing, self::$timings);
        }
        arsort(self::$timings);
        @file_put_contents(BEHAT_FEATURE_TIMING_FILE, json_encode(self::$timings, JSON_PRETTY_PRINT));
    }

    
    public function before_scenario_hook(BeforeScenarioScope $scope) {
        try {
            $this->before_scenario($scope);
        } catch (behat_stop_exception $e) {
            echo $e->getMessage() . PHP_EOL;
            exit(1);
        }
    }

    
    public function before_scenario(BeforeScenarioScope $scope) {
        global $DB, $SESSION, $CFG;

                if (!defined('BEHAT_TEST') ||
               !defined('BEHAT_SITE_RUNNING') ||
               php_sapi_name() != 'cli' ||
               !behat_util::is_test_mode_enabled() ||
               !behat_util::is_test_site()) {
            throw new behat_stop_exception('Behat only can modify the test database and the test dataroot!');
        }

        $moreinfo = 'More info in ' . behat_command::DOCS_URL . '#Running_tests';
        $driverexceptionmsg = 'Selenium server is not running, you need to start it to run tests that involve Javascript. ' . $moreinfo;
        try {
            $session = $this->getSession();
        } catch (CurlExec $e) {
                                    throw new behat_stop_exception($driverexceptionmsg);
        } catch (DriverException $e) {
            throw new behat_stop_exception($driverexceptionmsg);
        } catch (UnknownError $e) {
                        throw new behat_stop_exception($e->getMessage());
        }

                if (self::is_first_scenario()) {
            behat_selectors::register_moodle_selectors($session);
            behat_context_helper::set_session($scope->getEnvironment());
        }

                $session->reset();

                \core\session\manager::init_empty_session();

                        $errorlevel = error_reporting();
        error_reporting($errorlevel & ~E_NOTICE & ~E_WARNING);
        behat_util::reset_all_data();
        error_reporting($errorlevel);

                $user = $DB->get_record('user', array('username' => 'admin'));
        \core\session\manager::set_user($user);

                if (!empty($CFG->behat_restart_browser_after) && $this->running_javascript()) {
            $now = time();
            if (self::$lastbrowsersessionstart + $CFG->behat_restart_browser_after < $now) {
                $session->restart();
                self::$lastbrowsersessionstart = $now;
            }
        }

                try {
                        $session->visit($this->locate_path('/'));
        } catch (UnknownError $e) {
            throw new behat_stop_exception($e->getMessage());
        }


                if (self::is_first_scenario()) {
            $notestsiteexception = new behat_stop_exception('The base URL (' . $CFG->wwwroot . ') is not a behat test site, ' .
                'ensure you started the built-in web server in the correct directory or your web server is correctly started and set up');
            $this->find("xpath", "//head/child::title[normalize-space(.)='" . behat_util::BEHATSITENAME . "']", $notestsiteexception);

            self::$initprocessesfinished = true;
        }
                $this->resize_window('medium');
    }

    
    public function before_step_javascript(BeforeStepScope $scope) {
        self::$currentstepexception = null;

                if ($this->running_javascript()) {
            try {
                $this->wait_for_pending_js();
            } catch (Exception $e) {
                self::$currentstepexception = $e;
            }
        }
    }

    
    public function after_step_javascript(AfterStepScope $scope) {
        global $CFG, $DB;

                if (!empty($CFG->behat_faildump_path) &&
            $scope->getTestResult()->getResultCode() === Behat\Testwork\Tester\Result\TestResult::FAILED) {
            $this->take_contentdump($scope);
        }

                                if (($scope->getTestResult() instanceof \Behat\Behat\Tester\Result\ExecutedStepResult) &&
            $scope->getTestResult()->hasException()) {
            if ($DB && $DB->is_transaction_started()) {
                $DB->force_transaction_rollback();
            }
        }

                if (!$this->running_javascript()) {
            return;
        }

                if (!empty($CFG->behat_faildump_path) &&
            $scope->getTestResult()->getResultCode() === Behat\Testwork\Tester\Result\TestResult::FAILED) {
            $this->take_screenshot($scope);
        }

        try {
            $this->wait_for_pending_js();
            self::$currentstepexception = null;
        } catch (UnexpectedAlertOpen $e) {
            self::$currentstepexception = $e;

                                                try {
                $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
            } catch (Exception $e) {
                            }
        } catch (Exception $e) {
            self::$currentstepexception = $e;
        }
    }

    
    public function after_scenario_switchwindow(AfterScenarioScope $scope) {
        for ($count = 0; $count < self::EXTENDED_TIMEOUT; $count++) {
            try {
                $this->getSession()->restart();
                break;
            } catch (DriverException $e) {
                                sleep(self::TIMEOUT);
            }
        }
                    }

    
    protected function get_run_faildump_dir() {
        return self::$faildumpdirname;
    }

    
    protected function take_screenshot(AfterStepScope $scope) {
                if (!$this->running_javascript()) {
            return false;
        }

                                        try {
            list ($dir, $filename) = $this->get_faildump_filename($scope, 'png');
            $this->saveScreenshot($filename, $dir);
        } catch (Exception $e) {
                        list ($dir, $filename) = $this->get_faildump_filename($scope, 'txt');
            $message = "Could not save screenshot due to an error\n" . $e->getMessage();
            file_put_contents($dir . DIRECTORY_SEPARATOR . $filename, $message);
        }
    }

    
    protected function take_contentdump(AfterStepScope $scope) {
        list ($dir, $filename) = $this->get_faildump_filename($scope, 'html');

        try {
                        $content = $this->getSession()->getPage()->getContent();
        } catch (Exception $e) {
                        $content = "Could not save contentdump due to an error\n" . $e->getMessage();
        }
        file_put_contents($dir . DIRECTORY_SEPARATOR . $filename, $content);
    }

    
    protected function get_faildump_filename(AfterStepScope $scope, $filetype) {
        global $CFG;

                if (!$faildumpdir = self::get_run_faildump_dir()) {
            $faildumpdir = self::$faildumpdirname = date('Ymd_His');

            $dir = $CFG->behat_faildump_path . DIRECTORY_SEPARATOR . $faildumpdir;

            if (!is_dir($dir) && !mkdir($dir, $CFG->directorypermissions, true)) {
                                throw new Exception('No directories can be created inside $CFG->behat_faildump_path, check the directory permissions.');
            }
        } else {
                        $dir = $CFG->behat_faildump_path . DIRECTORY_SEPARATOR . $faildumpdir;
        }

                        $filename = $scope->getFeature()->getTitle() . '_' . $scope->getStep()->getText();
        $filename = preg_replace('/([^a-zA-Z0-9\_]+)/', '-', $filename);

                        $filename = substr($filename, 0, 245) . '_' . $scope->getStep()->getLine() . '.' . $filetype;

        return array($dir, $filename);
    }

    
    public function i_look_for_exceptions() {
                if (!is_null(self::$currentstepexception)) {
            throw self::$currentstepexception;
        }

        $this->look_for_exceptions();
    }

    
    protected static function is_first_scenario() {
        return !(self::$initprocessesfinished);
    }
}


class behat_stop_exception extends \Exception{}