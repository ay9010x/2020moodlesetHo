<?php



namespace tool_monitor\task;


class clean_events extends \core\task\scheduled_task {

    
    public function get_name() {
        return get_string('taskcleanevents', 'tool_monitor');
    }

    
    public function execute() {
        global $DB;

        if (!get_config('tool_monitor', 'enablemonitor')) {
            return;         }

                $courses = array();

                        if ($siterules = $DB->get_recordset('tool_monitor_rules', array('courseid' => 0), 'timewindow DESC')) {
                        foreach ($siterules as $rule) {
                                if (isset($courses[$rule->courseid][$rule->eventname])) {
                    continue;
                }
                                $courses[$rule->courseid][$rule->eventname] = $rule->timewindow;
                                $DB->delete_records_select('tool_monitor_events', 'eventname = :eventname AND
                    courseid = :courseid AND timecreated <= :timewindow',
                    array('eventname' => $rule->eventname, 'courseid' => $rule->courseid,
                        'timewindow' => time() - $rule->timewindow));
            }
                        $siterules->close();
        }

                        if ($rules = $DB->get_recordset_select('tool_monitor_rules', 'courseid != 0', array(), 'timewindow DESC')) {
                        foreach ($rules as $rule) {
                                if (isset($courses[$rule->courseid][$rule->eventname])) {
                    continue;
                }
                                $courses[$rule->courseid][$rule->eventname] = $rule->timewindow;
                                                $timewindow = $rule->timewindow;
                if (isset($courses[0][$rule->eventname]) && ($courses[0][$rule->eventname] > $timewindow)) {
                    $timewindow = $courses[0][$rule->eventname];
                }
                                $DB->delete_records_select('tool_monitor_events', 'eventname = :eventname AND
                    courseid = :courseid AND timecreated <= :timewindow',
                        array('eventname' => $rule->eventname, 'courseid' => $rule->courseid,
                            'timewindow' => time() - $timewindow));
            }
                        $rules->close();
        }

        if ($siterules || $rules) {                         $allevents = array();
            foreach ($courses as $key => $value) {
                foreach ($courses[$key] as $event => $notused) {
                    $allevents[] = $event;
                }
            }
                                                            if ($events = $DB->get_recordset('tool_monitor_events')) {
                                $eventstodelete = array();
                                $now = time();
                foreach ($events as $event) {
                                                            if (!isset($courses[$event->courseid][$event->eventname]) && (!isset($courses[0][$event->eventname])
                        || $courses[0][$event->eventname] <= ($now - $event->timecreated))) {
                        $eventstodelete[] = $event->id;
                    }
                }
                                $events->close();

                                if (!empty($eventstodelete)) {
                    list($eventidsql, $params) = $DB->get_in_or_equal($eventstodelete);
                    $DB->delete_records_select('tool_monitor_events', "id $eventidsql", $params);
                }
            }

                        if (!empty($allevents)) {
                list($eventnamesql, $params) = $DB->get_in_or_equal($allevents, SQL_PARAMS_QM, 'param', false);
                $DB->delete_records_select('tool_monitor_events', "eventname $eventnamesql", $params);
            }
        } else {             $DB->delete_records('tool_monitor_events');
        }
    }
}
