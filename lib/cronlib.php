<?php




function cron_run() {
    global $DB, $CFG, $OUTPUT;

    if (CLI_MAINTENANCE) {
        echo "CLI maintenance mode active, cron execution suspended.\n";
        exit(1);
    }

    if (moodle_needs_upgrading()) {
        echo "Moodle upgrade pending, cron execution suspended.\n";
        exit(1);
    }

    require_once($CFG->libdir.'/adminlib.php');

    if (!empty($CFG->showcronsql)) {
        $DB->set_debug(true);
    }
    if (!empty($CFG->showcrondebugging)) {
        set_debugging(DEBUG_DEVELOPER, true);
    }

    core_php_time_limit::raise();
    $starttime = microtime();

        raise_memory_limit(MEMORY_EXTRA);

        cron_setup_user();

        $timenow  = time();
    mtrace("Server Time: ".date('r', $timenow)."\n\n");

        while (!\core\task\manager::static_caches_cleared_since($timenow) &&
           $task = \core\task\manager::get_next_scheduled_task($timenow)) {
        $fullname = $task->get_name() . ' (' . get_class($task) . ')';
        mtrace('Execute scheduled task: ' . $fullname);
        cron_trace_time_and_memory();
        $predbqueries = null;
        $predbqueries = $DB->perf_get_queries();
        $pretime      = microtime(1);
        try {
            get_mailer('buffer');
            $task->execute();
            if ($DB->is_transaction_started()) {
                throw new coding_exception("Task left transaction open");
            }
            if (isset($predbqueries)) {
                mtrace("... used " . ($DB->perf_get_queries() - $predbqueries) . " dbqueries");
                mtrace("... used " . (microtime(1) - $pretime) . " seconds");
            }
            mtrace('Scheduled task complete: ' . $fullname);
            \core\task\manager::scheduled_task_complete($task);
        } catch (Exception $e) {
            if ($DB && $DB->is_transaction_started()) {
                error_log('Database transaction aborted automatically in ' . get_class($task));
                $DB->force_transaction_rollback();
            }
            if (isset($predbqueries)) {
                mtrace("... used " . ($DB->perf_get_queries() - $predbqueries) . " dbqueries");
                mtrace("... used " . (microtime(1) - $pretime) . " seconds");
            }
            mtrace('Scheduled task failed: ' . $fullname . ',' . $e->getMessage());
            if ($CFG->debugdeveloper) {
                 if (!empty($e->debuginfo)) {
                    mtrace("Debug info:");
                    mtrace($e->debuginfo);
                }
                mtrace("Backtrace:");
                mtrace(format_backtrace($e->getTrace(), true));
            }
            \core\task\manager::scheduled_task_failed($task);
        }
        get_mailer('close');
        unset($task);
    }

        while (!\core\task\manager::static_caches_cleared_since($timenow) &&
           $task = \core\task\manager::get_next_adhoc_task($timenow)) {
        mtrace("Execute adhoc task: " . get_class($task));
        cron_trace_time_and_memory();
        $predbqueries = null;
        $predbqueries = $DB->perf_get_queries();
        $pretime      = microtime(1);
        try {
            get_mailer('buffer');
            $task->execute();
            if ($DB->is_transaction_started()) {
                throw new coding_exception("Task left transaction open");
            }
            if (isset($predbqueries)) {
                mtrace("... used " . ($DB->perf_get_queries() - $predbqueries) . " dbqueries");
                mtrace("... used " . (microtime(1) - $pretime) . " seconds");
            }
            mtrace("Adhoc task complete: " . get_class($task));
            \core\task\manager::adhoc_task_complete($task);
        } catch (Exception $e) {
            if ($DB && $DB->is_transaction_started()) {
                error_log('Database transaction aborted automatically in ' . get_class($task));
                $DB->force_transaction_rollback();
            }
            if (isset($predbqueries)) {
                mtrace("... used " . ($DB->perf_get_queries() - $predbqueries) . " dbqueries");
                mtrace("... used " . (microtime(1) - $pretime) . " seconds");
            }
            mtrace("Adhoc task failed: " . get_class($task) . "," . $e->getMessage());
            if ($CFG->debugdeveloper) {
                 if (!empty($e->debuginfo)) {
                    mtrace("Debug info:");
                    mtrace($e->debuginfo);
                }
                mtrace("Backtrace:");
                mtrace(format_backtrace($e->getTrace(), true));
            }
            \core\task\manager::adhoc_task_failed($task);
        }
        get_mailer('close');
        unset($task);
    }

    mtrace("Cron script completed correctly");

    gc_collect_cycles();
    mtrace('Cron completed at ' . date('H:i:s') . '. Memory used ' . display_size(memory_get_usage()) . '.');
    $difftime = microtime_diff($starttime, microtime());
    mtrace("Execution took ".$difftime." seconds");
}


