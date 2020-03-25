<?php



defined('MOODLE_INTERNAL') || die();


function enrol_category_sync_course($course) {
    global $DB;

    if (!enrol_is_enabled('category')) {
        return;
    }

    $plugin = enrol_get_plugin('category');

    $syscontext = context_system::instance();
    $roles = get_roles_with_capability('enrol/category:synchronised', CAP_ALLOW, $syscontext);

    if (!$roles) {
                if ($instances = $DB->get_records('enrol', array('courseid'=>$course->id, 'enrol'=>'category'))) {
            foreach ($instances as $instance) {
                $plugin->delete_instance($instance);
            }
        }
        return;
    }

        $coursecontext = context_course::instance($course->id);
    $contextids = $coursecontext->get_parent_context_ids();
    array_pop($contextids); 
    list($roleids, $params) = $DB->get_in_or_equal(array_keys($roles), SQL_PARAMS_NAMED, 'r');
    list($contextids, $contextparams) = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED, 'c');
    $params = array_merge($params, $contextparams);
    $params['courseid'] = $course->id;

    $sql = "SELECT 'x'
              FROM {role_assignments}
             WHERE roleid $roleids AND contextid $contextids";
    if (!$DB->record_exists_sql($sql, $params)) {
        if ($instances = $DB->get_records('enrol', array('courseid'=>$course->id, 'enrol'=>'category'))) {
                        foreach ($instances as $instance) {
                $plugin->delete_instance($instance);
            }
        }
        return;
    }

        $delinstances = array();
    if ($instances = $DB->get_records('enrol', array('courseid'=>$course->id, 'enrol'=>'category'))) {
        $instance = array_shift($instances);
        $delinstances = $instances;
    } else {
        $i = $plugin->add_instance($course);
        $instance = $DB->get_record('enrol', array('id'=>$i));
    }

        $sql = "SELECT ra.userid, ra.estart
              FROM (SELECT xra.userid, MIN(xra.timemodified) AS estart
                      FROM {role_assignments} xra
                      JOIN {user} xu ON (xu.id = xra.userid AND xu.deleted = 0)
                     WHERE xra.roleid $roleids AND xra.contextid $contextids
                  GROUP BY xra.userid
                   ) ra
         LEFT JOIN {user_enrolments} ue ON (ue.enrolid = :instanceid AND ue.userid = ra.userid)
             WHERE ue.id IS NULL";
    $params['instanceid'] = $instance->id;
    $rs = $DB->get_recordset_sql($sql, $params);
    foreach ($rs as $ra) {
        $plugin->enrol_user($instance, $ra->userid, null, $ra->estart);
    }
    $rs->close();

        $sql = "SELECT DISTINCT ue.userid
              FROM {user_enrolments} ue
         LEFT JOIN {role_assignments} ra ON (ra.roleid $roleids AND ra.contextid $contextids AND ra.userid = ue.userid)
             WHERE ue.enrolid = :instanceid AND ra.id IS NULL";
    $rs = $DB->get_recordset_sql($sql, $params);
    foreach ($rs as $ra) {
        $plugin->unenrol_user($instance, $ra->userid);
    }
    $rs->close();

    if ($delinstances) {
                foreach ($delinstances as $delinstance) {
            $plugin->delete_instance($delinstance);
        }
    }
}


