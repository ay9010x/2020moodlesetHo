<?php



require_once(__DIR__ . '/../testing/lib.php');

define('BEHAT_EXITCODE_CONFIG', 250);
define('BEHAT_EXITCODE_REQUIREMENT', 251);
define('BEHAT_EXITCODE_PERMISSIONS', 252);
define('BEHAT_EXITCODE_REINSTALL', 253);
define('BEHAT_EXITCODE_INSTALL', 254);
define('BEHAT_EXITCODE_INSTALLED', 256);


define('BEHAT_PARALLEL_SITE_NAME', "behatrun");


function behat_error($errorcode, $text = '') {

        switch ($errorcode) {
        case BEHAT_EXITCODE_CONFIG:
            $text = 'Behat config error: ' . $text;
            break;
        case BEHAT_EXITCODE_REQUIREMENT:
            $text = 'Behat requirement not satisfied: ' . $text;
            break;
        case BEHAT_EXITCODE_PERMISSIONS:
            $text = 'Behat permissions problem: ' . $text . ', check the permissions';
            break;
        case BEHAT_EXITCODE_REINSTALL:
            $path = testing_cli_argument_path('/admin/tool/behat/cli/init.php');
            $text = "Reinstall Behat: ".$text.", use:\n php ".$path;
            break;
        case BEHAT_EXITCODE_INSTALL:
            $path = testing_cli_argument_path('/admin/tool/behat/cli/init.php');
            $text = "Install Behat before enabling it, use:\n php ".$path;
            break;
        case BEHAT_EXITCODE_INSTALLED:
            $text = "The Behat site is already installed";
            break;
        default:
            $text = 'Unknown error ' . $errorcode . ' ' . $text;
            break;
    }

    testing_error($errorcode, $text);
}


function behat_get_error_string($errtype) {
    switch ($errtype) {
        case E_USER_ERROR:
            $errnostr = 'Fatal error';
            break;
        case E_WARNING:
        case E_USER_WARNING:
            $errnostr = 'Warning';
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
        case E_STRICT:
            $errnostr = 'Notice';
            break;
        case E_RECOVERABLE_ERROR:
            $errnostr = 'Catchable';
            break;
        default:
            $errnostr = 'Unknown error type';
    }

    return $errnostr;
}


function behat_error_handler($errno, $errstr, $errfile, $errline, $errcontext) {

        if (!error_reporting()) {
        return true;
    }

                    $respect = array(E_NOTICE, E_USER_NOTICE, E_STRICT, E_WARNING, E_USER_WARNING);
    foreach ($respect as $respectable) {

                        if ($errno == $respectable && !(error_reporting() & $respectable)) {
            return true;
        }
    }

        default_error_handler($errno, $errstr, $errfile, $errline, $errcontext);

    $errnostr = behat_get_error_string($errno);

        if (defined('AJAX_SCRIPT')) {
        throw new Exception("$errnostr: $errstr in $errfile on line $errline");
    } else {
                echo '<div class="phpdebugmessage" data-rel="phpdebugmessage">' . PHP_EOL;
        echo "$errnostr: $errstr in $errfile on line $errline" . PHP_EOL;
        echo '</div>';
    }

        return false;
}


function behat_shutdown_function() {
        if ($error = error_get_last()) {
                if (isset($error['type']) && !($error['type'] & E_WARNING)) {

            $errors = behat_get_shutdown_process_errors();

            $errors[] = $error;
            $errorstosave = json_encode($errors);

            set_config('process_errors', $errorstosave, 'tool_behat');
        }
    }
}


function behat_get_shutdown_process_errors() {
    global $DB;

        $phperrors = $DB->get_field('config_plugins', 'value', array('name' => 'process_errors', 'plugin' => 'tool_behat'));

    if (!empty($phperrors)) {
        return json_decode($phperrors, true);
    } else {
        return array();
    }
}


function behat_clean_init_config() {
    global $CFG;

    $allowed = array_flip(array(
        'wwwroot', 'dataroot', 'dirroot', 'admin', 'directorypermissions', 'filepermissions',
        'umaskpermissions', 'dbtype', 'dblibrary', 'dbhost', 'dbname', 'dbuser', 'dbpass', 'prefix',
        'dboptions', 'proxyhost', 'proxyport', 'proxytype', 'proxyuser', 'proxypassword',
        'proxybypass', 'theme', 'pathtogs', 'pathtodu', 'aspellpath', 'pathtodot', 'skiplangupgrade',
        'altcacheconfigpath', 'pathtounoconv'
    ));

        if (!empty($CFG->behat_extraallowedsettings)) {
        $allowed = array_merge($allowed, array_flip($CFG->behat_extraallowedsettings));
    }

        foreach ($CFG as $key => $value) {
        if (!isset($allowed[$key]) && strpos($key, 'behat_') !== 0) {
            unset($CFG->{$key});
        }
    }
}