function cron_trace_time_and_memory() {
    gc_collect_cycles();
    mtrace('... started ' . date('H:i:s') . '. Current memory use ' . display_size(memory_get_usage()) . '.');
}


function cron_execute_plugin_type($plugintype, $description = null) {
    global $DB;

        $plugins = get_plugin_list_with_function($plugintype, 'cron');

        $plugins = cron_bc_hack_plugin_functions($plugintype, $plugins);

        if (!$plugins) {
        return;
    }

    if ($description) {
        mtrace('Starting '.$description);
    }

    foreach ($plugins as $component=>$cronfunction) {
        $dir = core_component::get_component_directory($component);

                $cronperiod = 0;
        if (file_exists("$dir/version.php")) {
            $plugin = new stdClass();
            include("$dir/version.php");
            if (isset($plugin->cron)) {
                $cronperiod = $plugin->cron;
            }
        }

                $lastcron = get_config($component, 'lastcron');
        if ($cronperiod && $lastcron) {
            if ($lastcron + $cronperiod > time()) {
                                continue;
            }
        }

        mtrace('Processing cron function for ' . $component . '...');
        cron_trace_time_and_memory();
        $pre_dbqueries = $DB->perf_get_queries();
        $pre_time = microtime(true);

        $cronfunction();

        mtrace("done. (" . ($DB->perf_get_queries() - $pre_dbqueries) . " dbqueries, " .
                round(microtime(true) - $pre_time, 2) . " seconds)");

        set_config('lastcron', time(), $component);
        core_php_time_limit::raise();
    }

    if ($description) {
        mtrace('Finished ' . $description);
    }
}


function cron_bc_hack_plugin_functions($plugintype, $plugins) {
    global $CFG; 
    if ($plugintype === 'report') {
                        foreach (core_component::get_plugin_list($plugintype) as $pluginname=>$dir) {
            $component = $plugintype . '_' . $pluginname;
            if (isset($plugins[$component])) {
                                continue;
            }
            if (!file_exists("$dir/cron.php")) {
                                continue;
            }
            include_once("$dir/cron.php");
            $cronfunction = $component . '_cron';
            if (function_exists($cronfunction)) {
                $plugins[$component] = $cronfunction;
            } else {
                debugging("Invalid legacy cron.php detected in $component, " .
                        "please use lib.php instead");
            }
        }
    } else if (strpos($plugintype, 'grade') === 0) {
                                foreach(core_component::get_plugin_list($plugintype) as $pluginname=>$dir) {
            $component = $plugintype.'_'.$pluginname;
            if (isset($plugins[$component])) {
                                continue;
            }
            if (!file_exists("$dir/lib.php")) {
                continue;
            }
            include_once("$dir/lib.php");
            $cronfunction = str_replace('grade', 'grade_', $plugintype) . '_' .
                    $pluginname . '_cron';
            if (function_exists($cronfunction)) {
                $plugins[$component] = $cronfunction;
            }
        }
    }

    return $plugins;
}
