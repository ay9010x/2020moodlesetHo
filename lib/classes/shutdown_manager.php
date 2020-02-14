<?php



defined('MOODLE_INTERNAL') || die();


class core_shutdown_manager {
    
    protected static $callbacks = array();
    
    protected static $registered = false;

    
    public static function initialize() {
        if (self::$registered) {
            debugging('Shutdown manager is already initialised!');
        }
        self::$registered = true;
        register_shutdown_function(array('core_shutdown_manager', 'shutdown_handler'));
    }

    
    public static function register_function($callback, array $params = null) {
        self::$callbacks[] = array($callback, $params);
    }

    
    public static function shutdown_handler() {
        global $DB;

                foreach (self::$callbacks as $data) {
            list($callback, $params) = $data;
            try {
                if (!is_callable($callback)) {
                    error_log('Invalid custom shutdown function detected '.var_export($callback, true));
                    continue;
                }
                if ($params === null) {
                    call_user_func($callback);
                } else {
                    call_user_func_array($callback, $params);
                }
            } catch (Exception $e) {
                error_log('Exception ignored in shutdown function '.var_export($callback, true).':'.$e->getMessage());
            } catch (Throwable $e) {
                                error_log('Exception ignored in shutdown function '.var_export($callback, true).':'.$e->getMessage());
            }
        }

                        if ($DB->is_transaction_started()) {
            if (!defined('PHPUNIT_TEST') or !PHPUNIT_TEST) {
                                                $backtrace = $DB->get_transaction_start_backtrace();
                error_log('Potential coding error - active database transaction detected during request shutdown:'."\n".format_backtrace($backtrace, true));
            }
            $DB->force_transaction_rollback();
        }

                \core\session\manager::write_close();

                self::request_shutdown();

                if (function_exists('profiling_is_running')) {
            if (profiling_is_running()) {
                profiling_stop();
            }
        }

            }

    
    protected static function request_shutdown() {
        global $CFG;

                $apachereleasemem = false;
        if (function_exists('apache_child_terminate') && function_exists('memory_get_usage') && ini_get_bool('child_terminate')) {
            $limit = (empty($CFG->apachemaxmem) ? 64*1024*1024 : $CFG->apachemaxmem);             if (memory_get_usage() > get_real_size($limit)) {
                $apachereleasemem = $limit;
                @apache_child_terminate();
            }
        }

                if (defined('MDL_PERF') || (!empty($CFG->perfdebug) and $CFG->perfdebug > 7)) {
            if ($apachereleasemem) {
                error_log('Mem usage over '.$apachereleasemem.': marking Apache child for reaping.');
            }
            if (defined('MDL_PERFTOLOG')) {
                $perf = get_performance_info();
                error_log("PERF: " . $perf['txt']);
            }
            if (defined('MDL_PERFINC')) {
                $inc = get_included_files();
                $ts  = 0;
                foreach ($inc as $f) {
                    if (preg_match(':^/:', $f)) {
                        $fs = filesize($f);
                        $ts += $fs;
                        $hfs = display_size($fs);
                        error_log(substr($f, strlen($CFG->dirroot)) . " size: $fs ($hfs)", null, null, 0);
                    } else {
                        error_log($f , null, null, 0);
                    }
                }
                if ($ts > 0 ) {
                    $hts = display_size($ts);
                    error_log("Total size of files included: $ts ($hts)");
                }
            }
        }
    }
}
