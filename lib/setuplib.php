<?php



defined('MOODLE_INTERNAL') || die();


define('DEBUG_NONE', 0);

define('DEBUG_MINIMAL', E_ERROR | E_PARSE);

define('DEBUG_NORMAL', E_ERROR | E_PARSE | E_WARNING | E_NOTICE);

define('DEBUG_ALL', E_ALL & ~E_STRICT);

define('DEBUG_DEVELOPER', E_ALL | E_STRICT);


define('MEMORY_UNLIMITED', -1);

define('MEMORY_STANDARD', -2);

define('MEMORY_EXTRA', -3);

define('MEMORY_HUGE', -4);



class object extends stdClass {
    
    public function __construct() {
        debugging("'object' class has been deprecated, please use stdClass instead.", DEBUG_DEVELOPER);
    }
};


class moodle_exception extends Exception {

    
    public $errorcode;

    
    public $module;

    
    public $a;

    
    public $link;

    
    public $debuginfo;

    
    function __construct($errorcode, $module='', $link='', $a=NULL, $debuginfo=null) {
        if (empty($module) || $module == 'moodle' || $module == 'core') {
            $module = 'error';
        }

        $this->errorcode = $errorcode;
        $this->module    = $module;
        $this->link      = $link;
        $this->a         = $a;
        $this->debuginfo = is_null($debuginfo) ? null : (string)$debuginfo;

        if (get_string_manager()->string_exists($errorcode, $module)) {
            $message = get_string($errorcode, $module, $a);
            $haserrorstring = true;
        } else {
            $message = $module . '/' . $errorcode;
            $haserrorstring = false;
        }

        if (defined('PHPUNIT_TEST') and PHPUNIT_TEST and $debuginfo) {
            $message = "$message ($debuginfo)";
        }

        if (!$haserrorstring and defined('PHPUNIT_TEST') and PHPUNIT_TEST) {
                                                $message .= PHP_EOL.'$a contents: '.print_r($a, true);
        }

        parent::__construct($message, 0);
    }
}


class require_login_exception extends moodle_exception {
    
    function __construct($debuginfo) {
        parent::__construct('requireloginerror', 'error', '', NULL, $debuginfo);
    }
}


class require_login_session_timeout_exception extends require_login_exception {
    
    public function __construct() {
        moodle_exception::__construct('sessionerroruser', 'error');
    }
}


class webservice_parameter_exception extends moodle_exception {
    
    function __construct($errorcode=null, $a = '', $debuginfo = null) {
        parent::__construct($errorcode, 'webservice', '', $a, $debuginfo);
    }
}


class required_capability_exception extends moodle_exception {
    
    function __construct($context, $capability, $errormessage, $stringfile) {
        $capabilityname = get_capability_string($capability);
        if ($context->contextlevel == CONTEXT_MODULE and preg_match('/:view$/', $capability)) {
                        $parentcontext = $context->get_parent_context();
            $link = $parentcontext->get_url();
        } else {
            $link = $context->get_url();
        }
        parent::__construct($errormessage, $stringfile, $link, $capabilityname);
    }
}


class coding_exception extends moodle_exception {
    
    function __construct($hint, $debuginfo=null) {
        parent::__construct('codingerror', 'debug', '', $hint, $debuginfo);
    }
}


class invalid_parameter_exception extends moodle_exception {
    
    function __construct($debuginfo=null) {
        parent::__construct('invalidparameter', 'debug', '', $debuginfo, $debuginfo);
    }
}


class invalid_response_exception extends moodle_exception {
    
    function __construct($debuginfo=null) {
        parent::__construct('invalidresponse', 'debug', '', null, $debuginfo);
    }
}


class invalid_state_exception extends moodle_exception {
    
    function __construct($hint, $debuginfo=null) {
        parent::__construct('invalidstatedetected', 'debug', '', $hint, $debuginfo);
    }
}


class invalid_dataroot_permissions extends moodle_exception {
    
    function __construct($debuginfo = NULL) {
        parent::__construct('invaliddatarootpermissions', 'error', '', NULL, $debuginfo);
    }
}


class file_serving_exception extends moodle_exception {
    
    function __construct($debuginfo = NULL) {
        parent::__construct('cannotservefile', 'error', '', NULL, $debuginfo);
    }
}