function enrol_category_sync_full(progress_trace $trace) {
    global $DB;


    if (!enrol_is_enabled('category')) {
        $trace->finished();
        return 2;
    }

        core_php_time_limit::raise();

    $plugin = enrol_get_plugin('category');

    $syscontext = context_system::instance();

        if (!$roles = get_roles_with_capability('enrol/category:synchronised', CAP_ALLOW, $syscontext)) {
                $trace->output("No roles with 'enrol/category:synchronised' capability found.");
        if ($instances = $DB->get_records('enrol', array('enrol'=>'category'))) {
            $trace->output("Deleting all category enrol instances...");
            foreach ($instances as $instance) {
                $trace->output("deleting category enrol instance from course {$instance->courseid}", 1);
                $plugin->delete_instance($instance);
            }
            $trace->output("...all instances deleted.");
        }
        $trace->finished();
        return 0;
    }
    $rolenames = role_fix_names($roles, null, ROLENAME_SHORT, true);
    $trace->output('Synchronising category enrolments for roles: '.implode(', ', $rolenames).'...');

    list($roleids, $params) = $DB->get_in_or_equal(array_keys($roles), SQL_PARAMS_NAMED, 'r');
    $params['courselevel'] = CONTEXT_COURSE;
    $params['catlevel'] = CONTEXT_COURSECAT;

        $parentcat = $DB->sql_concat("cat.path", "'/%'");
    $parentcctx = $DB->sql_concat("cctx.path", "'/%'");
                $sql = "SELECT c.*
              FROM {course} c
              JOIN (
                SELECT DISTINCT c.id
                  FROM {course} c
                  JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :courselevel)
                  JOIN (SELECT DISTINCT cctx.path
                          FROM {course_categories} cc
                          JOIN {context} cctx ON (cctx.instanceid = cc.id AND cctx.contextlevel = :catlevel)
                          JOIN {role_assignments} ra ON (ra.contextid = cctx.id AND ra.roleid $roleids)
                       ) cat ON (ctx.path LIKE $parentcat)
             LEFT JOIN {enrol} e ON (e.courseid = c.id AND e.enrol = 'category')
                 WHERE e.id IS NULL) ci ON (c.id = ci.id)";

    $rs = $DB->get_recordset_sql($sql, $params);
    foreach($rs as $course) {
        $plugin->add_instance($course);
    }
    $rs->close();

            $sql = "SELECT e.*
              FROM {enrol} e
              JOIN {context} ctx ON (ctx.instanceid = e.courseid AND ctx.contextlevel = :courselevel)
         LEFT JOIN ({course_categories} cc
                      JOIN {context} cctx ON (cctx.instanceid = cc.id AND cctx.contextlevel = :catlevel)
                      JOIN {role_assignments} ra ON (ra.contextid = cctx.id AND ra.roleid $roleids)
                   ) ON (ctx.path LIKE $parentcctx)
             WHERE e.enrol = 'category' AND cc.id IS NULL";

    $rs = $DB->get_recordset_sql($sql, $params);
    foreach($rs as $instance) {
        $plugin->delete_instance($instance);
    }
    $rs->close();

        $sql = "SELECT e.*, cat.userid, cat.estart
              FROM {enrol} e
              JOIN {context} ctx ON (ctx.instanceid = e.courseid AND ctx.contextlevel = :courselevel)
              JOIN (SELECT cctx.path, ra.userid, MIN(ra.timemodified) AS estart
                      FROM {course_categories} cc
                      JOIN {context} cctx ON (cctx.instanceid = cc.id AND cctx.contextlevel = :catlevel)
                      JOIN {role_assignments} ra ON (ra.contextid = cctx.id AND ra.roleid $roleids)
                  GROUP BY cctx.path, ra.userid
                   ) cat ON (ctx.path LIKE $parentcat)
         LEFT JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = cat.userid)
             WHERE e.enrol = 'category' AND ue.id IS NULL";
    $rs = $DB->get_recordset_sql($sql, $params);
    foreach($rs as $instance) {
        $userid = $instance->userid;
        $estart = $instance->estart;
        unset($instance->userid);
        unset($instance->estart);
        $plugin->enrol_user($instance, $userid, null, $estart);
        $trace->output("enrolling: user $userid ==> course $instance->courseid", 1);
    }
    $rs->close();

        $sql = "SELECT e.*, ue.userid
              FROM {enrol} e
              JOIN {context} ctx ON (ctx.instanceid = e.courseid AND ctx.contextlevel = :courselevel)
              JOIN {user_enrolments} ue ON (ue.enrolid = e.id)
         LEFT JOIN ({course_categories} cc
                      JOIN {context} cctx ON (cctx.instanceid = cc.id AND cctx.contextlevel = :catlevel)
                      JOIN {role_assignments} ra ON (ra.contextid = cctx.id AND ra.roleid $roleids)
                   ) ON (ctx.path LIKE $parentcctx AND ra.userid = ue.userid)
             WHERE e.enrol = 'category' AND cc.id IS NULL";
    $rs = $DB->get_recordset_sql($sql, $params);
    foreach($rs as $instance) {
        $userid = $instance->userid;
        unset($instance->userid);
        $plugin->unenrol_user($instance, $userid);
        $trace->output("unenrolling: user $userid ==> course $instance->courseid", 1);
    }
    $rs->close();

    $trace->output('...user enrolment synchronisation finished.');
    $trace->finished();

    return 0;
}
