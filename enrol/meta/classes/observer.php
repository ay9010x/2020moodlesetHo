<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/enrol/meta/locallib.php');


class enrol_meta_observer extends enrol_meta_handler {

    
    public static function user_enrolment_created(\core\event\user_enrolment_created $event) {
        if (!enrol_is_enabled('meta')) {
                        return true;
        }

        if ($event->other['enrol'] === 'meta') {
                        return true;
        }

        self::sync_course_instances($event->courseid, $event->relateduserid);
        return true;
    }

    
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event) {
        if (!enrol_is_enabled('meta')) {
                        return true;
        }

        if ($event->other['enrol'] === 'meta') {
                        return true;
        }

        self::sync_course_instances($event->courseid, $event->relateduserid);

        return true;
    }

    
    public static function user_enrolment_updated(\core\event\user_enrolment_updated $event) {
        if (!enrol_is_enabled('meta')) {
                        return true;
        }

        if ($event->other['enrol'] === 'meta') {
                        return true;
        }

        self::sync_course_instances($event->courseid, $event->relateduserid);

        return true;
    }

    
    public static function role_assigned(\core\event\role_assigned $event) {
        if (!enrol_is_enabled('meta')) {
            return true;
        }

                if ($event->other['component'] === 'enrol_meta') {
            return true;
        }

                if (!$parentcontext = context::instance_by_id($event->contextid, IGNORE_MISSING)) {
            return true;
        }
        if ($parentcontext->contextlevel != CONTEXT_COURSE) {
            return true;
        }

        self::sync_course_instances($parentcontext->instanceid, $event->relateduserid);

        return true;
    }

    
    public static function role_unassigned(\core\event\role_unassigned $event) {
        if (!enrol_is_enabled('meta')) {
                        return true;
        }

                if ($event->other['component'] === 'enrol_meta') {
            return true;
        }

                if (!$parentcontext = context::instance_by_id($event->contextid, IGNORE_MISSING)) {
            return true;
        }
        if ($parentcontext->contextlevel != CONTEXT_COURSE) {
            return true;
        }

        self::sync_course_instances($parentcontext->instanceid, $event->relateduserid);

        return true;
    }

    
    public static function course_deleted(\core\event\course_deleted $event) {
        global $DB;

        if (!enrol_is_enabled('meta')) {
                        return true;
        }

                if (!$enrols = $DB->get_records('enrol', array('customint1' => $event->objectid, 'enrol' => 'meta'),
                'courseid ASC, id ASC')) {
            return true;
        }

        $plugin = enrol_get_plugin('meta');
        $unenrolaction = $plugin->get_config('unenrolaction', ENROL_EXT_REMOVED_SUSPENDNOROLES);

        if ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
                                    foreach ($enrols as $enrol) {
                $plugin->delete_instance($enrol);
            }
            return true;
        }

        foreach ($enrols as $enrol) {
            if ($unenrolaction == ENROL_EXT_REMOVED_SUSPEND or $unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                                $plugin->update_status($enrol, ENROL_INSTANCE_DISABLED);
            }
            if ($unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                $context = context_course::instance($enrol->courseid);
                role_unassign_all(array('contextid'=>$context->id, 'component'=>'enrol_meta', 'itemid'=>$enrol->id));
            }
        }

        return true;
    }

    
    public static function enrol_instance_updated(\core\event\enrol_instance_updated $event) {
        global $DB;

        if (!enrol_is_enabled('meta')) {
                        return true;
        }

                $affectedcourses = $DB->get_fieldset_sql('SELECT DISTINCT courseid FROM {enrol} '.
                'WHERE customint1 = ? AND enrol = ?',
                array($event->courseid, 'meta'));

        foreach ($affectedcourses as $courseid) {
            enrol_meta_sync($courseid);
        }

        return true;
    }
}
