<?php



defined('MOODLE_INTERNAL') || die();



class enrol_category_observer {
    
    public static function role_assigned(\core\event\role_assigned $event) {
        global $DB;

        if (!enrol_is_enabled('category')) {
            return;
        }

        $ra = new stdClass();
        $ra->roleid = $event->objectid;
        $ra->userid = $event->relateduserid;
        $ra->contextid = $event->contextid;

                $parentcontext = context::instance_by_id($ra->contextid);
        if ($parentcontext->contextlevel != CONTEXT_COURSECAT) {
            return;
        }

                        $syscontext = context_system::instance();
        if (!$DB->record_exists('role_capabilities', array('contextid'=>$syscontext->id, 'roleid'=>$ra->roleid, 'capability'=>'enrol/category:synchronised', 'permission'=>CAP_ALLOW))) {
            return;
        }

                $plugin = enrol_get_plugin('category');
        $sql = "SELECT c.*
                  FROM {course} c
                  JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :courselevel AND ctx.path LIKE :match)
             LEFT JOIN {enrol} e ON (e.courseid = c.id AND e.enrol = 'category')
                 WHERE e.id IS NULL";
        $params = array('courselevel'=>CONTEXT_COURSE, 'match'=>$parentcontext->path.'/%');
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $course) {
            $plugin->add_instance($course);
        }
        $rs->close();

                $sql = "SELECT e.*
                  FROM {course} c
                  JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :courselevel AND ctx.path LIKE :match)
                  JOIN {enrol} e ON (e.courseid = c.id AND e.enrol = 'category')
             LEFT JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)
                 WHERE ue.id IS NULL";
        $params = array('courselevel'=>CONTEXT_COURSE, 'match'=>$parentcontext->path.'/%', 'userid'=>$ra->userid);
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $instance) {
            $plugin->enrol_user($instance, $ra->userid, null, time());
        }
        $rs->close();
    }

    
    public static function role_unassigned(\core\event\role_unassigned $event) {
        global $DB;

        if (!enrol_is_enabled('category')) {
            return;
        }

        $ra = new stdClass();
        $ra->userid = $event->relateduserid;
        $ra->contextid = $event->contextid;

                $parentcontext = context::instance_by_id($ra->contextid);
        if ($parentcontext->contextlevel != CONTEXT_COURSECAT) {
            return;
        }

                $syscontext = context_system::instance();
        if (!$roles = get_roles_with_capability('enrol/category:synchronised', CAP_ALLOW, $syscontext)) {
            return;
        }

        $plugin = enrol_get_plugin('category');

        $sql = "SELECT e.*
                  FROM {course} c
                  JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :courselevel AND ctx.path LIKE :match)
                  JOIN {enrol} e ON (e.courseid = c.id AND e.enrol = 'category')
                  JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)";
        $params = array('courselevel'=>CONTEXT_COURSE, 'match'=>$parentcontext->path.'/%', 'userid'=>$ra->userid);
        $rs = $DB->get_recordset_sql($sql, $params);

        list($roleids, $params) = $DB->get_in_or_equal(array_keys($roles), SQL_PARAMS_NAMED, 'r');
        $params['userid'] = $ra->userid;

        foreach ($rs as $instance) {
            $coursecontext = context_course::instance($instance->courseid);
            $contextids = $coursecontext->get_parent_context_ids();
            array_pop($contextids); 
            list($contextids, $contextparams) = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED, 'c');
            $params = array_merge($params, $contextparams);

            $sql = "SELECT ra.id
                      FROM {role_assignments} ra
                     WHERE ra.userid = :userid AND ra.contextid $contextids AND ra.roleid $roleids";
            if (!$DB->record_exists_sql($sql, $params)) {
                                $plugin->unenrol_user($instance, $ra->userid);
            }
        }
        $rs->close();
    }
}
