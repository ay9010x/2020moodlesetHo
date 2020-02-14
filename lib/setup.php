<?php





global $CFG; 
if (!isset($CFG)) {
    if (defined('PHPUNIT_TEST') and PHPUNIT_TEST) {
        echo('There is a missing "global $CFG;" at the beginning of the config.php file.'."\n");
        exit(1);
    } else {
                exit(1);
    }
}

$CFG->dirroot = dirname(dirname(__FILE__));

if (!isset($CFG->directorypermissions)) {
    $CFG->directorypermissions = 02777;      }
if (!isset($CFG->filepermissions)) {
    $CFG->filepermissions = ($CFG->directorypermissions & 0666); }
if (!isset($CFG->umaskpermissions)) {
    $CFG->umaskpermissions = (($CFG->directorypermissions & 0777) ^ 0777);
}
umask($CFG->umaskpermissions);

if (defined('BEHAT_SITE_RUNNING')) {
    
} else if (!empty($CFG->behat_wwwroot) or !empty($CFG->behat_dataroot) or !empty($CFG->behat_prefix)) {
            require_once(__DIR__ . '/../lib/behat/lib.php');

        behat_update_vars_for_process();

    if (behat_is_test_site()) {
        clearstatcache();

                        behat_check_config_vars();

                if (!file_exists("$CFG->behat_dataroot/behattestdir.txt")) {
            if ($dh = opendir($CFG->behat_dataroot)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file === 'behat' or $file === '.' or $file === '..' or $file === '.DS_Store' or is_numeric($file)) {
                        continue;
                    }
                    behat_error(BEHAT_EXITCODE_CONFIG, "$CFG->behat_dataroot directory is not empty, ensure this is the " .
                        "directory where you want to install behat test dataroot");
                }
                closedir($dh);
                unset($dh);
                unset($file);
            }

            if (defined('BEHAT_UTIL')) {
                                testing_initdataroot($CFG->behat_dataroot, 'behat');
            } else {
                behat_error(BEHAT_EXITCODE_INSTALL);
            }
        }

        if (!defined('BEHAT_UTIL') and !defined('BEHAT_TEST')) {
                        if (!file_exists($CFG->behat_dataroot . '/behat/test_environment_enabled.txt')) {
                behat_error(BEHAT_EXITCODE_CONFIG, 'Behat is configured but not enabled on this test site.');
            }
        }

                                                                define('BEHAT_SITE_RUNNING', true);

                behat_clean_init_config();

                $CFG->wwwroot = $CFG->behat_wwwroot;
        $CFG->prefix = $CFG->behat_prefix;
        $CFG->dataroot = $CFG->behat_dataroot;
    }
}

if (!isset($CFG->dataroot)) {
    if (isset($_SERVER['REMOTE_ADDR'])) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 503 Service Unavailable');
    }
    echo('Fatal error: $CFG->dataroot is not specified in config.php! Exiting.'."\n");
    exit(1);
}
$CFG->dataroot = realpath($CFG->dataroot);
if ($CFG->dataroot === false) {
    if (isset($_SERVER['REMOTE_ADDR'])) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 503 Service Unavailable');
    }
    echo('Fatal error: $CFG->dataroot is not configured properly, directory does not exist or is not accessible! Exiting.'."\n");
    exit(1);
} else if (!is_writable($CFG->dataroot)) {
    if (isset($_SERVER['REMOTE_ADDR'])) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 503 Service Unavailable');
    }
    echo('Fatal error: $CFG->dataroot is not writable, admin has to fix directory permissions! Exiting.'."\n");
    exit(1);
}

if (!isset($CFG->wwwroot) or $CFG->wwwroot === 'http://example.com/moodle') {
    if (isset($_SERVER['REMOTE_ADDR'])) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 503 Service Unavailable');
    }
    echo('Fatal error: $CFG->wwwroot is not configured! Exiting.'."\n");
    exit(1);
}

if (!isset($CFG->prefix)) {
    $CFG->prefix = '';
}

