<?php



defined('MOODLE_INTERNAL') || die();


class block_recent_activity_observer {

    
    const CM_CREATED = 0;
    
    const CM_UPDATED = 1;
    
    const CM_DELETED = 2;

    
    public static function store(\core\event\base $event) {
        global $DB;
        $eventdata = new \stdClass();
        switch ($event->eventname) {
            case '\core\event\course_module_created':
                $eventdata->action = self::CM_CREATED;
                break;
            case '\core\event\course_module_updated':
                $eventdata->action = self::CM_UPDATED;
                break;
            case '\core\event\course_module_deleted':
                $eventdata->action = self::CM_DELETED;
                $eventdata->modname = $event->other['modulename'];
                break;
            default:
                return;
        }
        $eventdata->timecreated = $event->timecreated;
        $eventdata->courseid = $event->courseid;
        $eventdata->cmid = $event->objectid;
        $eventdata->userid = $event->userid;
        $DB->insert_record('block_recent_activity', $eventdata);
    }
}