function default_exception_handler($ex) {
    global $CFG, $DB, $OUTPUT, $USER, $FULLME, $SESSION, $PAGE;

        abort_all_db_transactions();

    if (($ex instanceof required_capability_exception) && !CLI_SCRIPT && !AJAX_SCRIPT && !empty($CFG->autologinguests) && !empty($USER->autologinguest)) {
        $SESSION->wantsurl = qualified_me();
        redirect(get_login_url());
    }

    $info = get_exception_info($ex);

    if (debugging('', DEBUG_MINIMAL)) {
        $logerrmsg = "Default exception handler: ".$info->message.' Debug: '.$info->debuginfo."\n".format_backtrace($info->backtrace, true);
        error_log($logerrmsg);
    }

    if (is_early_init($info->backtrace)) {
        echo bootstrap_renderer::early_error($info->message, $info->moreinfourl, $info->link, $info->backtrace, $info->debuginfo, $info->errorcode);
    } else {
        try {
            if ($DB) {
                                $DB->set_debug(0);
            }
            echo $OUTPUT->fatal_error($info->message, $info->moreinfourl, $info->link, $info->backtrace, $info->debuginfo,
                $info->errorcode);
        } catch (Exception $e) {
            $out_ex = $e;
        } catch (Throwable $e) {
                        $out_ex = $e;
        }

        if (isset($out_ex)) {
                                                if (CLI_SCRIPT or AJAX_SCRIPT) {
                                echo bootstrap_renderer::early_error($info->message, $info->moreinfourl, $info->link, $info->backtrace, $info->debuginfo, $info->errorcode);
            } else {
                echo bootstrap_renderer::early_error_content($info->message, $info->moreinfourl, $info->link, $info->backtrace, $info->debuginfo);
                $outinfo = get_exception_info($out_ex);
                echo bootstrap_renderer::early_error_content($outinfo->message, $outinfo->moreinfourl, $outinfo->link, $outinfo->backtrace, $outinfo->debuginfo);
            }
        }
    }

    exit(1); }


function default_error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
    if ($errno == 4096) {
                throw new coding_exception('PHP catchable fatal error', $errstr);
    }
    return false;
}


function abort_all_db_transactions() {
    global $CFG, $DB, $SCRIPT;

    
    if ($DB && $DB->is_transaction_started()) {
        error_log('Database transaction aborted automatically in ' . $CFG->dirroot . $SCRIPT);
                $DB->force_transaction_rollback();
    }
}


function is_early_init($backtrace) {
    $dangerouscode = array(
        array('function' => 'header', 'type' => '->'),
        array('class' => 'bootstrap_renderer'),
        array('file' => dirname(__FILE__).'/setup.php'),
    );
    foreach ($backtrace as $stackframe) {
        foreach ($dangerouscode as $pattern) {
            $matches = true;
            foreach ($pattern as $property => $value) {
                if (!isset($stackframe[$property]) || $stackframe[$property] != $value) {
                    $matches = false;
                }
            }
            if ($matches) {
                return true;
            }
        }
    }
    return false;
}


function print_error($errorcode, $module = 'error', $link = '', $a = null, $debuginfo = null) {
    throw new moodle_exception($errorcode, $module, $link, $a, $debuginfo);
}


function get_exception_info($ex) {
    global $CFG, $DB, $SESSION;

    if ($ex instanceof moodle_exception) {
        $errorcode = $ex->errorcode;
        $module = $ex->module;
        $a = $ex->a;
        $link = $ex->link;
        $debuginfo = $ex->debuginfo;
    } else {
        $errorcode = 'generalexceptionmessage';
        $module = 'error';
        $a = $ex->getMessage();
        $link = '';
        $debuginfo = '';
    }

        $debuginfo .= PHP_EOL."Error code: $errorcode";

    $backtrace = $ex->getTrace();
    $place = array('file'=>$ex->getFile(), 'line'=>$ex->getLine(), 'exception'=>get_class($ex));
    array_unshift($backtrace, $place);

        if (empty($module) || $module == 'moodle' || $module == 'core') {
        $module = 'error';
    }
            if (function_exists('get_string_manager')) {
        if (get_string_manager()->string_exists($errorcode, $module)) {
            $message = get_string($errorcode, $module, $a);
        } elseif ($module == 'error' && get_string_manager()->string_exists($errorcode, 'moodle')) {
                        $message = get_string($errorcode, 'moodle', $a);
        } else {
            $message = $module . '/' . $errorcode;
            $debuginfo .= PHP_EOL.'$a contents: '.print_r($a, true);
        }
    } else {
        $message = $module . '/' . $errorcode;
        $debuginfo .= PHP_EOL.'$a contents: '.print_r($a, true);
    }

        $searches = array();
    $replaces = array();
    $cfgnames = array('tempdir', 'cachedir', 'localcachedir', 'themedir', 'dataroot', 'dirroot');
    foreach ($cfgnames as $cfgname) {
        if (property_exists($CFG, $cfgname)) {
            $searches[] = $CFG->$cfgname;
            $replaces[] = "[$cfgname]";
        }
    }
    if (!empty($searches)) {
        $message   = str_replace($searches, $replaces, $message);
        $debuginfo = str_replace($searches, $replaces, $debuginfo);
    }

        if (function_exists('clean_text')) {
        $message = clean_text($message);
    } else {
        $message = htmlspecialchars($message);
    }

    if (!empty($CFG->errordocroot)) {
        $errordoclink = $CFG->errordocroot . '/en/';
    } else {
        $errordoclink = get_docs_url();
    }

    if ($module === 'error') {
        $modulelink = 'moodle';
    } else {
        $modulelink = $module;
    }
    $moreinfourl = $errordoclink . 'error/' . $modulelink . '/' . $errorcode;

    if (empty($link)) {
        if (!empty($SESSION->fromurl)) {
            $link = $SESSION->fromurl;
            unset($SESSION->fromurl);
        } else {
            $link = $CFG->wwwroot .'/';
        }
    }

            $httpswwwroot = str_replace('http:', 'https:', $CFG->wwwroot);
    if (stripos($link, $CFG->wwwroot) === 0) {
            } else if (!empty($CFG->loginhttps) && stripos($link, $httpswwwroot) === 0) {
            } else {
                $link = $CFG->wwwroot . '/';
    }

    $info = new stdClass();
    $info->message     = $message;
    $info->errorcode   = $errorcode;
    $info->backtrace   = $backtrace;
    $info->link        = $link;
    $info->moreinfourl = $moreinfourl;
    $info->a           = $a;
    $info->debuginfo   = $debuginfo;

    return $info;
}


