<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../../testing/classes/util.php');
require_once(__DIR__ . '/behat_command.php');
require_once(__DIR__ . '/behat_config_manager.php');

require_once(__DIR__ . '/../../filelib.php');


class behat_util extends testing_util {

    
    const BEHATSITENAME = "Acceptance test site";

    
    protected static $datarootskiponreset = array('.', '..', 'behat', 'behattestdir.txt');

    
    protected static $datarootskipondrop = array('.', '..', 'lock');

    
    public static function install_site() {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/user/lib.php');
        if (!defined('BEHAT_UTIL')) {
            throw new coding_exception('This method can be only used by Behat CLI tool');
        }

        $tables = $DB->get_tables(false);
        if (!empty($tables)) {
            behat_error(BEHAT_EXITCODE_INSTALLED);
        }

                self::reset_dataroot();

        $options = array();
        $options['adminuser'] = 'admin';
        $options['adminpass'] = 'admin';
        $options['fullname'] = self::BEHATSITENAME;
        $options['shortname'] = self::BEHATSITENAME;

        install_cli_database($options, false);

                        self::save_original_data_files();

        $frontpagesummary = new admin_setting_special_frontpagedesc();
        $frontpagesummary->write_setting(self::BEHATSITENAME);

                $user = $DB->get_record('user', array('username' => 'admin'));
        $user->email = 'moodle@example.com';
        $user->firstname = 'Admin';
        $user->lastname = 'User';
        $user->city = 'Perth';
        $user->country = 'AU';
        user_update_user($user, false);

                $DB->set_field('message_processors', 'enabled', '0', array('name' => 'email'));

                set_config('debug', DEBUG_DEVELOPER);
        set_config('debugdisplay', 1);

                set_config('noemailever', 1);

                set_config('cronclionly', 0);

                set_config('autosavefrequency', '604800', 'editor_atto');

                set_config('noreplyaddress', 'noreply@example.com');

                self::store_versions_hash();

                self::store_database_state();
    }

    
    public static function drop_site() {

        if (!defined('BEHAT_UTIL')) {
            throw new coding_exception('This method can be only used by Behat CLI tool');
        }

        self::reset_dataroot();
        self::drop_dataroot();
        self::drop_database(true);
    }

    
    public static function check_server_status() {
        global $CFG;

        $url = $CFG->behat_wwwroot . '/admin/tool/behat/tests/behat/fixtures/environment.php';

                $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $statuscode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statuscode !== 200 || empty($result) || (!$result = json_decode($result, true))) {

            behat_error (BEHAT_EXITCODE_REQUIREMENT, $CFG->behat_wwwroot . ' is not available, ensure you specified ' .
                'correct url and that the server is set up and started.' . PHP_EOL . ' More info in ' .
                behat_command::DOCS_URL . '#Running_tests' . PHP_EOL);
        }

                $clienv = self::get_environment();
        if ($result != $clienv) {
            $output = 'Differences detected between cli and webserver...'.PHP_EOL;
            foreach ($result as $key => $version) {
                if ($clienv[$key] != $version) {
                    $output .= ' ' . $key . ': ' . PHP_EOL;
                    $output .= ' - web server: ' . $version . PHP_EOL;
                    $output .= ' - cli: ' . $clienv[$key] . PHP_EOL;
                }
            }
            echo $output;
            ob_flush();
        }
    }

    
    protected static function test_environment_problem() {
        global $CFG, $DB;

        if (!defined('BEHAT_UTIL')) {
            throw new coding_exception('This method can be only used by Behat CLI tool');
        }

        if (!self::is_test_site()) {
            behat_error(1, 'This is not a behat test site!');
        }

        $tables = $DB->get_tables(false);
        if (empty($tables)) {
            behat_error(BEHAT_EXITCODE_INSTALL, '');
        }

        if (!self::is_test_data_updated()) {
            behat_error(BEHAT_EXITCODE_REINSTALL, 'The test environment was initialised for a different version');
        }
    }

    
    public static function start_test_mode() {
        global $CFG;

        if (!defined('BEHAT_UTIL')) {
            throw new coding_exception('This method can be only used by Behat CLI tool');
        }

                if ($errorcode = behat_command::behat_setup_problem()) {
            exit($errorcode);
        }

                self::test_environment_problem();

                behat_config_manager::update_config_file();

        if (self::is_test_mode_enabled()) {
            return;
        }

        $contents = '$CFG->behat_wwwroot, $CFG->behat_prefix and $CFG->behat_dataroot' .
            ' are currently used as $CFG->wwwroot, $CFG->prefix and $CFG->dataroot';
        $filepath = self::get_test_file_path();
        if (!file_put_contents($filepath, $contents)) {
            behat_error(BEHAT_EXITCODE_PERMISSIONS, 'File ' . $filepath . ' can not be created');
        }
    }

    
    public static function get_behat_status() {

        if (!defined('BEHAT_UTIL')) {
            throw new coding_exception('This method can be only used by Behat CLI tool');
        }

                if ($errorcode = behat_command::behat_setup_problem()) {
            return $errorcode;
        }

                self::test_environment_problem();
    }

    
    public static function stop_test_mode() {

        if (!defined('BEHAT_UTIL')) {
            throw new coding_exception('This method can be only used by Behat CLI tool');
        }

        $testenvfile = self::get_test_file_path();

        if (!self::is_test_mode_enabled()) {
            echo "Test environment was already disabled\n";
        } else {
            if (!unlink($testenvfile)) {
                behat_error(BEHAT_EXITCODE_PERMISSIONS, 'Can not delete test environment file');
            }
        }
    }

    
    public static function is_test_mode_enabled() {

        $testenvfile = self::get_test_file_path();
        if (file_exists($testenvfile)) {
            return true;
        }

        return false;
    }

    
    protected final static function get_test_file_path() {
        return behat_command::get_behat_dir() . '/test_environment_enabled.txt';
    }

    
    public static function reset_all_data() {
                self::reset_database();

                self::reset_dataroot();

                accesslib_clear_all_caches(true);
                nasty_strings::reset_used_strings();

        filter_manager::reset_caches();

                if (class_exists('format_base')) {
                        format_base::reset_course_cache(0);
        }
        get_fast_modinfo(0, 0, true);

                self::get_data_generator()->reset();

                        initialise_cfg();
    }
}
