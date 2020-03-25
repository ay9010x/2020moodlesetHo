<?php



if (isset($_SERVER['REMOTE_ADDR'])) {
    die; }

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

if (ini_get('opcache.enable') and strtolower(ini_get('opcache.enable')) !== 'off') {
    if (!ini_get('opcache.save_comments') or strtolower(ini_get('opcache.save_comments')) === 'off') {
        ini_set('opcache.enable', 0);
    } else {
        ini_set('opcache.load_comments', 1);
    }
}

if (!defined('IGNORE_COMPONENT_CACHE')) {
    define('IGNORE_COMPONENT_CACHE', true);
}

require_once(__DIR__.'/bootstraplib.php');
require_once(__DIR__.'/../testing/lib.php');
require_once(__DIR__.'/classes/autoloader.php');

if (isset($_SERVER['REMOTE_ADDR'])) {
    phpunit_bootstrap_error(1, 'Unit tests can be executed only from command line!');
}

if (defined('PHPUNIT_TEST')) {
    phpunit_bootstrap_error(1, "PHPUNIT_TEST constant must not be manually defined anywhere!");
}

define('PHPUNIT_TEST', true);

if (!defined('PHPUNIT_UTIL')) {
    
    define('PHPUNIT_UTIL', false);
}

if (defined('CLI_SCRIPT')) {
    phpunit_bootstrap_error(1, 'CLI_SCRIPT must not be manually defined in any PHPUnit test scripts');
}
define('CLI_SCRIPT', true);

$phpunitversion = PHPUnit_Runner_Version::id();
if ($phpunitversion === '@package_version@') {
    } else if (version_compare($phpunitversion, '3.6.0', 'lt')) {
    phpunit_bootstrap_error(PHPUNIT_EXITCODE_PHPUNITWRONG, $phpunitversion);
}
unset($phpunitversion);

if (!include_once('PHPUnit/Extensions/Database/Autoload.php')) {
    phpunit_bootstrap_error(PHPUNIT_EXITCODE_PHPUNITEXTMISSING, 'phpunit/DbUnit');
}

define('NO_OUTPUT_BUFFERING', true);

define('ABORT_AFTER_CONFIG', true);
require(__DIR__ . '/../../config.php');

if (!defined('PHPUNIT_LONGTEST')) {
    
    define('PHPUNIT_LONGTEST', false);
}

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
set_time_limit(0); 
umask(0);
if (isset($CFG->phpunit_directorypermissions)) {
    $CFG->directorypermissions = $CFG->phpunit_directorypermissions;
} else {
    $CFG->directorypermissions = 02777;
}
$CFG->filepermissions = ($CFG->directorypermissions & 0666);
if (!isset($CFG->phpunit_dataroot)) {
    phpunit_bootstrap_error(PHPUNIT_EXITCODE_CONFIGERROR, 'Missing $CFG->phpunit_dataroot in config.php, can not run tests!');
}

if (!file_exists($CFG->phpunit_dataroot)) {
    mkdir($CFG->phpunit_dataroot, $CFG->directorypermissions);
}
if (!is_dir($CFG->phpunit_dataroot)) {
    phpunit_bootstrap_error(PHPUNIT_EXITCODE_CONFIGERROR, '$CFG->phpunit_dataroot directory can not be created, can not run tests!');
}

$CFG->phpunit_dataroot = realpath($CFG->phpunit_dataroot);

if (isset($CFG->dataroot) and $CFG->phpunit_dataroot === $CFG->dataroot) {
    phpunit_bootstrap_error(PHPUNIT_EXITCODE_CONFIGERROR, '$CFG->dataroot and $CFG->phpunit_dataroot must not be identical, can not run tests!');
}

if (!is_writable($CFG->phpunit_dataroot)) {
        if (function_exists('posix_getuid')) {
        $chmod = fileperms($CFG->phpunit_dataroot);
        if (fileowner($CFG->phpunit_dataroot) == posix_getuid()) {
            $chmod = $chmod | 0700;
            chmod($CFG->phpunit_dataroot, $chmod);
        }
    }
    if (!is_writable($CFG->phpunit_dataroot)) {
        phpunit_bootstrap_error(PHPUNIT_EXITCODE_CONFIGERROR, '$CFG->phpunit_dataroot directory is not writable, can not run tests!');
    }
}
if (!file_exists("$CFG->phpunit_dataroot/phpunittestdir.txt")) {
    if ($dh = opendir($CFG->phpunit_dataroot)) {
        while (($file = readdir($dh)) !== false) {
            if ($file === 'phpunit' or $file === '.' or $file === '..' or $file === '.DS_Store') {
                continue;
            }
            phpunit_bootstrap_error(PHPUNIT_EXITCODE_CONFIGERROR, '$CFG->phpunit_dataroot directory is not empty, can not run tests! Is it used for anything else?');
        }
        closedir($dh);
        unset($dh);
        unset($file);
    }

        testing_initdataroot($CFG->phpunit_dataroot, 'phpunit');
}