function generate_uuid() {
    $uuid = '';

    if (function_exists("uuid_create")) {
        $context = null;
        uuid_create($context);

        uuid_make($context, UUID_MAKE_V4);
        uuid_export($context, UUID_FMT_STR, $uuid);
    } else {
                        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

                        mt_rand(0, 0xffff), mt_rand(0, 0xffff),

                        mt_rand(0, 0xffff),

                                    mt_rand(0, 0x0fff) | 0x4000,

                                                mt_rand(0, 0x3fff) | 0x8000,

                        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    }
    return trim($uuid);
}


function get_docs_url($path = null) {
    global $CFG;

        if (substr($path, 0, 7) === 'http://' || substr($path, 0, 8) === 'https://') {
        return $path;
    }

        if (substr($path, 0, 11) === '%%WWWROOT%%') {
        return $CFG->wwwroot . substr($path, 11);
    }

    
        if (empty($CFG->branch)) {
                include($CFG->dirroot.'/version.php');
    } else {
                $branch = $CFG->branch;
    }
        if (!$branch) {
                                        $branch = '.';
    }
    if (empty($CFG->doclang)) {
        $lang = current_language();
    } else {
        $lang = $CFG->doclang;
    }
    $end = '/' . $branch . '/' . $lang . '/' . $path;
    if (empty($CFG->docroot)) {
        return 'http://docs.moodle.org'. $end;
    } else {
        return $CFG->docroot . $end ;
    }
}


function format_backtrace($callers, $plaintext = false) {
        $dirroot = dirname(dirname(__FILE__));

    if (empty($callers)) {
        return '';
    }

    $from = $plaintext ? '' : '<ul style="text-align: left" data-rel="backtrace">';
    foreach ($callers as $caller) {
        if (!isset($caller['line'])) {
            $caller['line'] = '?';         }
        if (!isset($caller['file'])) {
            $caller['file'] = 'unknownfile';         }
        $from .= $plaintext ? '* ' : '<li>';
        $from .= 'line ' . $caller['line'] . ' of ' . str_replace($dirroot, '', $caller['file']);
        if (isset($caller['function'])) {
            $from .= ': call to ';
            if (isset($caller['class'])) {
                $from .= $caller['class'] . $caller['type'];
            }
            $from .= $caller['function'] . '()';
        } else if (isset($caller['exception'])) {
            $from .= ': '.$caller['exception'].' thrown';
        }
        $from .= $plaintext ? "\n" : '</li>';
    }
    $from .= $plaintext ? '' : '</ul>';

    return $from;
}


function ini_get_bool($ini_get_arg) {
    $temp = ini_get($ini_get_arg);

    if ($temp == '1' or strtolower($temp) == 'on') {
        return true;
    }
    return false;
}


function setup_validate_php_configuration() {
   
   if (ini_get_bool('session.auto_start')) {
       print_error('sessionautostartwarning', 'admin');
   }
}


function initialise_cfg() {
    global $CFG, $DB;

    if (!$DB) {
                return;
    }

    try {
        $localcfg = get_config('core');
    } catch (dml_exception $e) {
                return;
    }

    foreach ($localcfg as $name => $value) {
                        $CFG->{$name} = $value;
    }
}


