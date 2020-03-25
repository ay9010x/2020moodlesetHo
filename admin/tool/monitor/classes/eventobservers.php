<?php



namespace tool_monitor;

defined('MOODLE_INTERNAL') || die();


class eventobservers {

    
    protected $buffer = array();

    
    protected $count = 0;

    
    protected static $instance;

    
    public static function course_deleted(\core\event\course_deleted $event) {
        $rules = rule_manager::get_rules_by_courseid($event->courseid, 0, 0, false);
        foreach ($rules as $rule) {
            rule_manager::delete_rule($rule->id, $event->get_context());
        }
    }

    
    public static function process_event(\core\event\base $event) {
        if (!get_config('tool_monitor', 'enablemonitor')) {
            return;         }

        if (empty(self::$instance)) {
            self::$instance = new static();
                        \core_shutdown_manager::register_function(array(self::$instance, 'process_buffer'));
        }

        self::$instance->buffer_event($event);

        if (PHPUNIT_TEST) {
                        self::$instance->process_buffer();

        }
    }

    
    protected function buffer_event(\core\event\base $event) {

                if (!\tool_monitor\subscription_manager::event_has_subscriptions($event->eventname, $event->courseid)) {
            return;
        }

        $eventdata = $event->get_data();
        $eventobj = new \stdClass();
        $eventobj->eventname = $eventdata['eventname'];
        $eventobj->contextid = $eventdata['contextid'];
        $eventobj->contextlevel = $eventdata['contextlevel'];
        $eventobj->contextinstanceid = $eventdata['contextinstanceid'];
        if ($event->get_url()) {
                        $eventobj->link = $event->get_url()->out();
        } else {
            $eventobj->link = '';
        }
        $eventobj->courseid = $eventdata['courseid'];
        $eventobj->timecreated = $eventdata['timecreated'];

        $this->buffer[] = $eventobj;
        $this->count++;
    }

    
    public function process_buffer() {
        global $DB;

        $events = $this->flush(); 
        $select = "SELECT COUNT(id) FROM {tool_monitor_events} ";
        $now = time();
        $messagestosend = array();
        $allsubids = array();

                foreach ($events as $eventobj) {
            $subscriptions = subscription_manager::get_subscriptions_by_event($eventobj);
            $idstosend = array();
            foreach ($subscriptions as $subscription) {
                                if (!subscription_manager::subscription_is_active($subscription)) {
                    continue;
                }
                $starttime = $now - $subscription->timewindow;
                $starttime = ($starttime > $subscription->lastnotificationsent) ? $starttime : $subscription->lastnotificationsent;
                if ($subscription->courseid == 0) {
                                        $where = "eventname = :eventname AND timecreated >  :starttime";
                    $params = array('eventname' => $eventobj->eventname, 'starttime' => $starttime);
                } else {
                                        if ($subscription->cmid == 0) {
                                                $where = "eventname = :eventname AND courseid = :courseid AND timecreated > :starttime";
                        $params = array('eventname' => $eventobj->eventname, 'courseid' => $eventobj->courseid,
                                'starttime' => $starttime);
                    } else {
                                                $where = "eventname = :eventname AND courseid = :courseid AND contextinstanceid = :cmid
                                AND timecreated > :starttime";
                        $params = array('eventname' => $eventobj->eventname, 'courseid' => $eventobj->courseid,
                                'cmid' => $eventobj->contextinstanceid, 'starttime' => $starttime);

                    }
                }
                $sql = $select . "WHERE " . $where;
                $count = $DB->count_records_sql($sql, $params);
                if (!empty($count) && $count >= $subscription->frequency) {
                    $idstosend[] = $subscription->id;

                                                                                $context = \context_system::instance();
                                                            $courseid = $subscription->courseid;
                    if (!empty($courseid)) {
                        if ($coursecontext = \context_course::instance($courseid, IGNORE_MISSING)) {
                            $context = $coursecontext;
                        }
                    }

                    $params = array(
                        'userid' => $subscription->userid,
                        'courseid' => $subscription->courseid,
                        'context' => $context,
                        'other' => array(
                            'subscriptionid' => $subscription->id
                        )
                    );
                    $event = \tool_monitor\event\subscription_criteria_met::create($params);
                    $event->trigger();
                }
            }
            if (!empty($idstosend)) {
                $messagestosend[] = array('subscriptionids' => $idstosend, 'event' => $eventobj);
                $allsubids = array_merge($allsubids, $idstosend);
            }
        }

        if (!empty($allsubids)) {
                        list($sql, $params) = $DB->get_in_or_equal($allsubids, SQL_PARAMS_NAMED);
            $params['now'] = $now;
            $sql = "UPDATE {tool_monitor_subscriptions} SET lastnotificationsent = :now WHERE id $sql";
            $DB->execute($sql, $params);
        }

                if (!empty($messagestosend)) {
            $adhocktask = new notification_task();
            $adhocktask->set_custom_data($messagestosend);
            $adhocktask->set_component('tool_monitor');
            \core\task\manager::queue_adhoc_task($adhocktask);
        }
    }

    
    protected function flush() {
        global $DB;

                $events = $this->buffer;
        $DB->insert_records('tool_monitor_events', $events);         $this->buffer = array();
        $this->count = 0;
        return $events;
    }

    
    public static function user_deleted(\core\event\user_deleted $event) {
        $userid = $event->objectid;
        subscription_manager::delete_user_subscriptions($userid);
    }

    
    public static function course_module_deleted(\core\event\course_module_deleted $event) {
        $cmid = $event->contextinstanceid;
        subscription_manager::delete_cm_subscriptions($cmid);
    }
}