function behat_check_config_vars() {
    global $CFG;

        if (empty($CFG->behat_prefix)) {
        behat_error(BEHAT_EXITCODE_CONFIG,
            'Define $CFG->behat_prefix in config.php');
    }
    if (!empty($CFG->prefix) and $CFG->behat_prefix == $CFG->prefix) {
        behat_error(BEHAT_EXITCODE_CONFIG,
            '$CFG->behat_prefix in config.php must be different from $CFG->prefix');
    }
    if (!empty($CFG->phpunit_prefix) and $CFG->behat_prefix == $CFG->phpunit_prefix) {
        behat_error(BEHAT_EXITCODE_CONFIG,
            '$CFG->behat_prefix in config.php must be different from $CFG->phpunit_prefix');
    }

        if (empty($CFG->behat_wwwroot)) {
        behat_error(BEHAT_EXITCODE_CONFIG,
            'Define $CFG->behat_wwwroot in config.php');
    }
    if (!empty($CFG->wwwroot) and $CFG->behat_wwwroot == $CFG->wwwroot) {
        behat_error(BEHAT_EXITCODE_CONFIG,
            '$CFG->behat_wwwroot in config.php must be different from $CFG->wwwroot');
    }

        if (empty($CFG->behat_dataroot)) {
        behat_error(BEHAT_EXITCODE_CONFIG,
            'Define $CFG->behat_dataroot in config.php');
    }
    clearstatcache();
    if (!file_exists($CFG->behat_dataroot)) {
        $permissions = isset($CFG->directorypermissions) ? $CFG->directorypermissions : 02777;
        umask(0);
        if (!mkdir($CFG->behat_dataroot, $permissions, true)) {
            behat_error(BEHAT_EXITCODE_PERMISSIONS, '$CFG->behat_dataroot directory can not be created');
        }
    }
    $CFG->behat_dataroot = realpath($CFG->behat_dataroot);
    if (empty($CFG->behat_dataroot) or !is_dir($CFG->behat_dataroot) or !is_writable($CFG->behat_dataroot)) {
        behat_error(BEHAT_EXITCODE_CONFIG,
            '$CFG->behat_dataroot in config.php must point to an existing writable directory');
    }
    if (!empty($CFG->dataroot) and $CFG->behat_dataroot == realpath($CFG->dataroot)) {
        behat_error(BEHAT_EXITCODE_CONFIG,
            '$CFG->behat_dataroot in config.php must be different from $CFG->dataroot');
    }
    if (!empty($CFG->phpunit_dataroot) and $CFG->behat_dataroot == realpath($CFG->phpunit_dataroot)) {
        behat_error(BEHAT_EXITCODE_CONFIG,
            '$CFG->behat_dataroot in config.php must be different from $CFG->phpunit_dataroot');
    }
}


function behat_is_test_site() {
    global $CFG;

    if (defined('BEHAT_UTIL')) {
                return true;
    }
    if (defined('BEHAT_TEST')) {
                return true;
    }
    if (empty($CFG->behat_wwwroot)) {
        return false;
    }
    if (isset($_SERVER['REMOTE_ADDR']) and behat_is_requested_url($CFG->behat_wwwroot)) {
                return true;
    }

    return false;
}


function behat_update_vars_for_process() {
    global $CFG;

    $allowedconfigoverride = array('dbtype', 'dblibrary', 'dbhost', 'dbname', 'dbuser', 'dbpass', 'behat_prefix',
        'behat_wwwroot', 'behat_dataroot');
    $behatrunprocess = behat_get_run_process();
    $CFG->behatrunprocess = $behatrunprocess;

    if ($behatrunprocess) {
        if (empty($CFG->behat_parallel_run[$behatrunprocess - 1]['behat_wwwroot'])) {
                        if (isset($CFG->behat_wwwroot) &&
                !preg_match("#/" . BEHAT_PARALLEL_SITE_NAME . $behatrunprocess . "\$#", $CFG->behat_wwwroot)) {
                $CFG->behat_wwwroot .= "/" . BEHAT_PARALLEL_SITE_NAME . $behatrunprocess;
            }
        }

        if (empty($CFG->behat_parallel_run[$behatrunprocess - 1]['behat_dataroot'])) {
                        if (!preg_match("#" . $behatrunprocess . "\$#", $CFG->behat_dataroot)) {
                $CFG->behat_dataroot .= $behatrunprocess;
            }
        }

                                if ($CFG->dbtype === 'oci') {
            $CFG->behat_prefix = substr($CFG->behat_prefix, 0, 1);
            $CFG->behat_prefix .= "{$behatrunprocess}";
        } else {
            $CFG->behat_prefix .= "{$behatrunprocess}_";
        }

        if (!empty($CFG->behat_parallel_run[$behatrunprocess - 1])) {
                        foreach ($allowedconfigoverride as $config) {
                if (isset($CFG->behat_parallel_run[$behatrunprocess - 1][$config])) {
                    $CFG->$config = $CFG->behat_parallel_run[$behatrunprocess - 1][$config];
                }
            }
        }
    }
}


