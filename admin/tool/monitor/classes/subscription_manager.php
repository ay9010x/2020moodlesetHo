<?php



namespace tool_monitor;

defined('MOODLE_INTERNAL') || die();


class subscription_manager {

    
    const INACTIVE_SUBSCRIPTION_LIFESPAN_IN_DAYS = 30;

    
    public static function create_subscription($ruleid, $courseid, $cmid, $userid = 0) {
        global $DB, $USER;

        $subscription = new \stdClass();
        $subscription->ruleid = $ruleid;
        $subscription->courseid = $courseid;
        $subscription->cmid = $cmid;
        $subscription->userid = empty($userid) ? $USER->id : $userid;
        if ($DB->record_exists('tool_monitor_subscriptions', (array)$subscription)) {
                        return false;
        }

        $subscription->timecreated = time();
        $subscription->id = $DB->insert_record('tool_monitor_subscriptions', $subscription);

                if ($subscription->id) {
            if (!empty($subscription->courseid)) {
                $courseid = $subscription->courseid;
                $context = \context_course::instance($subscription->courseid);
            } else {
                $courseid = 0;
                $context = \context_system::instance();
            }

            $params = array(
                'objectid' => $subscription->id,
                'courseid' => $courseid,
                'context' => $context
            );
            $event = \tool_monitor\event\subscription_created::create($params);
            $event->trigger();

                        $cache = \cache::make('tool_monitor', 'eventsubscriptions');
            $cache->delete($courseid);
        }

        return $subscription->id;
    }

    
    public static function delete_subscription($subscriptionorid, $checkuser = true) {
        global $DB, $USER;
        if (is_object($subscriptionorid)) {
            $subscription = $subscriptionorid;
        } else {
            $subscription = self::get_subscription($subscriptionorid);
        }
        if ($checkuser && $subscription->userid != $USER->id) {
            throw new \coding_exception('Invalid subscription supplied');
        }

                $subscription = $DB->get_record('tool_monitor_subscriptions', array('id' => $subscription->id));

        $success = $DB->delete_records('tool_monitor_subscriptions', array('id' => $subscription->id));

                if ($success) {
            if (!empty($subscription->courseid)) {
                $courseid = $subscription->courseid;
                $context = \context_course::instance($subscription->courseid);
            } else {
                $courseid = 0;
                $context = \context_system::instance();
            }

            $params = array(
                'objectid' => $subscription->id,
                'courseid' => $courseid,
                'context' => $context
            );
            $event = \tool_monitor\event\subscription_deleted::create($params);
            $event->add_record_snapshot('tool_monitor_subscriptions', $subscription);
            $event->trigger();

                        $cache = \cache::make('tool_monitor', 'eventsubscriptions');
            $cache->delete($courseid);
        }

        return $success;
    }

    
    public static function delete_user_subscriptions($userid) {
        global $DB;
        return $DB->delete_records('tool_monitor_subscriptions', array('userid' => $userid));
    }

    
    public static function delete_cm_subscriptions($cmid) {
        global $DB;
        return $DB->delete_records('tool_monitor_subscriptions', array('cmid' => $cmid));
    }

    
    public static function remove_all_subscriptions_for_rule($ruleid, $coursecontext = null) {
        global $DB;

                $subscriptions = $DB->get_recordset('tool_monitor_subscriptions', array('ruleid' => $ruleid));

                $success = $DB->delete_records('tool_monitor_subscriptions', array('ruleid' => $ruleid));

                if ($success && $subscriptions) {
            foreach ($subscriptions as $subscription) {
                                                if (!is_null($coursecontext)) {
                    $context = $coursecontext;
                    $courseid = $subscription->courseid;
                } else if (!empty($subscription->courseid) && ($coursecontext =
                        \context_course::instance($subscription->courseid, IGNORE_MISSING))) {
                    $courseid = $subscription->courseid;
                    $context = $coursecontext;
                } else {
                    $courseid = 0;
                    $context = \context_system::instance();
                }

                $params = array(
                    'objectid' => $subscription->id,
                    'courseid' => $courseid,
                    'context' => $context
                );
                $event = \tool_monitor\event\subscription_deleted::create($params);
                $event->add_record_snapshot('tool_monitor_subscriptions', $subscription);
                $event->trigger();

                                $cache = \cache::make('tool_monitor', 'eventsubscriptions');
                $cache->delete($courseid);
            }
        }

        $subscriptions->close();

        return $success;
    }

    
    public static function get_subscription($subscriptionorid) {
        global $DB;

        if (is_object($subscriptionorid)) {
            return new subscription($subscriptionorid);
        }

        $sql = self::get_subscription_join_rule_sql();
        $sql .= "WHERE s.id = :id";
        $sub = $DB->get_record_sql($sql, array('id' => $subscriptionorid), MUST_EXIST);
        return new subscription($sub);
    }

    
    public static function get_user_subscriptions_for_course($courseid, $limitfrom = 0, $limitto = 0, $userid = 0,
            $order = 's.timecreated DESC' ) {
        global $DB, $USER;
        if ($userid == 0) {
            $userid = $USER->id;
        }
        $sql = self::get_subscription_join_rule_sql();
        $sql .= "WHERE s.courseid = :courseid AND s.userid = :userid ORDER BY $order";

        return self::get_instances($DB->get_records_sql($sql, array('courseid' => $courseid, 'userid' => $userid), $limitfrom,
                $limitto));
    }

    
    public static function count_user_subscriptions_for_course($courseid, $userid = 0) {
        global $DB, $USER;
        if ($userid == 0) {
            $userid = $USER->id;
        }
        $sql = self::get_subscription_join_rule_sql(true);
        $sql .= "WHERE s.courseid = :courseid AND s.userid = :userid";

        return $DB->count_records_sql($sql, array('courseid' => $courseid, 'userid' => $userid));
    }

    
    public static function get_user_subscriptions($limitfrom = 0, $limitto = 0, $userid = 0,
                                                             $order = 's.courseid ASC, r.name' ) {
        global $DB, $USER;
        if ($userid == 0) {
            $userid = $USER->id;
        }
        $sql = self::get_subscription_join_rule_sql();
        $sql .= "WHERE s.userid = :userid ORDER BY $order";

        return self::get_instances($DB->get_records_sql($sql, array('userid' => $userid), $limitfrom, $limitto));
    }

    
    public static function count_user_subscriptions($userid = 0) {
        global $DB, $USER;;
        if ($userid == 0) {
            $userid = $USER->id;
        }
        $sql = self::get_subscription_join_rule_sql(true);
        $sql .= "WHERE s.userid = :userid";

        return $DB->count_records_sql($sql, array('userid' => $userid));
    }

    
    public static function get_subscriptions_by_event(\stdClass $event) {
        global $DB;

        $sql = self::get_subscription_join_rule_sql();
        if ($event->contextlevel == CONTEXT_MODULE && $event->contextinstanceid != 0) {
            $sql .= "WHERE r.eventname = :eventname AND s.courseid = :courseid AND (s.cmid = :cmid OR s.cmid = 0)";
            $params = array('eventname' => $event->eventname, 'courseid' => $event->courseid, 'cmid' => $event->contextinstanceid);
        } else {
            $sql .= "WHERE r.eventname = :eventname AND (s.courseid = :courseid OR s.courseid = 0)";
            $params = array('eventname' => $event->eventname, 'courseid' => $event->courseid);
        }
        return self::get_instances($DB->get_records_sql($sql, $params));
    }

    
    protected static function get_subscription_join_rule_sql($count = false) {
        if ($count) {
            $select = "SELECT COUNT(s.id) ";
        } else {
            $select = "SELECT s.*, r.description, r.descriptionformat, r.name, r.userid as ruleuserid, r.courseid as rulecourseid,
            r.plugin, r.eventname, r.template, r.templateformat, r.frequency, r.timewindow";
        }
        $sql = $select . "
                  FROM {tool_monitor_rules} r
                  JOIN {tool_monitor_subscriptions} s
                        ON r.id = s.ruleid ";
        return $sql;
    }

    
    protected static function get_instances($arr) {
        $result = array();
        foreach ($arr as $key => $sub) {
            $result[$key] = new subscription($sub);
        }
        return $result;
    }

    
    public static function count_rule_subscriptions($ruleid) {
        global $DB;
        $sql = self::get_subscription_join_rule_sql(true);
        $sql .= "WHERE s.ruleid = :ruleid";

        return $DB->count_records_sql($sql, array('ruleid' => $ruleid));
    }

    
    public static function event_has_subscriptions($eventname, $courseid) {
        global $DB;

                $cache = \cache::make('tool_monitor', 'eventsubscriptions');

                $sql = "SELECT DISTINCT(r.eventname)
                  FROM {tool_monitor_subscriptions} s
            INNER JOIN {tool_monitor_rules} r
                    ON s.ruleid = r.id
                 WHERE s.courseid = :courseid";

        $sitesubscriptions = $cache->get(0);
                if ($sitesubscriptions === false) {
                        $sitesubscriptions = array();
            if ($subscriptions = $DB->get_records_sql($sql, array('courseid' => 0))) {
                foreach ($subscriptions as $subscription) {
                    $sitesubscriptions[$subscription->eventname] = true;
                }
            }
            $cache->set(0, $sitesubscriptions);
        }

                if (isset($sitesubscriptions[$eventname])) {
            return true;
        }

                if (empty($courseid)) {
            return false;
        }

        $coursesubscriptions = $cache->get($courseid);
                if ($coursesubscriptions === false) {
                        $coursesubscriptions = array();
            if ($subscriptions = $DB->get_records_sql($sql, array('courseid' => $courseid))) {
                foreach ($subscriptions as $subscription) {
                    $coursesubscriptions[$subscription->eventname] = true;
                }
            }
            $cache->set($courseid, $coursesubscriptions);
        }

                if (isset($coursesubscriptions[$eventname])) {
            return true;
        }

        return false;
    }

    
    public static function activate_subscriptions(array $ids) {
        global $DB;
        if (!empty($ids)) {
            list($sql, $params) = $DB->get_in_or_equal($ids);
            $success = $DB->set_field_select('tool_monitor_subscriptions', 'inactivedate', '0', 'id ' . $sql, $params);
            return $success;
        }
        return false;
    }

    
    public static function deactivate_subscriptions(array $ids) {
        global $DB;
        if (!empty($ids)) {
            $inactivedate = time();
            list($sql, $params) = $DB->get_in_or_equal($ids);
            $success = $DB->set_field_select('tool_monitor_subscriptions', 'inactivedate', $inactivedate, 'id ' . $sql,
                                             $params);
            return $success;
        }
        return false;
    }

    
    public static function delete_stale_subscriptions($userid = 0) {
        global $DB;
                $cutofftime = strtotime("-" . self::INACTIVE_SUBSCRIPTION_LIFESPAN_IN_DAYS . " days", time());

        if (!empty($userid)) {
                        $success = $DB->delete_records_select('tool_monitor_subscriptions',
                                                  'userid = ? AND inactivedate < ? AND inactivedate <> 0',
                                                  array($userid, $cutofftime));

        } else {
                        $success = $DB->delete_records_select('tool_monitor_subscriptions',
                                                  'inactivedate < ? AND inactivedate <> 0',
                                                  array($cutofftime));
        }
        return $success;
    }

    
    public static function subscription_is_active(subscription $subscription) {
        return empty($subscription->inactivedate);
    }
}
