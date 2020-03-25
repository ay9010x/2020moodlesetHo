<?php



require_once(__DIR__.'/../../testing/classes/util.php');


class phpunit_util extends testing_util {
    
    public static $lastdbwrites = null;

    
    protected static $globals = array();

    
    protected static $debuggings = array();

    
    protected static $messagesink = null;

    
    protected static $phpmailersink = null;

    
    protected static $eventsink = null;

    
    protected static $datarootskiponreset = array('.', '..', 'phpunittestdir.txt', 'phpunit', '.htaccess');

    
    protected static $datarootskipondrop = array('.', '..', 'lock', 'webrunner.xml');

    
    public static function initialise_cfg() {
        global $DB;
        $dbhash = false;
        try {
            $dbhash = $DB->get_field('config', 'value', array('name'=>'phpunittest'));
        } catch (Exception $e) {
                        initialise_cfg();
            return;
        }
        if ($dbhash !== core_component::get_all_versions_hash()) {
                        return;
        }
                initialise_cfg();
    }

    
    public static function reset_all_data($detectchanges = false) {
        global $DB, $CFG, $USER, $SITE, $COURSE, $PAGE, $OUTPUT, $SESSION, $FULLME;

                self::stop_message_redirection();

                self::stop_event_redirection();

                                        self::start_phpmailer_redirection();

                
                self::display_debugging_messages();
        self::reset_debugging();

                $DB = self::get_global_backup('DB');

        if ($DB->is_transaction_started()) {
                        $DB->force_transaction_rollback();
        }

        $resetdb = self::reset_database();
        $localename = self::get_locale_name();
        $warnings = array();

        if ($detectchanges === true) {
            if ($resetdb) {
                $warnings[] = 'Warning: unexpected database modification, resetting DB state';
            }

            $oldcfg = self::get_global_backup('CFG');
            $oldsite = self::get_global_backup('SITE');
            foreach($CFG as $k=>$v) {
                if (!property_exists($oldcfg, $k)) {
                    $warnings[] = 'Warning: unexpected new $CFG->'.$k.' value';
                } else if ($oldcfg->$k !== $CFG->$k) {
                    $warnings[] = 'Warning: unexpected change of $CFG->'.$k.' value';
                }
                unset($oldcfg->$k);

            }
            if ($oldcfg) {
                foreach($oldcfg as $k=>$v) {
                    $warnings[] = 'Warning: unexpected removal of $CFG->'.$k;
                }
            }

            if ($USER->id != 0) {
                $warnings[] = 'Warning: unexpected change of $USER';
            }

            if ($COURSE->id != $oldsite->id) {
                $warnings[] = 'Warning: unexpected change of $COURSE';
            }

            if ($FULLME !== self::get_global_backup('FULLME')) {
                $warnings[] = 'Warning: unexpected change of $FULLME';
            }

            if (setlocale(LC_TIME, 0) !== $localename) {
                $warnings[] = 'Warning: unexpected change of locale';
            }
        }

        if (ini_get('max_execution_time') != 0) {
                                    
            if ($detectchanges !== false) {
                $warnings[] = 'Warning: max_execution_time was changed to '.ini_get('max_execution_time');
            }
            set_time_limit(0);
        }

                $_SERVER = self::get_global_backup('_SERVER');
        $CFG = self::get_global_backup('CFG');
        $SITE = self::get_global_backup('SITE');
        $FULLME = self::get_global_backup('FULLME');
        $_GET = array();
        $_POST = array();
        $_FILES = array();
        $_REQUEST = array();
        $COURSE = $SITE;

                $OUTPUT = new bootstrap_renderer();
        $PAGE = new moodle_page();
        $FULLME = null;
        $ME = null;
        $SCRIPT = null;

                \core\session\manager::init_empty_session();

                \core\event\manager::phpunit_reset();
        accesslib_clear_all_caches(true);
        get_string_manager()->reset_caches(true);
        reset_text_filters_cache(true);
        events_get_handlers('reset');
        core_text::reset_caches();
        get_message_processors(false, true, true);
        filter_manager::reset_caches();
        core_filetypes::reset_caches();
        \core_search\manager::clear_static();
        core_user::reset_caches();

                if (class_exists('\availability_date\condition', false)) {
            \availability_date\condition::set_current_time_for_test(0);
        }

                core_user::reset_internal_users();

        
                if (class_exists('format_base')) {
                        format_base::reset_course_cache(0);
        }
        get_fast_modinfo(0, 0, true);

                if (class_exists('core_plugin_manager')) {
            core_plugin_manager::reset_caches(true);
        }
        if (class_exists('\core\update\checker')) {
            \core\update\checker::reset_caches(true);
        }

                if (class_exists('restore_section_structure_step')) {
            restore_section_structure_step::reset_caches();
        }

                self::reset_dataroot();

                $CFG = self::get_global_backup('CFG');

                self::get_data_generator()->reset();

                error_reporting($CFG->debug);

                core_date::phpunit_reset();

                setlocale(LC_TIME, $localename);

                get_log_manager(true);

                if (self::$lastdbwrites != $DB->perf_get_writes()) {
            error_log('Unexpected DB writes in phpunit_util::reset_all_data()');
            self::$lastdbwrites = $DB->perf_get_writes();
        }

        if ($warnings) {
            $warnings = implode("\n", $warnings);
            trigger_error($warnings, E_USER_WARNING);
        }
    }

    
    public static function reset_database() {
        global $DB;

        if (!is_null(self::$lastdbwrites) and self::$lastdbwrites == $DB->perf_get_writes()) {
            return false;
        }

        if (!parent::reset_database()) {
            return false;
        }

        self::$lastdbwrites = $DB->perf_get_writes();

        return true;
    }

    
    public static function bootstrap_init() {
        global $CFG, $SITE, $DB, $FULLME;

                self::$globals['_SERVER'] = $_SERVER;
        self::$globals['CFG'] = clone($CFG);
        self::$globals['SITE'] = clone($SITE);
        self::$globals['DB'] = $DB;
        self::$globals['FULLME'] = $FULLME;

                self::reset_all_data();
    }

    
    public static function bootstrap_moodle_info() {
        echo self::get_site_info();
    }

    
    public static function get_global_backup($name) {
        if ($name === 'DB') {
                                    return self::$globals['DB'];
        }
        if (isset(self::$globals[$name])) {
            if (is_object(self::$globals[$name])) {
                $return = clone(self::$globals[$name]);
                return $return;
            } else {
                return self::$globals[$name];
            }
        }
        return null;
    }

    
    public static function testing_ready_problem() {
        global $DB;

        $localename = self::get_locale_name();
        if (setlocale(LC_TIME, $localename) === false) {
            return array(PHPUNIT_EXITCODE_CONFIGERROR, "Required locale '$localename' is not installed.");
        }

        if (!self::is_test_site()) {
                        return array(PHPUNIT_EXITCODE_CONFIGERROR, 'Can not use database for testing, try different prefix');
        }

        $tables = $DB->get_tables(false);
        if (empty($tables)) {
            return array(PHPUNIT_EXITCODE_INSTALL, '');
        }

        if (!self::is_test_data_updated()) {
            return array(PHPUNIT_EXITCODE_REINSTALL, '');
        }

        return array(0, '');
    }

    
    public static function drop_site($displayprogress = false) {
        global $DB, $CFG;

        if (!self::is_test_site()) {
            phpunit_bootstrap_error(PHPUNIT_EXITCODE_CONFIGERROR, 'Can not drop non-test site!!');
        }

                if ($displayprogress) {
            echo "Purging dataroot:\n";
        }

        self::reset_dataroot();
        testing_initdataroot($CFG->dataroot, 'phpunit');
        self::drop_dataroot();

                self::drop_database($displayprogress);
    }

    
    public static function install_site() {
        global $DB, $CFG;

        if (!self::is_test_site()) {
            phpunit_bootstrap_error(PHPUNIT_EXITCODE_CONFIGERROR, 'Can not install on non-test site!!');
        }

        if ($DB->get_tables()) {
            list($errorcode, $message) = self::testing_ready_problem();
            if ($errorcode) {
                phpunit_bootstrap_error(PHPUNIT_EXITCODE_REINSTALL, 'Database tables already present, Moodle PHPUnit test environment can not be initialised');
            } else {
                phpunit_bootstrap_error(0, 'Moodle PHPUnit test environment is already initialised');
            }
        }

        $options = array();
        $options['adminpass'] = 'admin';
        $options['shortname'] = 'phpunit';
        $options['fullname'] = 'PHPUnit test site';

        install_cli_database($options, false);

                $DB->set_field('user', 'email', 'admin@example.com', array('username' => 'admin'));

                set_config('enabled_stores', '', 'tool_log');

                        self::save_original_data_files();

                self::store_versions_hash();

                self::store_database_state();
    }

    
    public static function build_config_file() {
        global $CFG;

        $template = '
        <testsuite name="@component@_testsuite">
            <directory suffix="_test.php">@dir@</directory>
        </testsuite>';
        $data = file_get_contents("$CFG->dirroot/phpunit.xml.dist");

        $suites = '';

        $plugintypes = core_component::get_plugin_types();
        ksort($plugintypes);
        foreach ($plugintypes as $type=>$unused) {
            $plugs = core_component::get_plugin_list($type);
            ksort($plugs);
            foreach ($plugs as $plug=>$fullplug) {
                if (!file_exists("$fullplug/tests/")) {
                    continue;
                }
                $dir = substr($fullplug, strlen($CFG->dirroot)+1);
                $dir .= '/tests';
                $component = $type.'_'.$plug;

                $suite = str_replace('@component@', $component, $template);
                $suite = str_replace('@dir@', $dir, $suite);

                $suites .= $suite;
            }
        }
                                $sequencestart = 100000 + mt_rand(0, 99) * 1000;

        $data = preg_replace('|<!--@plugin_suites_start@-->.*<!--@plugin_suites_end@-->|s', $suites, $data, 1);
        $data = str_replace(
            '<const name="PHPUNIT_SEQUENCE_START" value=""/>',
            '<const name="PHPUNIT_SEQUENCE_START" value="' . $sequencestart . '"/>',
            $data);

        $result = false;
        if (is_writable($CFG->dirroot)) {
            if ($result = file_put_contents("$CFG->dirroot/phpunit.xml", $data)) {
                testing_fix_file_permissions("$CFG->dirroot/phpunit.xml");
            }
        }

                $data = str_replace('lib/phpunit/', $CFG->dirroot.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'phpunit'.DIRECTORY_SEPARATOR, $data);
        $data = preg_replace('|<directory suffix="_test.php">([^<]+)</directory>|',
            '<directory suffix="_test.php">'.$CFG->dirroot.(DIRECTORY_SEPARATOR === '\\' ? '\\\\' : DIRECTORY_SEPARATOR).'$1</directory>',
            $data);
        file_put_contents("$CFG->dataroot/phpunit/webrunner.xml", $data);
        testing_fix_file_permissions("$CFG->dataroot/phpunit/webrunner.xml");

        return (bool)$result;
    }

    
    public static function build_component_config_files() {
        global $CFG;

        $template = '
        <testsuites>
            <testsuite name="@component@_testsuite">
                <directory suffix="_test.php">.</directory>
            </testsuite>
        </testsuites>';

                                $sequencestart = 100000 + mt_rand(0, 99) * 1000;

                $ftemplate = file_get_contents("$CFG->dirroot/phpunit.xml.dist");
        $ftemplate = preg_replace('|<!--All core suites.*</testsuites>|s', '<!--@component_suite@-->', $ftemplate);

                $components = tests_finder::get_components_with_tests('phpunit');

                foreach ($components as $cname => $cpath) {
                        $ctemplate = $template;
            $ctemplate = str_replace('@component@', $cname, $ctemplate);

                        $fcontents = str_replace('<!--@component_suite@-->', $ctemplate, $ftemplate);
            $fcontents = str_replace(
                '<const name="PHPUNIT_SEQUENCE_START" value=""/>',
                '<const name="PHPUNIT_SEQUENCE_START" value="' . $sequencestart . '"/>',
                $fcontents);

                        $level = substr_count(str_replace('\\', '/', $cpath), '/') - substr_count(str_replace('\\', '/', $CFG->dirroot), '/');
            $fcontents = str_replace('lib/phpunit/', str_repeat('../', $level).'lib/phpunit/', $fcontents);

                        $result = false;
            if (is_writable($cpath)) {
                if ($result = (bool)file_put_contents("$cpath/phpunit.xml", $fcontents)) {
                    testing_fix_file_permissions("$cpath/phpunit.xml");
                }
            }
                        if (!$result) {
                phpunit_bootstrap_error(PHPUNIT_EXITCODE_CONFIGWARNING, "Can not create $cpath/phpunit.xml configuration file, verify dir permissions");
            }
        }
    }

    
    public static function debugging_triggered($message, $level, $from) {
                        $backtrace = debug_backtrace();

        foreach ($backtrace as $bt) {
            if (isset($bt['object']) and is_object($bt['object'])
                    && $bt['object'] instanceof PHPUnit_Framework_TestCase) {
                $debug = new stdClass();
                $debug->message = $message;
                $debug->level   = $level;
                $debug->from    = $from;

                self::$debuggings[] = $debug;

                return true;
            }
        }
        return false;
    }

    
    public static function reset_debugging() {
        self::$debuggings = array();
        set_debugging(DEBUG_DEVELOPER);
    }

    
    public static function get_debugging_messages() {
        return self::$debuggings;
    }

    
    public static function display_debugging_messages($return = false) {
        if (empty(self::$debuggings)) {
            return false;
        }

        $debugstring = '';
        foreach(self::$debuggings as $debug) {
            $debugstring .= 'Debugging: ' . $debug->message . "\n" . trim($debug->from) . "\n";
        }

        if ($return) {
            return $debugstring;
        }
        echo $debugstring;
        return true;
    }

    
    public static function start_message_redirection() {
        if (self::$messagesink) {
            self::stop_message_redirection();
        }
        self::$messagesink = new phpunit_message_sink();
        return self::$messagesink;
    }

    
    public static function stop_message_redirection() {
        self::$messagesink = null;
    }

    
    public static function is_redirecting_messages() {
        return !empty(self::$messagesink);
    }

    
    public static function message_sent($message) {
        if (self::$messagesink) {
            self::$messagesink->add_message($message);
        }
    }

    
    public static function start_phpmailer_redirection() {
        if (self::$phpmailersink) {
                        self::$phpmailersink->clear();
        } else {
            self::$phpmailersink = new phpunit_phpmailer_sink();
        }
        return self::$phpmailersink;
    }

    
    public static function stop_phpmailer_redirection() {
        self::$phpmailersink = null;
    }

    
    public static function is_redirecting_phpmailer() {
        return !empty(self::$phpmailersink);
    }

    
    public static function phpmailer_sent($message) {
        if (self::$phpmailersink) {
            self::$phpmailersink->add_message($message);
        }
    }

    
    public static function start_event_redirection() {
        if (self::$eventsink) {
            self::stop_event_redirection();
        }
        self::$eventsink = new phpunit_event_sink();
        return self::$eventsink;
    }

    
    public static function stop_event_redirection() {
        self::$eventsink = null;
    }

    
    public static function is_redirecting_events() {
        return !empty(self::$eventsink);
    }

    
    public static function event_triggered(\core\event\base $event) {
        if (self::$eventsink) {
            self::$eventsink->add_event($event);
        }
    }

    
    protected static function get_locale_name() {
        global $CFG;
        if ($CFG->ostype === 'WINDOWS') {
            return 'English_Australia.1252';
        } else {
            return 'en_AU.UTF-8';
        }
    }
}