if (!isset($CFG->admin)) {       $CFG->admin = 'admin';   }

$CFG->libdir = $CFG->dirroot .'/lib';

if (!isset($CFG->tempdir)) {
    $CFG->tempdir = "$CFG->dataroot/temp";
}

if (!isset($CFG->cachedir)) {
    $CFG->cachedir = "$CFG->dataroot/cache";
}

if (!isset($CFG->localcachedir)) {
    $CFG->localcachedir = "$CFG->dataroot/localcache";
}

if (!isset($CFG->langotherroot)) {
	        $CFG->langotherroot = $CFG->dirroot.'/lang';
}

if (!isset($CFG->langlocalroot)) {
		    $CFG->langlocalroot = $CFG->dirroot.'/lang';
}

if (!isset($_SERVER['REMOTE_ADDR']) && isset($_SERVER['argv'][0])) {
        if (!defined('ABORT_AFTER_CONFIG') and !defined('ABORT_AFTER_CONFIG_CANCEL')) {
        chdir(dirname($_SERVER['argv'][0]));
    }
}

ini_set('precision', 14); ini_set('serialize_precision', 17); 
if (!defined('NO_DEBUG_DISPLAY')) {
    if (defined('AJAX_SCRIPT') and AJAX_SCRIPT) {
                        define('NO_DEBUG_DISPLAY', true);
    } else {
        define('NO_DEBUG_DISPLAY', false);
    }
}

if (!defined('NO_OUTPUT_BUFFERING')) {
    define('NO_OUTPUT_BUFFERING', false);
}

if (!defined('PHPUNIT_TEST')) {
    define('PHPUNIT_TEST', false);
}

if (!defined('MDL_PERF_TEST')) {
    define('MDL_PERF_TEST', false);
} else {
        if (!defined('MDL_PERF')) {
        define('MDL_PERF', true);
    }
    if (!defined('MDL_PERFDB')) {
        define('MDL_PERFDB', true);
    }
    if (!defined('MDL_PERFTOFOOT')) {
        define('MDL_PERFTOFOOT', true);
    }
}

if (!defined('CACHE_DISABLE_ALL')) {
    define('CACHE_DISABLE_ALL', false);
}

if (!defined('CACHE_DISABLE_STORES')) {
    define('CACHE_DISABLE_STORES', false);
}

date_default_timezone_set(@date_default_timezone_get());

if (!defined('CLI_SCRIPT')) {
    define('CLI_SCRIPT', false);
}
if (defined('WEB_CRON_EMULATED_CLI')) {
    if (!isset($_SERVER['REMOTE_ADDR'])) {
        echo('Web cron can not be executed as CLI script any more, please use admin/cli/cron.php instead'."\n");
        exit(1);
    }
} else if (isset($_SERVER['REMOTE_ADDR'])) {
    if (CLI_SCRIPT) {
        echo('Command line scripts can not be executed from the web interface');
        exit(1);
    }
} else {
    if (!CLI_SCRIPT) {
        echo('Command line scripts must define CLI_SCRIPT before requiring config.php'."\n");
        exit(1);
    }
}

if (!defined('WS_SERVER')) {
    define('WS_SERVER', false);
}

if (file_exists("$CFG->dataroot/climaintenance.html")) {
    if (!CLI_SCRIPT) {
        header('Content-type: text/html; charset=utf-8');
        header('X-UA-Compatible: IE=edge');
                header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: Mon, 20 Aug 1969 09:23:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Accept-Ranges: none');
        readfile("$CFG->dataroot/climaintenance.html");
        die;
    } else {
        if (!defined('CLI_MAINTENANCE')) {
            define('CLI_MAINTENANCE', true);
        }
    }
} else {
    if (!defined('CLI_MAINTENANCE')) {
        define('CLI_MAINTENANCE', false);
    }
}

if (CLI_SCRIPT) {
        if (version_compare(phpversion(), '5.4.4') < 0) {
        $phpversion = phpversion();
                echo "Moodle 2.7 or later requires at least PHP 5.4.4 (currently using version $phpversion).\n";
        echo "Some servers may have multiple PHP versions installed, are you using the correct executable?\n";
        exit(1);
    }
}