function initialise_fullme() {
    global $CFG, $FULLME, $ME, $SCRIPT, $FULLSCRIPT;

        if (substr($CFG->wwwroot, -1) == '/') {
        print_error('wwwrootslash', 'error');
    }

    if (CLI_SCRIPT) {
        initialise_fullme_cli();
        return;
    }

    $rurl = setup_get_remote_url();
    $wwwroot = parse_url($CFG->wwwroot.'/');

    if (empty($rurl['host'])) {
        
    } else if (!empty($CFG->reverseproxy)) {
                        
    } else {
        if (($rurl['host'] !== $wwwroot['host']) or
                (!empty($wwwroot['port']) and $rurl['port'] != $wwwroot['port']) or
                (strpos($rurl['path'], $wwwroot['path']) !== 0)) {

                        if (!defined('NO_MOODLE_COOKIES')) {
                define('NO_MOODLE_COOKIES', true);
            }
                        if (defined('REQUIRE_CORRECT_ACCESS') && REQUIRE_CORRECT_ACCESS) {
                $wwwrootport = empty($wwwroot['port'])?'':$wwwroot['port'];
                $calledurl = $rurl['host'];
                if (!empty($rurl['port'])) {
                    $calledurl .=  ':'. $rurl['port'];
                }
                $correcturl = $wwwroot['host'];
                if (!empty($wwwrootport)) {
                    $correcturl .=  ':'. $wwwrootport;
                }
                throw new moodle_exception('requirecorrectaccess', 'error', '', null,
                    'You called ' . $calledurl .', you should have called ' . $correcturl);
            }
            redirect($CFG->wwwroot, get_string('wwwrootmismatch', 'error', $CFG->wwwroot), 3);
        }
    }

        if (strpos($rurl['path'], $wwwroot['path']) === 0) {
        $SCRIPT = substr($rurl['path'], strlen($wwwroot['path'])-1);
    } else {
                $SCRIPT = $FULLSCRIPT = $FULLME = $ME = null;
        return;
    }

            if (empty($CFG->sslproxy)) {
        if ($rurl['scheme'] === 'http' and $wwwroot['scheme'] === 'https') {
            print_error('sslonlyaccess', 'error');
        }
    } else {
        if ($wwwroot['scheme'] !== 'https') {
            throw new coding_exception('Must use https address in wwwroot when ssl proxy enabled!');
        }
        $rurl['scheme'] = 'https';         $_SERVER['HTTPS'] = 'on';         $_SERVER['SERVER_PORT'] = 443;     }

            if (!empty($CFG->reverseproxy) && $rurl['host'] === $wwwroot['host']) {
        print_error('reverseproxyabused', 'error');
    }

    $hostandport = $rurl['scheme'] . '://' . $wwwroot['host'];
    if (!empty($wwwroot['port'])) {
        $hostandport .= ':'.$wwwroot['port'];
    }

    $FULLSCRIPT = $hostandport . $rurl['path'];
    $FULLME = $hostandport . $rurl['fullpath'];
    $ME = $rurl['fullpath'];
}


function initialise_fullme_cli() {
    global $CFG, $FULLME, $ME, $SCRIPT, $FULLSCRIPT;

        $backtrace = debug_backtrace();
    $topfile = array_pop($backtrace);
    $topfile = realpath($topfile['file']);
    $dirroot = realpath($CFG->dirroot);

    if (strpos($topfile, $dirroot) !== 0) {
                $SCRIPT = $FULLSCRIPT = $FULLME = $ME = null;
    } else {
        $relativefile = substr($topfile, strlen($dirroot));
        $relativefile = str_replace('\\', '/', $relativefile);         $SCRIPT = $FULLSCRIPT = $relativefile;
        $FULLME = $ME = null;
    }
}


function setup_get_remote_url() {
    $rurl = array();
    if (isset($_SERVER['HTTP_HOST'])) {
        list($rurl['host']) = explode(':', $_SERVER['HTTP_HOST']);
    } else {
        $rurl['host'] = null;
    }
    $rurl['port'] = $_SERVER['SERVER_PORT'];
    $rurl['path'] = $_SERVER['SCRIPT_NAME'];     $rurl['scheme'] = (empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] === 'off' or $_SERVER['HTTPS'] === 'Off' or $_SERVER['HTTPS'] === 'OFF') ? 'http' : 'https';

    if (stripos($_SERVER['SERVER_SOFTWARE'], 'apache') !== false) {
                $rurl['fullpath'] = $_SERVER['REQUEST_URI'];

                                        if (isset($_SERVER['PATH_INFO']) && (php_sapi_name() === 'fpm-fcgi') && isset($_SERVER['SCRIPT_NAME'])) {
            $pathinfodec = rawurldecode($_SERVER['PATH_INFO']);
            $lenneedle = strlen($pathinfodec);
                        if (substr($_SERVER['SCRIPT_NAME'], -$lenneedle) === $pathinfodec) {
                                                                                $lenhaystack = strlen($_SERVER['SCRIPT_NAME']);
                $pos = $lenhaystack - $lenneedle;
                                if ($pos > 0) {
                    $_SERVER['PATH_INFO'] = $pathinfodec;
                    $_SERVER['SCRIPT_NAME'] = substr($_SERVER['SCRIPT_NAME'], 0, $pos);
                }
            }
        }

    } else if (stripos($_SERVER['SERVER_SOFTWARE'], 'iis') !== false) {
                $rurl['fullpath'] = $_SERVER['SCRIPT_NAME'];

                                                if (isset($_SERVER['PATH_INFO']) and $_SERVER['PATH_INFO'] !== '') {
                        if (strpos($_SERVER['PATH_INFO'], $_SERVER['SCRIPT_NAME']) === false) {
                $rurl['fullpath'] .= clean_param(urldecode($_SERVER['PATH_INFO']), PARAM_PATH);
            }
        }

        if (isset($_SERVER['QUERY_STRING']) and $_SERVER['QUERY_STRING'] !== '') {
            $rurl['fullpath'] .= '?'.$_SERVER['QUERY_STRING'];
        }
        $_SERVER['REQUEST_URI'] = $rurl['fullpath']; 


    } else if (stripos($_SERVER['SERVER_SOFTWARE'], 'lighttpd') !== false) {
                $rurl['fullpath'] = $_SERVER['REQUEST_URI']; 
    } else if (stripos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false) {
                if (!isset($_SERVER['SCRIPT_NAME'])) {
            die('Invalid server configuration detected, please try to add "fastcgi_param SCRIPT_NAME $fastcgi_script_name;" to the nginx server configuration.');
        }
        $rurl['fullpath'] = $_SERVER['REQUEST_URI']; 
     } else if (stripos($_SERVER['SERVER_SOFTWARE'], 'cherokee') !== false) {
                  $rurl['fullpath'] = $_SERVER['REQUEST_URI']; 
     } else if (stripos($_SERVER['SERVER_SOFTWARE'], 'zeus') !== false) {
                  $rurl['fullpath'] = $_SERVER['REQUEST_URI']; 
    } else if (stripos($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false) {
                $rurl['fullpath'] = $_SERVER['REQUEST_URI']; 
    } else if ($_SERVER['SERVER_SOFTWARE'] === 'HTTPD') {
                $rurl['fullpath'] = $_SERVER['REQUEST_URI']; 
    } else if (strpos($_SERVER['SERVER_SOFTWARE'], 'PHP') === 0) {
                $rurl['fullpath'] = $_SERVER['REQUEST_URI'];

    } else {
        throw new moodle_exception('unsupportedwebserver', 'error', '', $_SERVER['SERVER_SOFTWARE']);
    }

        $rurl['fullpath'] = str_replace('"', '%22', $rurl['fullpath']);
    $rurl['fullpath'] = str_replace('\'', '%27', $rurl['fullpath']);

    return $rurl;
}


