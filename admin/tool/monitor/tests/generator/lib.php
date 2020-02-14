<?php



defined('MOODLE_INTERNAL') || die();


class tool_monitor_generator extends testing_module_generator {

    
    protected $rulecount;

    
    public function create_rule($record = null) {
        global $USER;

        $this->rulecount++;
        $i = $this->rulecount;
        $now = time();
        $record = (object)(array)$record;

        if (!isset($record->userid)) {
            $record->userid = $USER->id;
        }
        if (!isset($record->courseid)) {
            $record->courseid = 0;
        }
        if (!isset($record->name)) {
            $record->name = 'Test rule ' . $i;
        }
        if (!isset($record->description)) {
            $record->description = 'Rule description ' . $i;
        }
        if (!isset($record->descriptionformat)) {
            $record->descriptionformat = FORMAT_HTML;
        }
        if (!isset($record->frequency)) {
            $record->frequency = 5;
        }
        if (!isset($record->minutes)) {
            $record->minutes = 5;
        }
        if (!isset($record->template)) {
            $record->template = 'Rule message template ' . $i;
        }
        if (!isset($record->templateformat)) {
            $record->templateformat = FORMAT_HTML;
        }
        if (!isset($record->timewindow)) {
            $record->timewindow = $record->minutes * 60;
        }
        if (!isset($record->timecreated)) {
            $record->timecreated = $now;
        }
        if (!isset($record->timemodified)) {
            $record->timemodified = $now;
        }
        if (!isset($record->plugin)) {
            $record->plugin = 'core';
        }
        if (!isset($record->eventname)) {
            $record->eventname = '\core\event\blog_entry_created';
        }

        unset($record->minutes);         return \tool_monitor\rule_manager::add_rule($record);
    }

    
    public function create_subscription($record = null) {

        if (!isset($record->timecreated)) {
            $record->timecreated = time();
        }
        if (!isset($record->courseid)) {
            $record->courseid = 0;
        }
        if (!isset($record->ruleid)) {
            throw new coding_exception('$record->ruleid must be present in tool_monitor_generator::create_subscription()');
        }
        if (!isset($record->cmid)) {
            $record->cmid = 0;
        }
        if (!isset($record->userid)) {
            throw new coding_exception('$record->userid must be present in tool_monitor_generator::create_subscription()');
        }

        $sid = \tool_monitor\subscription_manager::create_subscription($record->ruleid, $record->courseid,
                $record->cmid, $record->userid);
        return \tool_monitor\subscription_manager::get_subscription($sid);
    }

    
    public function create_event_entries($record = null) {
        global $DB, $CFG;

        $record = (object)(array)$record;
        $context = \context_system::instance();

        if (!isset($record->eventname)) {
            $record->eventname = '\core\event\user_loggedin';
        }
        if (!isset($record->contextid)) {
            $record->contextid = $context->id;
        }
        if (!isset($record->contextlevel)) {
            $record->contextlevel = $context->contextlevel;
        }
        if (!isset($record->contextinstanceid)) {
            $record->contextinstanceid = $context->instanceid;
        }
        if (!isset($record->link)) {
            $record->link = $CFG->wwwroot . '/user/profile.php';
        }
        if (!isset($record->courseid)) {
            $record->courseid = 0;
        }
        if (!isset($record->timecreated)) {
            $record->timecreated = time();
        }
        $record->id = $DB->insert_record('tool_monitor_events', $record, true);

        return $record;
    }

    
    public function create_history($record = null) {
        global $DB;
        $record = (object)(array)$record;
        if (!isset($record->sid)) {
            throw new coding_exception('subscription ID must be present in tool_monitor_generator::create_history() $record');
        }
        if (!isset($record->userid)) {
            throw new coding_exception('user ID must be present in tool_monitor_generator::create_history() $record');
        }
        if (!isset($record->timesent)) {
            $record->timesent = time();
        }
        $record->id = $DB->insert_record('tool_monitor_history', $record, true);

        return $record;
    }
}