if (!defined('AJAX_SCRIPT')) {
    define('AJAX_SCRIPT', false);
}

$CFG->yui2version = '2.9.0';
$CFG->yui3version = '3.17.2';

$CFG->yuipatchlevel = 0;
$CFG->yuipatchedmodules = array(
);

if (!empty($CFG->disableonclickaddoninstall)) {
        $CFG->disableupdateautodeploy = true;
}

if (!isset($CFG->config_php_settings)) {
    $CFG->config_php_settings = (array)$CFG;
        unset($CFG->config_php_settings['forced_plugin_settings']);
    if (!isset($CFG->forced_plugin_settings)) {
        $CFG->forced_plugin_settings = array();
    }
}

if (isset($CFG->debug)) {
    $CFG->debug = (int)$CFG->debug;
} else {
    $CFG->debug = 0;
}
$CFG->debugdeveloper = (($CFG->debug & (E_ALL | E_STRICT)) === (E_ALL | E_STRICT)); 
if (!defined('MOODLE_INTERNAL')) {     
    define('MOODLE_INTERNAL', true);
}

require_once($CFG->libdir .'/classes/component.php');

if (defined('ABORT_AFTER_CONFIG')) {
    if (!defined('ABORT_AFTER_CONFIG_CANCEL')) {
                error_reporting($CFG->debug);
        if (NO_DEBUG_DISPLAY) {
                        ini_set('display_errors', '0');
            ini_set('log_errors', '1');
        } else if (empty($CFG->debugdisplay)) {
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
        } else {
            ini_set('display_errors', '1');
        }
        require_once("$CFG->dirroot/lib/configonlylib.php");
        return;
    }
}

if (!empty($CFG->earlyprofilingenabled)) {
    require_once($CFG->libdir . '/xhprof/xhprof_moodle.php');
    profiling_start();
}


global $DB;


global $SESSION;


global $USER;


global $SITE;


global $PAGE;


global $COURSE;


global $OUTPUT;


global $FULLME;


global $ME;


global $FULLSCRIPT;


global $SCRIPT;

$CFG->httpswwwroot = $CFG->wwwroot;

require_once($CFG->libdir .'/setuplib.php');        
if (NO_OUTPUT_BUFFERING) {
        disable_output_buffering();
}

raise_memory_limit(MEMORY_STANDARD);

init_performance_info();

$OUTPUT = new bootstrap_renderer();

if (!PHPUNIT_TEST or PHPUNIT_UTIL) {
    set_exception_handler('default_exception_handler');
    set_error_handler('default_error_handler', E_ALL | E_STRICT);
}

if (defined('BEHAT_SITE_RUNNING') && !defined('BEHAT_TEST') && !defined('BEHAT_UTIL')) {
    require_once(__DIR__ . '/behat/lib.php');
    set_error_handler('behat_error_handler', E_ALL | E_STRICT);
}

error_reporting(E_ALL | E_STRICT);

if (!empty($_SERVER['HTTP_X_moz']) && $_SERVER['HTTP_X_moz'] === 'prefetch'){
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Prefetch Forbidden');
    echo('Prefetch request forbidden.');
    exit(1);
}

ini_set('include_path', $CFG->libdir.'/pear' . PATH_SEPARATOR . ini_get('include_path'));

if (defined('COMPONENT_CLASSLOADER')) {
    spl_autoload_register(COMPONENT_CLASSLOADER);
} else {
    spl_autoload_register('core_component::classloader');
}

core_date::store_default_php_timezone();

