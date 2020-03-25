<?php


namespace core\task;

define('CORE_TASK_TASKS_FILENAME', 'db/tasks.php');

class manager {

    
    public static function load_default_scheduled_tasks_for_component($componentname) {
        $dir = \core_component::get_component_directory($componentname);

        if (!$dir) {
            return array();
        }

        $file = $dir . '/' . CORE_TASK_TASKS_FILENAME;
        if (!file_exists($file)) {
            return array();
        }

        $tasks = null;
        include($file);

        if (!isset($tasks)) {
            return array();
        }

        $scheduledtasks = array();

        foreach ($tasks as $task) {
            $record = (object) $task;
            $scheduledtask = self::scheduled_task_from_record($record);
                        if ($scheduledtask) {
                $scheduledtask->set_component($componentname);
                $scheduledtasks[] = $scheduledtask;
            }
        }

        return $scheduledtasks;
    }

    
    public static function reset_scheduled_tasks_for_component($componentname) {
        global $DB;
        $tasks = self::load_default_scheduled_tasks_for_component($componentname);
        $validtasks = array();

        foreach ($tasks as $taskid => $task) {
            $classname = get_class($task);
            if (strpos($classname, '\\') !== 0) {
                $classname = '\\' . $classname;
            }

            $validtasks[] = $classname;

            if ($currenttask = self::get_scheduled_task($classname)) {
                if ($currenttask->is_customised()) {
                                        continue;
                }

                                self::configure_scheduled_task($task);
            } else {
                                $task->set_next_run_time($task->get_next_scheduled_time());

                                $record = self::record_from_scheduled_task($task);
                $DB->insert_record('task_scheduled', $record);
            }
        }

                $sql = "component = :component";
        $params = array('component' => $componentname);
        if (!empty($validtasks)) {
            list($insql, $inparams) = $DB->get_in_or_equal($validtasks, SQL_PARAMS_NAMED, 'param', false);
            $sql .= ' AND classname ' . $insql;
            $params = array_merge($params, $inparams);
        }
        $DB->delete_records_select('task_scheduled', $sql, $params);
    }

    
    public static function queue_adhoc_task(adhoc_task $task) {
        global $DB;

        $record = self::record_from_adhoc_task($task);
                if (!$task->get_next_run_time()) {
            $record->nextruntime = time() - 1;
        }
        $result = $DB->insert_record('task_adhoc', $record);

        return $result;
    }

    
    public static function configure_scheduled_task(scheduled_task $task) {
        global $DB;

        $classname = get_class($task);
        if (strpos($classname, '\\') !== 0) {
            $classname = '\\' . $classname;
        }

        $original = $DB->get_record('task_scheduled', array('classname'=>$classname), 'id', MUST_EXIST);

        $record = self::record_from_scheduled_task($task);
        $record->id = $original->id;
        $record->nextruntime = $task->get_next_scheduled_time();
        $result = $DB->update_record('task_scheduled', $record);

        return $result;
    }

    
    public static function record_from_scheduled_task($task) {
        $record = new \stdClass();
        $record->classname = get_class($task);
        if (strpos($record->classname, '\\') !== 0) {
            $record->classname = '\\' . $record->classname;
        }
        $record->component = $task->get_component();
        $record->blocking = $task->is_blocking();
        $record->customised = $task->is_customised();
        $record->lastruntime = $task->get_last_run_time();
        $record->nextruntime = $task->get_next_run_time();
        $record->faildelay = $task->get_fail_delay();
        $record->hour = $task->get_hour();
        $record->minute = $task->get_minute();
        $record->day = $task->get_day();
        $record->dayofweek = $task->get_day_of_week();
        $record->month = $task->get_month();
        $record->disabled = $task->get_disabled();

        return $record;
    }

    
    public static function record_from_adhoc_task($task) {
        $record = new \stdClass();
        $record->classname = get_class($task);
        if (strpos($record->classname, '\\') !== 0) {
            $record->classname = '\\' . $record->classname;
        }
        $record->id = $task->get_id();
        $record->component = $task->get_component();
        $record->blocking = $task->is_blocking();
        $record->nextruntime = $task->get_next_run_time();
        $record->faildelay = $task->get_fail_delay();
        $record->customdata = $task->get_custom_data_as_string();

        return $record;
    }

    
    public static function adhoc_task_from_record($record) {
        $classname = $record->classname;
        if (strpos($classname, '\\') !== 0) {
            $classname = '\\' . $classname;
        }
        if (!class_exists($classname)) {
            debugging("Failed to load task: " . $classname, DEBUG_DEVELOPER);
            return false;
        }
        $task = new $classname;
        if (isset($record->nextruntime)) {
            $task->set_next_run_time($record->nextruntime);
        }
        if (isset($record->id)) {
            $task->set_id($record->id);
        }
        if (isset($record->component)) {
            $task->set_component($record->component);
        }
        $task->set_blocking(!empty($record->blocking));
        if (isset($record->faildelay)) {
            $task->set_fail_delay($record->faildelay);
        }
        if (isset($record->customdata)) {
            $task->set_custom_data_as_string($record->customdata);
        }

        return $task;
    }

    
    public static function scheduled_task_from_record($record) {
        $classname = $record->classname;
        if (strpos($classname, '\\') !== 0) {
            $classname = '\\' . $classname;
        }
        if (!class_exists($classname)) {
            debugging("Failed to load task: " . $classname, DEBUG_DEVELOPER);
            return false;
        }
        
        $task = new $classname;
        if (isset($record->lastruntime)) {
            $task->set_last_run_time($record->lastruntime);
        }
        if (isset($record->nextruntime)) {
            $task->set_next_run_time($record->nextruntime);
        }
        if (isset($record->customised)) {
            $task->set_customised($record->customised);
        }
        if (isset($record->component)) {
            $task->set_component($record->component);
        }
        $task->set_blocking(!empty($record->blocking));
        if (isset($record->minute)) {
            $task->set_minute($record->minute);
        }
        if (isset($record->hour)) {
            $task->set_hour($record->hour);
        }
        if (isset($record->day)) {
            $task->set_day($record->day);
        }
        if (isset($record->month)) {
            $task->set_month($record->month);
        }
        if (isset($record->dayofweek)) {
            $task->set_day_of_week($record->dayofweek);
        }
        if (isset($record->faildelay)) {
            $task->set_fail_delay($record->faildelay);
        }
        if (isset($record->disabled)) {
            $task->set_disabled($record->disabled);
        }

        return $task;
    }

    
    public static function load_scheduled_tasks_for_component($componentname) {
        global $DB;

        $tasks = array();
                $records = $DB->get_records('task_scheduled', array('component' => $componentname), 'classname', '*', IGNORE_MISSING);
        foreach ($records as $record) {
            $task = self::scheduled_task_from_record($record);
                        if ($task) {
                $tasks[] = $task;
            }
        }

        return $tasks;
    }

    
    public static function get_scheduled_task($classname) {
        global $DB;

        if (strpos($classname, '\\') !== 0) {
            $classname = '\\' . $classname;
        }
                $record = $DB->get_record('task_scheduled', array('classname'=>$classname), '*', IGNORE_MISSING);
        if (!$record) {
            return false;
        }
        return self::scheduled_task_from_record($record);
    }

    
    public static function get_default_scheduled_task($classname) {
        $task = self::get_scheduled_task($classname);
        $componenttasks = array();

                if ($task) {
            $componenttasks = self::load_default_scheduled_tasks_for_component($task->get_component());
        }

        foreach ($componenttasks as $componenttask) {
            if (get_class($componenttask) == get_class($task)) {
                return $componenttask;
            }
        }

        return false;
    }

    
    public static function get_all_scheduled_tasks() {
        global $DB;

        $records = $DB->get_records('task_scheduled', null, 'component, classname', '*', IGNORE_MISSING);
        $tasks = array();

        foreach ($records as $record) {
            $task = self::scheduled_task_from_record($record);
                        if ($task) {
                $tasks[] = $task;
            }
        }

        return $tasks;
    }

    
    public static function get_next_adhoc_task($timestart) {
        global $DB;
        $cronlockfactory = \core\lock\lock_config::get_lock_factory('cron');

        if (!$cronlock = $cronlockfactory->get_lock('core_cron', 10)) {
            throw new \moodle_exception('locktimeout');
        }

        $where = '(nextruntime IS NULL OR nextruntime < :timestart1)';
        $params = array('timestart1' => $timestart);
        $records = $DB->get_records_select('task_adhoc', $where, $params);

        foreach ($records as $record) {

            if ($lock = $cronlockfactory->get_lock('adhoc_' . $record->id, 10)) {
                $classname = '\\' . $record->classname;
                $task = self::adhoc_task_from_record($record);
                                if (!$task) {
                    $lock->release();
                    continue;
                }

                $task->set_lock($lock);
                if (!$task->is_blocking()) {
                    $cronlock->release();
                } else {
                    $task->set_cron_lock($cronlock);
                }
                return $task;
            }
        }

                $cronlock->release();
        return null;
    }

    
    public static function get_next_scheduled_task($timestart) {
        global $DB;
        $cronlockfactory = \core\lock\lock_config::get_lock_factory('cron');

        if (!$cronlock = $cronlockfactory->get_lock('core_cron', 10)) {
            throw new \moodle_exception('locktimeout');
        }

        $where = "(lastruntime IS NULL OR lastruntime < :timestart1)
                  AND (nextruntime IS NULL OR nextruntime < :timestart2)
                  AND disabled = 0
                  ORDER BY lastruntime, id ASC";
        $params = array('timestart1' => $timestart, 'timestart2' => $timestart);
        $records = $DB->get_records_select('task_scheduled', $where, $params);

        $pluginmanager = \core_plugin_manager::instance();

        foreach ($records as $record) {

            if ($lock = $cronlockfactory->get_lock(($record->classname), 10)) {
                $classname = '\\' . $record->classname;
                $task = self::scheduled_task_from_record($record);
                                if (!$task) {
                    $lock->release();
                    continue;
                }

                $task->set_lock($lock);

                                $plugininfo = $pluginmanager->get_plugin_info($task->get_component());

                if ($plugininfo) {
                    if (($plugininfo->is_enabled() === false) && !$task->get_run_if_component_disabled()) {
                        $lock->release();
                        continue;
                    }
                }

                                if (!$DB->record_exists('task_scheduled', (array) $record)) {
                    $lock->release();
                    continue;
                }

                if (!$task->is_blocking()) {
                    $cronlock->release();
                } else {
                    $task->set_cron_lock($cronlock);
                }
                return $task;
            }
        }

                $cronlock->release();
        return null;
    }

    
    public static function adhoc_task_failed(adhoc_task $task) {
        global $DB;
        $delay = $task->get_fail_delay();

                if (empty($delay)) {
            $delay = 60;
        } else {
            $delay *= 2;
        }

                if ($delay > 86400) {
            $delay = 86400;
        }

        $classname = get_class($task);
        if (strpos($classname, '\\') !== 0) {
            $classname = '\\' . $classname;
        }

        $task->set_next_run_time(time() + $delay);
        $task->set_fail_delay($delay);
        $record = self::record_from_adhoc_task($task);
        $DB->update_record('task_adhoc', $record);

        if ($task->is_blocking()) {
            $task->get_cron_lock()->release();
        }
        $task->get_lock()->release();
    }

    
    public static function adhoc_task_complete(adhoc_task $task) {
        global $DB;

                $DB->delete_records('task_adhoc', array('id' => $task->get_id()));

                if ($task->is_blocking()) {
            $task->get_cron_lock()->release();
        }
        $task->get_lock()->release();
    }

    
    public static function scheduled_task_failed(scheduled_task $task) {
        global $DB;

        $delay = $task->get_fail_delay();

                if (empty($delay)) {
            $delay = 60;
        } else {
            $delay *= 2;
        }

                if ($delay > 86400) {
            $delay = 86400;
        }

        $classname = get_class($task);
        if (strpos($classname, '\\') !== 0) {
            $classname = '\\' . $classname;
        }

        $record = $DB->get_record('task_scheduled', array('classname' => $classname));
        $record->nextruntime = time() + $delay;
        $record->faildelay = $delay;
        $DB->update_record('task_scheduled', $record);

        if ($task->is_blocking()) {
            $task->get_cron_lock()->release();
        }
        $task->get_lock()->release();
    }

    
    public static function scheduled_task_complete(scheduled_task $task) {
        global $DB;

        $classname = get_class($task);
        if (strpos($classname, '\\') !== 0) {
            $classname = '\\' . $classname;
        }
        $record = $DB->get_record('task_scheduled', array('classname' => $classname));
        if ($record) {
            $record->lastruntime = time();
            $record->faildelay = 0;
            $record->nextruntime = $task->get_next_scheduled_time();

            $DB->update_record('task_scheduled', $record);
        }

                if ($task->is_blocking()) {
            $task->get_cron_lock()->release();
        }
        $task->get_lock()->release();
    }

    
    public static function clear_static_caches() {
        global $DB;
                $record = $DB->get_record('config', array('name'=>'scheduledtaskreset'));
        if ($record) {
            $record->value = time();
            $DB->update_record('config', $record);
        } else {
            $record = new \stdClass();
            $record->name = 'scheduledtaskreset';
            $record->value = time();
            $DB->insert_record('config', $record);
        }
    }

    
    public static function static_caches_cleared_since($starttime) {
        global $DB;
        $record = $DB->get_record('config', array('name'=>'scheduledtaskreset'));
        return $record && (intval($record->value) > $starttime);
    }
}