function workaround_max_input_vars() {
        static $executed = false;
    if ($executed) {
        debugging('workaround_max_input_vars() must be called only once!');
        return;
    }
    $executed = true;

    if (!isset($_SERVER["CONTENT_TYPE"]) or strpos($_SERVER["CONTENT_TYPE"], 'multipart/form-data') !== false) {
                return;
    }

    if (!isloggedin() or isguestuser()) {
                return;
    }

    $max = (int)ini_get('max_input_vars');

    if ($max <= 0) {
                return;
    }

    if ($max >= 200000) {
                
                        return;
    }

                if (count($_POST, COUNT_RECURSIVE) * 2 < $max) {
        return;
    }

            $str = file_get_contents("php://input");
    if ($str === false or $str === '') {
                return;
    }

    $delim = '&';
    $fun = create_function('$p', 'return implode("'.$delim.'", $p);');
    $chunks = array_map($fun, array_chunk(explode($delim, $str), $max));

            foreach ($_POST as $key => $value) {
        unset($_POST[$key]);
                        unset($_REQUEST[$key]);
    }

    foreach ($chunks as $chunk) {
        $values = array();
        parse_str($chunk, $values);

        merge_query_params($_POST, $values);
        merge_query_params($_REQUEST, $values);
    }
}


function merge_query_params(array &$target, array $values) {
    if (isset($values[0]) and isset($target[0])) {
                $keys1 = array_keys($values);
        $keys2 = array_keys($target);
        if ($keys1 === array_keys($keys1) and $keys2 === array_keys($keys2)) {
            foreach ($values as $v) {
                $target[] = $v;
            }
            return;
        }
    }
    foreach ($values as $k => $v) {
        if (!isset($target[$k])) {
            $target[$k] = $v;
            continue;
        }
        if (is_array($target[$k]) and is_array($v)) {
            merge_query_params($target[$k], $v);
            continue;
        }
                $target[$k] = $v;
    }
}


function init_performance_info() {

    global $PERF, $CFG, $USER;

    $PERF = new stdClass();
    $PERF->logwrites = 0;
    if (function_exists('microtime')) {
        $PERF->starttime = microtime();
    }
    if (function_exists('memory_get_usage')) {
        $PERF->startmemory = memory_get_usage();
    }
    if (function_exists('posix_times')) {
        $PERF->startposixtimes = posix_times();
    }
}


function during_initial_install() {
    global $CFG;
    return empty($CFG->rolesactive);
}


function raise_memory_limit($newlimit) {
    global $CFG;

    if ($newlimit == MEMORY_UNLIMITED) {
        ini_set('memory_limit', -1);
        return true;

    } else if ($newlimit == MEMORY_STANDARD) {
        if (PHP_INT_SIZE > 4) {
            $newlimit = get_real_size('128M');         } else {
            $newlimit = get_real_size('96M');
        }

    } else if ($newlimit == MEMORY_EXTRA) {
        if (PHP_INT_SIZE > 4) {
            $newlimit = get_real_size('384M');         } else {
            $newlimit = get_real_size('256M');
        }
        if (!empty($CFG->extramemorylimit)) {
            $extra = get_real_size($CFG->extramemorylimit);
            if ($extra > $newlimit) {
                $newlimit = $extra;
            }
        }

    } else if ($newlimit == MEMORY_HUGE) {
                $newlimit = get_real_size('2G');
        if (!empty($CFG->extramemorylimit)) {
            $extra = get_real_size($CFG->extramemorylimit);
            if ($extra > $newlimit) {
                $newlimit = $extra;
            }
        }

    } else {
        $newlimit = get_real_size($newlimit);
    }

    if ($newlimit <= 0) {
        debugging('Invalid memory limit specified.');
        return false;
    }

    $cur = ini_get('memory_limit');
    if (empty($cur)) {
                        $cur = 0;
    } else {
        if ($cur == -1){
            return true;         }
        $cur = get_real_size($cur);
    }

    if ($newlimit > $cur) {
        ini_set('memory_limit', $newlimit);
        return true;
    }
    return false;
}