require_once($CFG->libdir .'/filterlib.php');       require_once($CFG->libdir .'/ajax/ajaxlib.php');    require_once($CFG->libdir .'/weblib.php');          require_once($CFG->libdir .'/outputlib.php');       require_once($CFG->libdir .'/navigationlib.php');   require_once($CFG->libdir .'/dmllib.php');          require_once($CFG->libdir .'/datalib.php');         require_once($CFG->libdir .'/accesslib.php');       require_once($CFG->libdir .'/deprecatedlib.php');   require_once($CFG->libdir .'/moodlelib.php');       require_once($CFG->libdir .'/enrollib.php');        require_once($CFG->libdir .'/pagelib.php');         require_once($CFG->libdir .'/blocklib.php');        require_once($CFG->libdir .'/eventslib.php');       require_once($CFG->libdir .'/grouplib.php');        require_once($CFG->libdir .'/sessionlib.php');      require_once($CFG->libdir .'/editorlib.php');       require_once($CFG->libdir .'/messagelib.php');      require_once($CFG->libdir .'/modinfolib.php');      require_once($CFG->dirroot.'/cache/lib.php');       
setup_validate_php_configuration();

setup_DB();

if (PHPUNIT_TEST and !PHPUNIT_UTIL) {
        test_lock::acquire('phpunit');
    $dbhash = null;
    try {
        if ($dbhash = $DB->get_field('config', 'value', array('name'=>'phpunittest'))) {
                        phpunit_util::reset_database();
        }
    } catch (Exception $e) {
        if ($dbhash) {
                        $DB->set_field('config', 'value', 'na', array('name'=>'phpunittest'));
        }
    }
    unset($dbhash);
}

if (PHPUNIT_TEST) {
    phpunit_util::initialise_cfg();
} else {
    initialise_cfg();
}

if (isset($CFG->debug)) {
    $CFG->debug = (int)$CFG->debug;
    error_reporting($CFG->debug);
}  else {
    $CFG->debug = 0;
}
$CFG->debugdeveloper = (($CFG->debug & DEBUG_DEVELOPER) === DEBUG_DEVELOPER);

