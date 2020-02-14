<?php


namespace tool_monitor;

defined('MOODLE_INTERNAL') || die();


class rule_manager {

    
    public static function add_rule($ruledata) {
        global $DB;

        $now = time();
        $ruledata->timecreated = $now;
        $ruledata->timemodified = $now;

        $ruledata->id = $DB->insert_record('tool_monitor_rules', $ruledata);

                if ($ruledata->id) {
            if (!empty($ruledata->courseid)) {
                $courseid = $ruledata->courseid;
                $context = \context_course::instance($ruledata->courseid);
            } else {
                $courseid = 0;
                $context = \context_system::instance();
            }

            $params = array(
                'objectid' => $ruledata->id,
                'courseid' => $courseid,
                'context' => $context
            );
            $event = \tool_monitor\event\rule_created::create($params);
            $event->trigger();
        }

        return new rule($ruledata);
    }

    
    public static function clean_ruledata_form($mformdata) {
        global $USER;

        $rule = new \stdClass();
        if (!empty($mformdata->ruleid)) {
            $rule->id = $mformdata->ruleid;
        }
        $rule->userid = empty($mformdata->userid) ? $USER->id : $mformdata->userid;
        $rule->courseid = $mformdata->courseid;
        $rule->name = $mformdata->name;
        $rule->plugin = $mformdata->plugin;
        $rule->eventname = $mformdata->eventname;
        $rule->description = $mformdata->description['text'];
        $rule->descriptionformat = $mformdata->description['format'];
        $rule->frequency = $mformdata->frequency;
        $rule->timewindow = $mformdata->minutes * MINSECS;
        $rule->template = $mformdata->template['text'];
        $rule->templateformat = $mformdata->template['format'];

        return $rule;
    }

    
    public static function delete_rule($ruleid, $coursecontext = null) {
        global $DB;

        subscription_manager::remove_all_subscriptions_for_rule($ruleid, $coursecontext);

                $rule = $DB->get_record('tool_monitor_rules', array('id' => $ruleid));

        $success = $DB->delete_records('tool_monitor_rules', array('id' => $ruleid));

                if ($success) {
                                    if (!is_null($coursecontext)) {
                $context = $coursecontext;
                $courseid = $rule->courseid;
            } else if (!empty($rule->courseid) && ($context = \context_course::instance($rule->courseid,
                    IGNORE_MISSING))) {
                $courseid = $rule->courseid;
            } else {
                $courseid = 0;
                $context = \context_system::instance();
            }

            $params = array(
                'objectid' => $rule->id,
                'courseid' => $courseid,
                'context' => $context
            );
            $event = \tool_monitor\event\rule_deleted::create($params);
            $event->add_record_snapshot('tool_monitor_rules', $rule);
            $event->trigger();
        }

        return $success;
    }

    
    public static function get_rule($ruleorid) {
        global $DB;
        if (!is_object($ruleorid)) {
            $rule = $DB->get_record('tool_monitor_rules', array('id' => $ruleorid), '*', MUST_EXIST);
        } else {
            $rule = $ruleorid;
        }

        return new rule($rule);
    }

    
    public static function update_rule($ruledata) {
        global $DB;
        if (!self::get_rule($ruledata->id)) {
            throw new \coding_exception('Invalid rule ID.');
        }
        $ruledata->timemodified = time();

        $success = $DB->update_record('tool_monitor_rules', $ruledata);

                if ($success) {
                        if (!isset($ruledata->courseid)) {
                $courseid = $DB->get_field('tool_monitor_rules', 'courseid', array('id' => $ruledata->id), MUST_EXIST);
            } else {
                $courseid = $ruledata->courseid;
            }

            if (!empty($courseid)) {
                $context = \context_course::instance($courseid);
            } else {
                $context = \context_system::instance();
            }

            $params = array(
                'objectid' => $ruledata->id,
                'courseid' => $courseid,
                'context' => $context
            );
            $event = \tool_monitor\event\rule_updated::create($params);
            $event->trigger();
        }

        return $success;
    }

    
    public static function get_rules_by_courseid($courseid, $limitfrom = 0, $limitto = 0, $includesite = true) {
        global $DB;

        $select = 'courseid = ?';
        $params = array();
        $params[] = $courseid;
        if ($includesite) {
            $select .= ' OR courseid = ?';
            $params[] = 0;
        }
        $orderby = 'courseid DESC, name ASC';

        return self::get_instances($DB->get_records_select('tool_monitor_rules', $select, $params, $orderby,
                '*', $limitfrom, $limitto));
    }

    
    public static function count_rules_by_courseid($courseid) {
        global $DB;
        $select = "courseid = ? OR courseid = ?";
        return $DB->count_records_select('tool_monitor_rules', $select, array(0, $courseid));
    }

    
    public static function get_rules_by_plugin($plugin) {
        global $DB;
        return self::get_instances($DB->get_records('tool_monitor_rules', array('plugin' => $plugin)));
    }

    
    public static function get_rules_by_event($eventname) {
        global $DB;
        return self::get_instances($DB->get_records('tool_monitor_rules', array('eventname' => $eventname)));
    }

    
    protected static function get_instances($arr) {
        $result = array();
        foreach ($arr as $key => $sub) {
            $result[$key] = new rule($sub);
        }
        return $result;
    }
}