function reduce_memory_limit($newlimit) {
    if (empty($newlimit)) {
        return false;
    }
    $cur = ini_get('memory_limit');
    if (empty($cur)) {
                        $cur = 0;
    } else {
        if ($cur == -1){
            return true;         }
        $cur = get_real_size($cur);
    }

    $new = get_real_size($newlimit);
        if ($new < $cur && $new != -1) {
        ini_set('memory_limit', $newlimit);
        return true;
    }
    return false;
}


function get_real_size($size = 0) {
    if (!$size) {
        return 0;
    }

    static $binaryprefixes = array(
        'K' => 1024,
        'k' => 1024,
        'M' => 1048576,
        'm' => 1048576,
        'G' => 1073741824,
        'g' => 1073741824,
        'T' => 1099511627776,
        't' => 1099511627776,
    );

    if (preg_match('/^([0-9]+)([KMGT])/i', $size, $matches)) {
        return $matches[1] * $binaryprefixes[$matches[2]];
    }

    return (int) $size;
}


function disable_output_buffering() {
    $olddebug = error_reporting(0);

        if (ini_get_bool('zlib.output_compression')) {
        ini_set('zlib.output_compression', 'Off');
    }

        ob_implicit_flush(true);

            while(ob_get_level()) {
        if (!ob_end_clean()) {
                        break;
        }
    }

        ini_set('output_handler', '');

    error_reporting($olddebug);
}


function redirect_if_major_upgrade_required() {
    global $CFG;
    $lastmajordbchanges = 2014093001.00;
    if (empty($CFG->version) or (float)$CFG->version < $lastmajordbchanges or
            during_initial_install() or !empty($CFG->adminsetuppending)) {
        try {
            @\core\session\manager::terminate_current();
        } catch (Exception $e) {
                    }
        $url = $CFG->wwwroot . '/' . $CFG->admin . '/index.php';
        @header($_SERVER['SERVER_PROTOCOL'] . ' 303 See Other');
        @header('Location: ' . $url);
        echo bootstrap_renderer::plain_redirect_message(htmlspecialchars($url));
        exit;
    }
}


function upgrade_ensure_not_running($warningonly = false) {
    global $CFG;
    if (!empty($CFG->upgraderunning)) {
        if (!$warningonly) {
            throw new moodle_exception('cannotexecduringupgrade');
        } else {
            debugging(get_string('cannotexecduringupgrade', 'error'), DEBUG_DEVELOPER);
            return false;
        }
    }
    return true;
}


function check_dir_exists($dir, $create = true, $recursive = true) {
    global $CFG;

    umask($CFG->umaskpermissions);

    if (is_dir($dir)) {
        return true;
    }

    if (!$create) {
        return false;
    }

    return mkdir($dir, $CFG->directorypermissions, $recursive);
}


function make_unique_writable_directory($basedir, $exceptiononerror = true) {
    if (!is_dir($basedir) || !is_writable($basedir)) {
                if ($exceptiononerror) {
            throw new invalid_dataroot_permissions($basedir . ' is not writable. Unable to create a unique directory within it.');
        } else {
            return false;
        }
    }

    do {
                $uniquedir = $basedir . DIRECTORY_SEPARATOR . generate_uuid();
    } while (
                        is_writable($basedir) &&

                        !make_writable_directory($uniquedir, $exceptiononerror) &&

                        file_exists($uniquedir) && is_dir($uniquedir)
        );

        if (!file_exists($uniquedir) || !is_dir($uniquedir) || !is_writable($uniquedir)) {
        if ($exceptiononerror) {
            throw new invalid_dataroot_permissions('Unique directory creation failed.');
        } else {
            return false;
        }
    }

    return $uniquedir;
}


function make_writable_directory($dir, $exceptiononerror = true) {
    global $CFG;

    if (file_exists($dir) and !is_dir($dir)) {
        if ($exceptiononerror) {
            throw new coding_exception($dir.' directory can not be created, file with the same name already exists.');
        } else {
            return false;
        }
    }

    umask($CFG->umaskpermissions);

    if (!file_exists($dir)) {
        if (!@mkdir($dir, $CFG->directorypermissions, true)) {
            clearstatcache();
                        if (!is_dir($dir)) {
                if ($exceptiononerror) {
                    throw new invalid_dataroot_permissions($dir.' can not be created, check permissions.');
                } else {
                    debugging('Can not create directory: '.$dir, DEBUG_DEVELOPER);
                    return false;
                }
            }
        }
    }

    if (!is_writable($dir)) {
        if ($exceptiononerror) {
            throw new invalid_dataroot_permissions($dir.' is not writable, check permissions.');
        } else {
            return false;
        }
    }

    return $dir;
}