if (ini_get_bool('display_errors')) {
    define('WARN_DISPLAY_ERRORS_ENABLED', true);
}
if (!isset($CFG->debugdisplay)) {
    } else if (NO_DEBUG_DISPLAY) {
        ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else if (empty($CFG->debugdisplay)) {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
        ini_set('display_errors', '1');
}

core_shutdown_manager::initialize();

if (!defined('NO_UPGRADE_CHECK') and isset($CFG->upgraderunning)) {
    if ($CFG->upgraderunning < time()) {
        unset_config('upgraderunning');
    } else {
        print_error('upgraderunning');
    }
}

if (function_exists('gc_enable')) {
    gc_enable();
}

if (!empty($CFG->version) and $CFG->version < 2007101509) {
    print_error('upgraderequires19', 'error');
    die;
}

if (stristr(PHP_OS, 'win') && !stristr(PHP_OS, 'darwin')) {
    $CFG->ostype = 'WINDOWS';
} else {
    $CFG->ostype = 'UNIX';
}
$CFG->os = PHP_OS;

ini_set('arg_separator.output', '&amp;');

ini_set('pcre.backtrack_limit', 20971520);  
if (ini_get('pcre.jit')) {
    ini_set('pcre.jit', 0);
}

core_date::set_default_server_timezone();

$CFG->wordlist = $CFG->libdir .'/wordlist.txt';
$CFG->moddata  = 'moddata';

if (isset($_SERVER['PHP_SELF'])) {
    $phppos = strpos($_SERVER['PHP_SELF'], '.php');
    if ($phppos !== false) {
        $_SERVER['PHP_SELF'] = substr($_SERVER['PHP_SELF'], 0, $phppos+4);
    }
    unset($phppos);
}

initialise_fullme();

if (!defined('SYSCONTEXTID')) {
    context_system::instance();
}

try {
    $SITE = get_site();
} catch (moodle_exception $e) {
    $SITE = null;
    if (empty($CFG->version)) {
        $SITE = new stdClass();
        $SITE->id = 1;
        $SITE->shortname = null;
    } else {
        throw $e;
    }
}
$COURSE = clone($SITE);
define('SITEID', $SITE->id);

if (CLI_SCRIPT) {
        define('NO_MOODLE_COOKIES', true);

} else if (WS_SERVER) {
        define('NO_MOODLE_COOKIES', true);

} else if (!defined('NO_MOODLE_COOKIES')) {
    if (empty($CFG->version) or $CFG->version < 2009011900) {
                define('NO_MOODLE_COOKIES', true);
    } else if (CLI_SCRIPT) {
                define('NO_MOODLE_COOKIES', true);
    } else {
        define('NO_MOODLE_COOKIES', false);
    }
}

if (empty($CFG->sessiontimeout)) {
    $CFG->sessiontimeout = 7200;
}
\core\session\manager::start();

if (AJAX_SCRIPT) {
    if (!core_useragent::supports_json_contenttype()) {
                @header('Content-type: text/plain; charset=utf-8');
        @header('X-Content-Type-Options: nosniff');
    } else if (!empty($_FILES)) {
                @header('Content-type: text/plain; charset=utf-8');
    } else {
        @header('Content-type: application/json; charset=utf-8');
    }
} else if (!CLI_SCRIPT) {
    @header('Content-type: text/html; charset=utf-8');
}

if (!isset($CFG->filelifetime)) {
    $CFG->filelifetime = 60*60*6;
}

if (!empty($CFG->profilingenabled)) {
    require_once($CFG->libdir . '/xhprof/xhprof_moodle.php');
    profiling_start();
}

workaround_max_input_vars();

if (!empty($CFG->allowthemechangeonurl) and !empty($_GET['theme'])) {
        $urlthemename = optional_param('theme', '', PARAM_PLUGIN);
    try {
        $themeconfig = theme_config::load($urlthemename);
                if ($themeconfig->name === $urlthemename) {
            $SESSION->theme = $urlthemename;
        } else {
            unset($SESSION->theme);
        }
        unset($themeconfig);
        unset($urlthemename);
    } catch (Exception $e) {
        debugging('Failed to set the theme from the URL.', DEBUG_DEVELOPER, $e->getTrace());
    }
}
unset($urlthemename);

if (!isset($CFG->theme)) {
    $CFG->theme = 'clean';
}

if (isset($_GET['lang']) and ($lang = optional_param('lang', '', PARAM_SAFEDIR))) {
    if (get_string_manager()->translation_exists($lang, false)) {
        $SESSION->lang = $lang;
    }
}
unset($lang);

if ($forcelang = optional_param('forcelang', '', PARAM_SAFEDIR)) {
    if (isloggedin()
        && get_string_manager()->translation_exists($forcelang, false)
        && has_capability('moodle/site:forcelanguage', context_system::instance())) {
        $SESSION->forcelang = $forcelang;
    } else if (isset($SESSION->forcelang)) {
        unset($SESSION->forcelang);
    }
}
unset($forcelang);

setup_lang_from_browser();

if (empty($CFG->lang)) {
    if (empty($SESSION->lang)) {
        $CFG->lang = 'en';
    } else {
        $CFG->lang = $SESSION->lang;
    }
}

moodle_setlocale();

if (!empty($CFG->moodlepageclass)) {
    if (!empty($CFG->moodlepageclassfile)) {
        require_once($CFG->moodlepageclassfile);
    }
    $classname = $CFG->moodlepageclass;
} else {
    $classname = 'moodle_page';
}
$PAGE = new $classname();
unset($classname);


if (!empty($CFG->debugvalidators) and !empty($CFG->guestloginbutton)) {
    if ($CFG->theme == 'standard') {            if (isset($_SERVER['HTTP_USER_AGENT']) and empty($USER->id)) {                  if ((strpos($_SERVER['HTTP_USER_AGENT'], 'W3C_Validator') !== false) or
                (strpos($_SERVER['HTTP_USER_AGENT'], 'Cynthia') !== false )) {
                if ($user = get_complete_user_data("username", "w3cvalidator")) {
                    $user->ignoresesskey = true;
                } else {
                    $user = guest_user();
                }
                \core\session\manager::set_user($user);
            }
        }
    }
}

if ($USER && function_exists('apache_note')
    && !empty($CFG->apacheloguser) && isset($USER->username)) {
    $apachelog_userid = $USER->id;
    $apachelog_username = clean_filename($USER->username);
    $apachelog_name = '';
    if (isset($USER->firstname)) {
                        $apachelog_name = clean_filename($USER->firstname . " " .
                                         $USER->lastname);
    }
    if (\core\session\manager::is_loggedinas()) {
        $realuser = \core\session\manager::get_realuser();
        $apachelog_username = clean_filename($realuser->username." as ".$apachelog_username);
        $apachelog_name = clean_filename($realuser->firstname." ".$realuser->lastname ." as ".$apachelog_name);
        $apachelog_userid = clean_filename($realuser->id." as ".$apachelog_userid);
    }
    switch ($CFG->apacheloguser) {
        case 3:
            $logname = $apachelog_username;
            break;
        case 2:
            $logname = $apachelog_name;
            break;
        case 1:
        default:
            $logname = $apachelog_userid;
            break;
    }
    apache_note('MOODLEUSER', $logname);
}

if (isset($CFG->urlrewriteclass)) {
    if (!class_exists($CFG->urlrewriteclass)) {
        debugging("urlrewriteclass {$CFG->urlrewriteclass} was not found, disabling.");
        unset($CFG->urlrewriteclass);
    } else if (!in_array('core\output\url_rewriter', class_implements($CFG->urlrewriteclass))) {
        debugging("{$CFG->urlrewriteclass} does not implement core\output\url_rewriter, disabling.", DEBUG_DEVELOPER);
        unset($CFG->urlrewriteclass);
    }
}

if (!empty($CFG->customscripts)) {
    if (($customscript = custom_script_path()) !== false) {
        require ($customscript);
    }
}

if (PHPUNIT_TEST) {
    } else if (CLI_SCRIPT and !defined('WEB_CRON_EMULATED_CLI')) {
    } else if (!empty($CFG->allowbeforeblock)) {                         if (!empty($CFG->allowedip)) {
        if (!remoteip_in_list($CFG->allowedip)) {
            die(get_string('ipblocked', 'admin'));
        }
    }
                if (!empty($CFG->blockedip)) {
        if (remoteip_in_list($CFG->blockedip)) {
            die(get_string('ipblocked', 'admin'));
        }
    }

} else {
                        if (!empty($CFG->blockedip)) {
        if (remoteip_in_list($CFG->blockedip)) {
                                                if (!empty($CFG->allowedip)) {
                if (!remoteip_in_list($CFG->allowedip)) {
                    die(get_string('ipblocked', 'admin'));
                }
            } else {
                die(get_string('ipblocked', 'admin'));
            }
        }
    }
            if(!empty($CFG->allowedip)) {
        if (!remoteip_in_list($CFG->allowedip)) {
            die(get_string('ipblocked', 'admin'));
        }
    }

}

if (!empty($_SERVER['HTTP_USER_AGENT']) and strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6') !== false) {
    ini_set('zlib.output_compression', 'Off');
    if (function_exists('apache_setenv')) {
        apache_setenv('no-gzip', 1);
    }
}

if (isset($CFG->maintenance_later) and $CFG->maintenance_later <= time()) {
    if (!file_exists("$CFG->dataroot/climaintenance.html")) {
        require_once("$CFG->libdir/adminlib.php");
        enable_cli_maintenance_mode();
    }
    unset_config('maintenance_later');
    if (AJAX_SCRIPT) {
        die;
    } else if (!CLI_SCRIPT) {
        redirect(new moodle_url('/'));
    }
}

if (defined('BEHAT_SITE_RUNNING') && !defined('BEHAT_TEST')) {
    core_shutdown_manager::register_function('behat_shutdown_function');
}




if (false) {
    $DB = new moodle_database();
    $OUTPUT = new core_renderer(null, null);
    $PAGE = new moodle_page();
}