function behat_is_requested_url($url) {

    $parsedurl = parse_url($url . '/');
    $parsedurl['port'] = isset($parsedurl['port']) ? $parsedurl['port'] : 80;
    $parsedurl['path'] = rtrim($parsedurl['path'], '/');

        $pos = strpos($_SERVER['HTTP_HOST'], ':');
    if ($pos !== false) {
        $requestedhost = substr($_SERVER['HTTP_HOST'], 0, $pos);
    } else {
        $requestedhost = $_SERVER['HTTP_HOST'];
    }

        if (empty($parsedurl['path'])) {
        $matchespath = true;
    } else if (strpos($_SERVER['SCRIPT_NAME'], $parsedurl['path']) === 0) {
        $matchespath = true;
    }

        if ($parsedurl['host'] == $requestedhost && $parsedurl['port'] == $_SERVER['SERVER_PORT'] && !empty($matchespath)) {
        return true;
    }

    return false;
}


function behat_get_run_process() {
    global $argv, $CFG;
    $behatrunprocess = false;

        if (defined('BEHAT_CURRENT_RUN') && BEHAT_CURRENT_RUN) {
        $behatrunprocess = BEHAT_CURRENT_RUN;
    } else if (!empty($_SERVER['REMOTE_ADDR'])) {
                if (!empty($CFG->behat_parallel_run)) {
            foreach ($CFG->behat_parallel_run as $run => $behatconfig) {
                if (isset($behatconfig['behat_wwwroot']) && behat_is_requested_url($behatconfig['behat_wwwroot'])) {
                    $behatrunprocess = $run + 1;                     break;
                }
            }
        }
                if (empty($behatrunprocess) && preg_match('#/' . BEHAT_PARALLEL_SITE_NAME . '(.+?)/#', $_SERVER['REQUEST_URI'])) {
            $dirrootrealpath = str_replace("\\", "/", realpath($CFG->dirroot));
            $serverrealpath = str_replace("\\", "/", realpath($_SERVER['SCRIPT_FILENAME']));
            $afterpath = str_replace($dirrootrealpath.'/', '', $serverrealpath);
            if (!$behatrunprocess = preg_filter("#.*/" . BEHAT_PARALLEL_SITE_NAME . "(.+?)/$afterpath#", '$1',
                $_SERVER['SCRIPT_FILENAME'])) {
                throw new Exception("Unable to determine behat process [afterpath=" . $afterpath .
                    ", scriptfilename=" . $_SERVER['SCRIPT_FILENAME'] . "]!");
            }
        }
    } else if (defined('BEHAT_TEST') || defined('BEHAT_UTIL')) {
        if ($match = preg_filter('#--run=(.+)#', '$1', $argv)) {
            $behatrunprocess = reset($match);
        } else if ($k = array_search('--config', $argv)) {
            $behatconfig = str_replace("\\", "/", $argv[$k + 1]);
                        if (!empty($CFG->behat_parallel_run)) {
                foreach ($CFG->behat_parallel_run as $run => $parallelconfig) {
                    if (!empty($parallelconfig['behat_dataroot']) &&
                        $parallelconfig['behat_dataroot'] . '/behat/behat.yml' == $behatconfig) {

                        $behatrunprocess = $run + 1;                         break;
                    }
                }
            }
                        if (empty($behatrunprocess)) {
                $behatdataroot = str_replace("\\", "/", $CFG->behat_dataroot);
                $behatrunprocess = preg_filter("#^{$behatdataroot}" . "(.+?)[/|\\\]behat[/|\\\]behat\.yml#", '$1',
                    $behatconfig);
            }
        }
    }

    return $behatrunprocess;
}


function cli_execute_parallel($cmds, $cwd = null) {
    require_once(__DIR__ . "/../../vendor/autoload.php");

    $processes = array();

        foreach ($cmds as $name => $cmd) {
        $process = new Symfony\Component\Process\Process($cmd);

        $process->setWorkingDirectory($cwd);
        $process->setTimeout(null);
        $processes[$name] = $process;
        $processes[$name]->start();

                if ($processes[$name]->getStatus() !== 'started') {
            echo "Error starting process: $name";
            foreach ($processes[$name] as $process) {
                if ($process) {
                    $process->signal(SIGKILL);
                }
            }
            exit(1);
        }
    }
    return $processes;
}