function protect_directory($dir) {
    global $CFG;
        if (!file_exists("$dir/.htaccess")) {
        if ($handle = fopen("$dir/.htaccess", 'w')) {               @fwrite($handle, "deny from all\r\nAllowOverride None\r\nNote: this file is broken intentionally, we do not want anybody to undo it in subdirectory!\r\n");
            @fclose($handle);
            @chmod("$dir/.htaccess", $CFG->filepermissions);
        }
    }
}


function make_upload_directory($directory, $exceptiononerror = true) {
    global $CFG;

    if (strpos($directory, 'temp/') === 0 or $directory === 'temp') {
        debugging('Use make_temp_directory() for creation of temporary directory and $CFG->tempdir to get the location.');

    } else if (strpos($directory, 'cache/') === 0 or $directory === 'cache') {
        debugging('Use make_cache_directory() for creation of cache directory and $CFG->cachedir to get the location.');

    } else if (strpos($directory, 'localcache/') === 0 or $directory === 'localcache') {
        debugging('Use make_localcache_directory() for creation of local cache directory and $CFG->localcachedir to get the location.');
    }

    protect_directory($CFG->dataroot);
    return make_writable_directory("$CFG->dataroot/$directory", $exceptiononerror);
}


function get_request_storage_directory($exceptiononerror = true) {
    global $CFG;

    static $requestdir = null;

    if (!$requestdir || !file_exists($requestdir) || !is_dir($requestdir) || !is_writable($requestdir)) {
        if ($CFG->localcachedir !== "$CFG->dataroot/localcache") {
            check_dir_exists($CFG->localcachedir, true, true);
            protect_directory($CFG->localcachedir);
        } else {
            protect_directory($CFG->dataroot);
        }

        if ($requestdir = make_unique_writable_directory($CFG->localcachedir, $exceptiononerror)) {
                        \core_shutdown_manager::register_function('remove_dir', array($requestdir));
        }
    }

    return $requestdir;
}


function make_request_directory($exceptiononerror = true) {
    $basedir = get_request_storage_directory($exceptiononerror);
    return make_unique_writable_directory($basedir, $exceptiononerror);
}


function make_temp_directory($directory, $exceptiononerror = true) {
    global $CFG;
    if ($CFG->tempdir !== "$CFG->dataroot/temp") {
        check_dir_exists($CFG->tempdir, true, true);
        protect_directory($CFG->tempdir);
    } else {
        protect_directory($CFG->dataroot);
    }
    return make_writable_directory("$CFG->tempdir/$directory", $exceptiononerror);
}


function make_cache_directory($directory, $exceptiononerror = true) {
    global $CFG;
    if ($CFG->cachedir !== "$CFG->dataroot/cache") {
        check_dir_exists($CFG->cachedir, true, true);
        protect_directory($CFG->cachedir);
    } else {
        protect_directory($CFG->dataroot);
    }
    return make_writable_directory("$CFG->cachedir/$directory", $exceptiononerror);
}


function make_localcache_directory($directory, $exceptiononerror = true) {
    global $CFG;

    make_writable_directory($CFG->localcachedir, $exceptiononerror);

    if ($CFG->localcachedir !== "$CFG->dataroot/localcache") {
        protect_directory($CFG->localcachedir);
    } else {
        protect_directory($CFG->dataroot);
    }

    if (!isset($CFG->localcachedirpurged)) {
        $CFG->localcachedirpurged = 0;
    }
    $timestampfile = "$CFG->localcachedir/.lastpurged";

    if (!file_exists($timestampfile)) {
        touch($timestampfile);
        @chmod($timestampfile, $CFG->filepermissions);

    } else if (filemtime($timestampfile) <  $CFG->localcachedirpurged) {
                remove_dir($CFG->localcachedir, true);
        if ($CFG->localcachedir !== "$CFG->dataroot/localcache") {
            protect_directory($CFG->localcachedir);
        }
        touch($timestampfile);
        @chmod($timestampfile, $CFG->filepermissions);
        clearstatcache();
    }

    if ($directory === '') {
        return $CFG->localcachedir;
    }

    return make_writable_directory("$CFG->localcachedir/$directory", $exceptiononerror);
}


class bootstrap_renderer {
    