if (!isset($CFG->phpunit_prefix)) {
    phpunit_bootstrap_error(PHPUNIT_EXITCODE_CONFIGERROR, 'Missing $CFG->phpunit_prefix in config.php, can not run tests!');
}
if ($CFG->phpunit_prefix === '') {
    phpunit_bootstrap_error(PHPUNIT_EXITCODE_CONFIGERROR, '$CFG->phpunit_prefix can not be empty, can not run tests!');
}
if (isset($CFG->prefix) and $CFG->prefix === $CFG->phpunit_prefix) {
    phpunit_bootstrap_error(PHPUNIT_EXITCODE_CONFIGERROR, '$CFG->prefix and $CFG->phpunit_prefix must not be identical, can not run tests!');
}

$CFG->wwwroot   = 'http://www.example.com/moodle';
$CFG->dataroot  = $CFG->phpunit_dataroot;
$CFG->prefix    = $CFG->phpunit_prefix;
$CFG->dbtype    = isset($CFG->phpunit_dbtype) ? $CFG->phpunit_dbtype : $CFG->dbtype;
$CFG->dblibrary = isset($CFG->phpunit_dblibrary) ? $CFG->phpunit_dblibrary : $CFG->dblibrary;
$CFG->dbhost    = isset($CFG->phpunit_dbhost) ? $CFG->phpunit_dbhost : $CFG->dbhost;
$CFG->dbname    = isset($CFG->phpunit_dbname) ? $CFG->phpunit_dbname : $CFG->dbname;
$CFG->dbuser    = isset($CFG->phpunit_dbuser) ? $CFG->phpunit_dbuser : $CFG->dbuser;
$CFG->dbpass    = isset($CFG->phpunit_dbpass) ? $CFG->phpunit_dbpass : $CFG->dbpass;
$CFG->prefix    = isset($CFG->phpunit_prefix) ? $CFG->phpunit_prefix : $CFG->prefix;
$CFG->dboptions = isset($CFG->phpunit_dboptions) ? $CFG->phpunit_dboptions : $CFG->dboptions;

$allowed = array('wwwroot', 'dataroot', 'dirroot', 'admin', 'directorypermissions', 'filepermissions',
                 'dbtype', 'dblibrary', 'dbhost', 'dbname', 'dbuser', 'dbpass', 'prefix', 'dboptions',
                 'proxyhost', 'proxyport', 'proxytype', 'proxyuser', 'proxypassword', 'proxybypass',                  'altcacheconfigpath', 'pathtogs', 'pathtodu', 'aspellpath', 'pathtodot',
                 'pathtounoconv'
                );
$productioncfg = (array)$CFG;
$CFG = new stdClass();
foreach ($productioncfg as $key=>$value) {
    if (!in_array($key, $allowed) and strpos($key, 'phpunit_') !== 0) {
                continue;
    }
    $CFG->{$key} = $value;
}
unset($key);
unset($value);
unset($allowed);
unset($productioncfg);

$CFG->debug = (E_ALL | E_STRICT); $CFG->debugdeveloper = true;
$CFG->debugdisplay = 1;
error_reporting($CFG->debug);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

$CFG->themerev = 1;
$CFG->jsrev = 1;

require_once("$CFG->dirroot/lib/phpunit/lib.php");

define('ABORT_AFTER_CONFIG_CANCEL', true);
require("$CFG->dirroot/lib/setup.php");

raise_memory_limit(MEMORY_HUGE);

if (PHPUNIT_UTIL) {
        return;
}

list($errorcode, $message) = phpunit_util::testing_ready_problem();
phpunit_util::bootstrap_moodle_info();
if ($errorcode) {
    phpunit_bootstrap_error($errorcode, $message);
}

phpunit_util::bootstrap_init();