    protected $initialising = false;

    
    public function has_started() {
        return false;
    }

    
    public function __call($method, $arguments) {
        global $OUTPUT, $PAGE;

        $recursing = false;
        if ($method == 'notification') {
                        $backtrace = debug_backtrace();
            array_shift($backtrace);
            array_shift($backtrace);
            $recursing = is_early_init($backtrace);
        }

        $earlymethods = array(
            'fatal_error' => 'early_error',
            'notification' => 'early_notification',
        );

                if (!empty($PAGE) && !$recursing) {
            if (array_key_exists($method, $earlymethods)) {
                                $PAGE->set_context(null);
            }
            $PAGE->initialise_theme_and_output();
            return call_user_func_array(array($OUTPUT, $method), $arguments);
        }

        $this->initialising = true;

                if (array_key_exists($method, $earlymethods)) {
            return call_user_func_array(array('bootstrap_renderer', $earlymethods[$method]), $arguments);
        }

        throw new coding_exception('Attempt to start output before enough information is known to initialise the theme.');
    }

    
    public static function early_error_content($message, $moreinfourl, $link, $backtrace, $debuginfo = null) {
        global $CFG;

        $content = '<div style="margin-top: 6em; margin-left:auto; margin-right:auto; color:#990000; text-align:center; font-size:large; border-width:1px;
border-color:black; background-color:#ffffee; border-style:solid; border-radius: 20px; border-collapse: collapse;
width: 80%; -moz-border-radius: 20px; padding: 15px">
' . $message . '
</div>';
                $debug = (!empty($CFG->debug) && $CFG->debug >= DEBUG_DEVELOPER);
                        $debug = $debug || (!empty($CFG->config_php_settings['debug'])  && $CFG->config_php_settings['debug'] >= DEBUG_DEVELOPER );
        if ($debug) {
            if (!empty($debuginfo)) {
                $debuginfo = s($debuginfo);                 $debuginfo = str_replace("\n", '<br />', $debuginfo);                 $content .= '<div class="notifytiny">Debug info: ' . $debuginfo . '</div>';
            }
            if (!empty($backtrace)) {
                $content .= '<div class="notifytiny">Stack trace: ' . format_backtrace($backtrace, false) . '</div>';
            }
        }

        return $content;
    }

    
    public static function early_error($message, $moreinfourl, $link, $backtrace, $debuginfo = null, $errorcode = null) {
        global $CFG;

        if (CLI_SCRIPT) {
            echo "!!! $message !!!\n";
            if (!empty($CFG->debug) and $CFG->debug >= DEBUG_DEVELOPER) {
                if (!empty($debuginfo)) {
                    echo "\nDebug info: $debuginfo";
                }
                if (!empty($backtrace)) {
                    echo "\nStack trace: " . format_backtrace($backtrace, true);
                }
            }
            return;

        } else if (AJAX_SCRIPT) {
            $e = new stdClass();
            $e->error      = $message;
            $e->stacktrace = NULL;
            $e->debuginfo  = NULL;
            if (!empty($CFG->debug) and $CFG->debug >= DEBUG_DEVELOPER) {
                if (!empty($debuginfo)) {
                    $e->debuginfo = $debuginfo;
                }
                if (!empty($backtrace)) {
                    $e->stacktrace = format_backtrace($backtrace, true);
                }
            }
            $e->errorcode  = $errorcode;
            @header('Content-Type: application/json; charset=utf-8');
            echo json_encode($e);
            return;
        }

                        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        @header($protocol . ' 503 Service Unavailable');

                @header('Content-Type: text/html; charset=utf-8');
        @header('X-UA-Compatible: IE=edge');
        @header('Cache-Control: no-store, no-cache, must-revalidate');
        @header('Cache-Control: post-check=0, pre-check=0', false);
        @header('Pragma: no-cache');
        @header('Expires: Mon, 20 Aug 1969 09:23:00 GMT');
        @header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

        if (function_exists('get_string')) {
            $strerror = get_string('error');
        } else {
            $strerror = 'Error';
        }

        $content = self::early_error_content($message, $moreinfourl, $link, $backtrace, $debuginfo);

        return self::plain_page($strerror, $content);
    }

    
    public static function early_notification($message, $classes = 'notifyproblem') {
        return '<div class="' . $classes . '">' . $message . '</div>';
    }

    
    public static function plain_redirect_message($encodedurl) {
        $message = '<div style="margin-top: 3em; margin-left:auto; margin-right:auto; text-align:center;">' . get_string('pageshouldredirect') . '<br /><a href="'.
                $encodedurl .'">'. get_string('continue') .'</a></div>';
        return self::plain_page(get_string('redirect'), $message);
    }

    
    public static function early_redirect_message($encodedurl, $message, $delay) {
        $meta = '<meta http-equiv="refresh" content="'. $delay .'; url='. $encodedurl .'" />';
        $content = self::early_error_content($message, null, null, null);
        $content .= self::plain_redirect_message($encodedurl);

        return self::plain_page(get_string('redirect'), $content, $meta);
    }

    
    public static function plain_page($title, $content, $meta = '') {
        if (function_exists('get_string') && function_exists('get_html_lang')) {
            $htmllang = get_html_lang();
        } else {
            $htmllang = '';
        }

        $footer = '';
        if (MDL_PERF_TEST) {
            $perfinfo = get_performance_info();
            $footer = '<footer>' . $perfinfo['html'] . '</footer>';
        }

        return '<!DOCTYPE html>
<html ' . $htmllang . '>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
'.$meta.'
<title>' . $title . '</title>
</head><body>' . $content . $footer . '</body></html>';
    }
}
