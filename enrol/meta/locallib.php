<?php



defined('MOODLE_INTERNAL') || die();



class enrol_meta_handler {

    
    protected static function sync_course_instances($courseid, $userid) {
        global $DB;

        static $preventrecursion = false;

                if (!$enrols = $DB->get_records('enrol', array('customint1'=>$courseid, 'enrol'=>'meta'), 'id ASC')) {
            return;
        }

        if ($preventrecursion) {
            return;
        }

        $preventrecursion = true;

        try {
            foreach ($enrols as $enrol) {
                self::sync_with_parent_course($enrol, $userid);
            }
        } catch (Exception $e) {
            $preventrecursion = false;
            throw $e;
        }

        $preventrecursion = false;
    }

    
    protected static function sync_with_parent_course(stdClass $instance, $userid) {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/group/lib.php');

        $plugin = enrol_get_plugin('meta');

        if ($instance->customint1 == $instance->courseid) {
                        return;
        }

        $context = context_course::instance($instance->courseid);

                list($enabled, $params) = $DB->get_in_or_equal(explode(',', $CFG->enrol_plugins_enabled), SQL_PARAMS_NAMED, 'e');
        $params['userid'] = $userid;
        $params['parentcourse'] = $instance->customint1;
        $sql = "SELECT ue.*, e.status AS enrolstatus
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol <> 'meta' AND e.courseid = :parentcourse AND e.enrol $enabled)
                 WHERE ue.userid = :userid";
        $parentues = $DB->get_records_sql($sql, $params);
                $ue = $DB->get_record('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$userid));

                if (empty($parentues)) {
            self::user_not_supposed_to_be_here($instance, $ue, $context, $plugin);
            return;
        }

        if (!$parentcontext = context_course::instance($instance->customint1, IGNORE_MISSING)) {
                        return;
        }

        $skiproles = $plugin->get_config('nosyncroleids', '');
        $skiproles = empty($skiproles) ? array() : explode(',', $skiproles);
        $syncall   = $plugin->get_config('syncall', 1);

                $parentroles = array();
        list($ignoreroles, $params) = $DB->get_in_or_equal($skiproles, SQL_PARAMS_NAMED, 'ri', false, -1);
        $params['contextid'] = $parentcontext->id;
        $params['userid'] = $userid;
        $select = "contextid = :contextid AND userid = :userid AND component <> 'enrol_meta' AND roleid $ignoreroles";
        foreach($DB->get_records_select('role_assignments', $select, $params) as $ra) {
            $parentroles[$ra->roleid] = $ra->roleid;
        }

                $roles = array();
        $ras = $DB->get_records('role_assignments', array('contextid'=>$context->id, 'userid'=>$userid, 'component'=>'enrol_meta', 'itemid'=>$instance->id));
        foreach($ras as $ra) {
            $roles[$ra->roleid] = $ra->roleid;
        }
        unset($ras);

                if (!$syncall and empty($parentroles)) {
            self::user_not_supposed_to_be_here($instance, $ue, $context, $plugin);
            return;
        }

                $parentstatus = ENROL_USER_SUSPENDED;
        $parenttimeend = null;
        $parenttimestart = null;
        foreach ($parentues as $pue) {
            if ($pue->status == ENROL_USER_ACTIVE && $pue->enrolstatus == ENROL_INSTANCE_ENABLED) {
                $parentstatus = ENROL_USER_ACTIVE;
                if ($parenttimeend === null || $pue->timeend == 0 || ($parenttimeend && $parenttimeend < $pue->timeend)) {
                    $parenttimeend = $pue->timeend;
                }
                if ($parenttimestart === null || $parenttimestart > $pue->timestart) {
                    $parenttimestart = $pue->timestart;
                }
            }
        }

                if ($ue) {
            if ($parentstatus != $ue->status ||
                    ($parentstatus == ENROL_USER_ACTIVE && ($parenttimestart != $ue->timestart || $parenttimeend != $ue->timeend))) {
                $plugin->update_user_enrol($instance, $userid, $parentstatus, $parenttimestart, $parenttimeend);
                $ue->status = $parentstatus;
                $ue->timestart = $parenttimestart;
                $ue->timeend = $parenttimeend;
            }
        } else {
            $plugin->enrol_user($instance, $userid, NULL, (int)$parenttimestart, (int)$parenttimeend, $parentstatus);
            $ue = new stdClass();
            $ue->userid = $userid;
            $ue->enrolid = $instance->id;
            $ue->status = $parentstatus;
            if ($instance->customint2) {
                groups_add_member($instance->customint2, $userid, 'enrol_meta', $instance->id);
            }
        }

        $unenrolaction = $plugin->get_config('unenrolaction', ENROL_EXT_REMOVED_SUSPENDNOROLES);

                if ($ue->status != ENROL_USER_ACTIVE or $instance->status != ENROL_INSTANCE_ENABLED or
                ($parenttimeend and $parenttimeend < time()) or ($parenttimestart > time())) {
            if ($unenrolaction == ENROL_EXT_REMOVED_SUSPEND) {
                            } else if ($roles) {
                                role_unassign_all(array('userid'=>$userid, 'contextid'=>$context->id, 'component'=>'enrol_meta', 'itemid'=>$instance->id));
            }
            return;
        }

                foreach ($parentroles as $rid) {
            if (!isset($roles[$rid])) {
                role_assign($rid, $userid, $context->id, 'enrol_meta', $instance->id);
            }
        }

        if ($unenrolaction == ENROL_EXT_REMOVED_SUSPEND) {
                        return;
        }

                foreach ($roles as $rid) {
            if (!isset($parentroles[$rid])) {
                role_unassign($rid, $userid, $context->id, 'enrol_meta', $instance->id);
            }
        }
    }

    
    protected static function user_not_supposed_to_be_here($instance, $ue, context_course $context, $plugin) {
        if (!$ue) {
                        return;
        }

        $userid = $ue->userid;
        $unenrolaction = $plugin->get_config('unenrolaction', ENROL_EXT_REMOVED_SUSPENDNOROLES);

        if ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
                        $plugin->unenrol_user($instance, $userid);

        } else if ($unenrolaction == ENROL_EXT_REMOVED_SUSPEND) {
            if ($ue->status != ENROL_USER_SUSPENDED) {
                $plugin->update_user_enrol($instance, $userid, ENROL_USER_SUSPENDED);
            }

        } else if ($unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
            if ($ue->status != ENROL_USER_SUSPENDED) {
                $plugin->update_user_enrol($instance, $userid, ENROL_USER_SUSPENDED);
            }
            role_unassign_all(array('userid'=>$userid, 'contextid'=>$context->id, 'component'=>'enrol_meta', 'itemid'=>$instance->id));

        } else {
            debugging('Unknown unenrol action '.$unenrolaction);
        }
    }
}


function enrol_meta_sync($courseid = NULL, $verbose = false) {
    global $CFG, $DB;
    require_once("{$CFG->dirroot}/group/lib.php");

        if (!enrol_is_enabled('meta')) {
        if ($verbose) {
            mtrace('Meta sync plugin is disabled, unassigning all plugin roles and stopping.');
        }
        role_unassign_all(array('component'=>'enrol_meta'));
        return 2;
    }

        core_php_time_limit::raise();
    raise_memory_limit(MEMORY_HUGE);

    if ($verbose) {
        mtrace('Starting user enrolment synchronisation...');
    }

    $instances = array(); 
    $meta = enrol_get_plugin('meta');

    $unenrolaction = $meta->get_config('unenrolaction', ENROL_EXT_REMOVED_SUSPENDNOROLES);
    $skiproles     = $meta->get_config('nosyncroleids', '');
    $skiproles     = empty($skiproles) ? array() : explode(',', $skiproles);
    $syncall       = $meta->get_config('syncall', 1);

    $allroles = get_all_roles();


                            $onecourse = $courseid ? "AND e.courseid = :courseid" : "";
    list($enabled, $params) = $DB->get_in_or_equal(explode(',', $CFG->enrol_plugins_enabled), SQL_PARAMS_NAMED, 'e');
    $params['courseid'] = $courseid;
    $sql = "SELECT pue.userid, e.id AS enrolid, MIN(pue.status + pe.status) AS status,
                      MIN(CASE WHEN (pue.status + pe.status = 0) THEN pue.timestart ELSE 9999999999 END) AS timestart,
                      MAX(CASE WHEN (pue.status + pe.status = 0) THEN
                                (CASE WHEN pue.timeend = 0 THEN 9999999999 ELSE pue.timeend END)
                                ELSE 0 END) AS timeend
              FROM {user_enrolments} pue
              JOIN {enrol} pe ON (pe.id = pue.enrolid AND pe.enrol <> 'meta' AND pe.enrol $enabled)
              JOIN {enrol} e ON (e.customint1 = pe.courseid AND e.enrol = 'meta' AND e.status = :enrolstatus $onecourse)
              JOIN {user} u ON (u.id = pue.userid AND u.deleted = 0)
         LEFT JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = pue.userid)
             WHERE ue.id IS NULL
             GROUP BY pue.userid, e.id";
    $params['enrolstatus'] = ENROL_INSTANCE_ENABLED;

    $rs = $DB->get_recordset_sql($sql, $params);
    foreach($rs as $ue) {
        if (!isset($instances[$ue->enrolid])) {
            $instances[$ue->enrolid] = $DB->get_record('enrol', array('id'=>$ue->enrolid));
        }
        $instance = $instances[$ue->enrolid];

        if (!$syncall) {
                        $parentcontext = context_course::instance($instance->customint1);
            list($ignoreroles, $params) = $DB->get_in_or_equal($skiproles, SQL_PARAMS_NAMED, 'ri', false, -1);
            $params['contextid'] = $parentcontext->id;
            $params['userid'] = $ue->userid;
            $select = "contextid = :contextid AND userid = :userid AND component <> 'enrol_meta' AND roleid $ignoreroles";
            if (!$DB->record_exists_select('role_assignments', $select, $params)) {
                                if ($verbose) {
                    mtrace("  skipping enrolling: $ue->userid ==> $instance->courseid (user without role)");
                }
                continue;
            }
        }

                        $ue->status = ($ue->status == ENROL_USER_ACTIVE + ENROL_INSTANCE_ENABLED) ? ENROL_USER_ACTIVE : ENROL_USER_SUSPENDED;
                $ue->timeend = ($ue->timeend == 9999999999) ? 0 : (int)$ue->timeend;
                $ue->timestart = ($ue->timestart == 9999999999) ? 0 : (int)$ue->timestart;

        $meta->enrol_user($instance, $ue->userid, null, $ue->timestart, $ue->timeend, $ue->status);
        if ($instance->customint2) {
            groups_add_member($instance->customint2, $ue->userid, 'enrol_meta', $instance->id);
        }
        if ($verbose) {
            mtrace("  enrolling: $ue->userid ==> $instance->courseid");
        }
    }
    $rs->close();


        $onecourse = $courseid ? "AND e.courseid = :courseid" : "";
    list($enabled, $params) = $DB->get_in_or_equal(explode(',', $CFG->enrol_plugins_enabled), SQL_PARAMS_NAMED, 'e');
    $params['courseid'] = $courseid;
    $sql = "SELECT ue.*
              FROM {user_enrolments} ue
              JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'meta' $onecourse)
         LEFT JOIN ({user_enrolments} xpue
                      JOIN {enrol} xpe ON (xpe.id = xpue.enrolid AND xpe.enrol <> 'meta' AND xpe.enrol $enabled)
                   ) ON (xpe.courseid = e.customint1 AND xpue.userid = ue.userid)
             WHERE xpue.userid IS NULL";
    $rs = $DB->get_recordset_sql($sql, $params);
    foreach($rs as $ue) {
        if (!isset($instances[$ue->enrolid])) {
            $instances[$ue->enrolid] = $DB->get_record('enrol', array('id'=>$ue->enrolid));
        }
        $instance = $instances[$ue->enrolid];

        if ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
            $meta->unenrol_user($instance, $ue->userid);
            if ($verbose) {
                mtrace("  unenrolling: $ue->userid ==> $instance->courseid");
            }

        } else if ($unenrolaction == ENROL_EXT_REMOVED_SUSPEND) {
            if ($ue->status != ENROL_USER_SUSPENDED) {
                $meta->update_user_enrol($instance, $ue->userid, ENROL_USER_SUSPENDED);
                if ($verbose) {
                    mtrace("  suspending: $ue->userid ==> $instance->courseid");
                }
            }

        } else if ($unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
            if ($ue->status != ENROL_USER_SUSPENDED) {
                $meta->update_user_enrol($instance, $ue->userid, ENROL_USER_SUSPENDED);
                $context = context_course::instance($instance->courseid);
                role_unassign_all(array('userid'=>$ue->userid, 'contextid'=>$context->id, 'component'=>'enrol_meta', 'itemid'=>$instance->id));
                if ($verbose) {
                    mtrace("  suspending and removing all roles: $ue->userid ==> $instance->courseid");
                }
            }
        }
    }
    $rs->close();


            $onecourse = $courseid ? "AND e.courseid = :courseid" : "";
    list($enabled, $params) = $DB->get_in_or_equal(explode(',', $CFG->enrol_plugins_enabled), SQL_PARAMS_NAMED, 'e');
    $params['courseid'] = $courseid;
    $sql = "SELECT ue.userid, ue.enrolid, pue.pstatus, pue.ptimestart, pue.ptimeend
              FROM {user_enrolments} ue
              JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'meta' $onecourse)
              JOIN (SELECT xpue.userid, xpe.courseid, MIN(xpue.status + xpe.status) AS pstatus,
                      MIN(CASE WHEN (xpue.status + xpe.status = 0) THEN xpue.timestart ELSE 9999999999 END) AS ptimestart,
                      MAX(CASE WHEN (xpue.status + xpe.status = 0) THEN
                                (CASE WHEN xpue.timeend = 0 THEN 9999999999 ELSE xpue.timeend END)
                                ELSE 0 END) AS ptimeend
                      FROM {user_enrolments} xpue
                      JOIN {enrol} xpe ON (xpe.id = xpue.enrolid AND xpe.enrol <> 'meta' AND xpe.enrol $enabled)
                  GROUP BY xpue.userid, xpe.courseid
                   ) pue ON (pue.courseid = e.customint1 AND pue.userid = ue.userid)
             WHERE (pue.pstatus = 0 AND ue.status > 0) OR (pue.pstatus > 0 and ue.status = 0)
             OR ((CASE WHEN pue.ptimestart = 9999999999 THEN 0 ELSE pue.ptimestart END) <> ue.timestart)
             OR ((CASE WHEN pue.ptimeend = 9999999999 THEN 0 ELSE pue.ptimeend END) <> ue.timeend)";
    $rs = $DB->get_recordset_sql($sql, $params);
    foreach($rs as $ue) {
        if (!isset($instances[$ue->enrolid])) {
            $instances[$ue->enrolid] = $DB->get_record('enrol', array('id'=>$ue->enrolid));
        }
        $instance = $instances[$ue->enrolid];
        $ue->pstatus = ($ue->pstatus == ENROL_USER_ACTIVE + ENROL_INSTANCE_ENABLED) ? ENROL_USER_ACTIVE : ENROL_USER_SUSPENDED;
        $ue->ptimeend = ($ue->ptimeend == 9999999999) ? 0 : (int)$ue->ptimeend;
        $ue->ptimestart = ($ue->ptimestart == 9999999999) ? 0 : (int)$ue->ptimestart;

        if ($ue->pstatus == ENROL_USER_ACTIVE and (!$ue->ptimeend || $ue->ptimeend > time())
                and !$syncall and $unenrolaction != ENROL_EXT_REMOVED_UNENROL) {
                        $parentcontext = context_course::instance($instance->customint1);
            list($ignoreroles, $params) = $DB->get_in_or_equal($skiproles, SQL_PARAMS_NAMED, 'ri', false, -1);
            $params['contextid'] = $parentcontext->id;
            $params['userid'] = $ue->userid;
            $select = "contextid = :contextid AND userid = :userid AND component <> 'enrol_meta' AND roleid $ignoreroles";
            if (!$DB->record_exists_select('role_assignments', $select, $params)) {
                                if ($verbose) {
                    mtrace("  skipping unsuspending: $ue->userid ==> $instance->courseid (user without role)");
                }
                continue;
            }
        }

        $meta->update_user_enrol($instance, $ue->userid, $ue->pstatus, $ue->ptimestart, $ue->ptimeend);
        if ($verbose) {
            if ($ue->pstatus == ENROL_USER_ACTIVE) {
                mtrace("  unsuspending: $ue->userid ==> $instance->courseid");
            } else {
                mtrace("  suspending: $ue->userid ==> $instance->courseid");
            }
        }
    }
    $rs->close();


        $enabled = explode(',', $CFG->enrol_plugins_enabled);
    foreach($enabled as $k=>$v) {
        if ($v === 'meta') {
            continue;         }
        $enabled[$k] = 'enrol_'.$v;
    }
    $enabled[] = ''; 
    $onecourse = $courseid ? "AND e.courseid = :courseid" : "";
    list($enabled, $params) = $DB->get_in_or_equal($enabled, SQL_PARAMS_NAMED, 'e');
    $params['coursecontext'] = CONTEXT_COURSE;
    $params['courseid'] = $courseid;
    $params['activeuser'] = ENROL_USER_ACTIVE;
    $params['enabledinstance'] = ENROL_INSTANCE_ENABLED;
    $sql = "SELECT DISTINCT pra.roleid, pra.userid, c.id AS contextid, e.id AS enrolid, e.courseid
              FROM {role_assignments} pra
              JOIN {user} u ON (u.id = pra.userid AND u.deleted = 0)
              JOIN {context} pc ON (pc.id = pra.contextid AND pc.contextlevel = :coursecontext AND pra.component $enabled)
              JOIN {enrol} e ON (e.customint1 = pc.instanceid AND e.enrol = 'meta' $onecourse AND e.status = :enabledinstance)
              JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = u.id AND ue.status = :activeuser)
              JOIN {context} c ON (c.contextlevel = pc.contextlevel AND c.instanceid = e.courseid)
         LEFT JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.userid = pra.userid AND ra.roleid = pra.roleid AND ra.itemid = e.id AND ra.component = 'enrol_meta')
             WHERE ra.id IS NULL";

    if ($ignored = $meta->get_config('nosyncroleids')) {
        list($notignored, $xparams) = $DB->get_in_or_equal(explode(',', $ignored), SQL_PARAMS_NAMED, 'ig', false);
        $params = array_merge($params, $xparams);
        $sql = "$sql AND pra.roleid $notignored";
    }

    $rs = $DB->get_recordset_sql($sql, $params);
    foreach($rs as $ra) {
        role_assign($ra->roleid, $ra->userid, $ra->contextid, 'enrol_meta', $ra->enrolid);
        if ($verbose) {
            mtrace("  assigning role: $ra->userid ==> $ra->courseid as ".$allroles[$ra->roleid]->shortname);
        }
    }
    $rs->close();


        $onecourse = $courseid ? "AND e.courseid = :courseid" : "";
    $params = array();
    $params['coursecontext'] = CONTEXT_COURSE;
    $params['courseid'] = $courseid;
    $params['activeuser'] = ENROL_USER_ACTIVE;
    $params['enabledinstance'] = ENROL_INSTANCE_ENABLED;
    if ($ignored = $meta->get_config('nosyncroleids')) {
        list($notignored, $xparams) = $DB->get_in_or_equal(explode(',', $ignored), SQL_PARAMS_NAMED, 'ig', false);
        $params = array_merge($params, $xparams);
        $notignored = "AND pra.roleid $notignored";
    } else {
        $notignored = "";
    }

    $sql = "SELECT ra.roleid, ra.userid, ra.contextid, ra.itemid, e.courseid
              FROM {role_assignments} ra
              JOIN {enrol} e ON (e.id = ra.itemid AND ra.component = 'enrol_meta' AND e.enrol = 'meta' $onecourse)
              JOIN {context} pc ON (pc.instanceid = e.customint1 AND pc.contextlevel = :coursecontext)
         LEFT JOIN {role_assignments} pra ON (pra.contextid = pc.id AND pra.userid = ra.userid AND pra.roleid = ra.roleid AND pra.component <> 'enrol_meta' $notignored)
         LEFT JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = ra.userid AND ue.status = :activeuser)
             WHERE pra.id IS NULL OR ue.id IS NULL OR e.status <> :enabledinstance";

    if ($unenrolaction != ENROL_EXT_REMOVED_SUSPEND) {
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach($rs as $ra) {
            role_unassign($ra->roleid, $ra->userid, $ra->contextid, 'enrol_meta', $ra->itemid);
            if ($verbose) {
                mtrace("  unassigning role: $ra->userid ==> $ra->courseid as ".$allroles[$ra->roleid]->shortname);
            }
        }
        $rs->close();
    }


        if (!$syncall) {
        if ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
            $onecourse = $courseid ? "AND e.courseid = :courseid" : "";
            $params = array();
            $params['coursecontext'] = CONTEXT_COURSE;
            $params['courseid'] = $courseid;
            $sql = "SELECT ue.userid, ue.enrolid
                      FROM {user_enrolments} ue
                      JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'meta' $onecourse)
                      JOIN {context} c ON (e.courseid = c.instanceid AND c.contextlevel = :coursecontext)
                 LEFT JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.itemid = e.id AND ra.userid = ue.userid)
                     WHERE ra.id IS NULL";
            $ues = $DB->get_recordset_sql($sql, $params);
            foreach($ues as $ue) {
                if (!isset($instances[$ue->enrolid])) {
                    $instances[$ue->enrolid] = $DB->get_record('enrol', array('id'=>$ue->enrolid));
                }
                $instance = $instances[$ue->enrolid];
                $meta->unenrol_user($instance, $ue->userid);
                if ($verbose) {
                    mtrace("  unenrolling: $ue->userid ==> $instance->courseid (user without role)");
                }
            }
            $ues->close();

        } else {
                        $onecourse = $courseid ? "AND e.courseid = :courseid" : "";
            $params = array();
            $params['coursecontext'] = CONTEXT_COURSE;
            $params['courseid'] = $courseid;
            $params['active'] = ENROL_USER_ACTIVE;
            $sql = "SELECT ue.userid, ue.enrolid
                      FROM {user_enrolments} ue
                      JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'meta' $onecourse)
                      JOIN {context} c ON (e.courseid = c.instanceid AND c.contextlevel = :coursecontext)
                 LEFT JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.itemid = e.id AND ra.userid = ue.userid)
                     WHERE ra.id IS NULL AND ue.status = :active";
            $ues = $DB->get_recordset_sql($sql, $params);
            foreach($ues as $ue) {
                if (!isset($instances[$ue->enrolid])) {
                    $instances[$ue->enrolid] = $DB->get_record('enrol', array('id'=>$ue->enrolid));
                }
                $instance = $instances[$ue->enrolid];
                $meta->update_user_enrol($instance, $ue->userid, ENROL_USER_SUSPENDED);
                if ($verbose) {
                    mtrace("  suspending: $ue->userid ==> $instance->courseid (user without role)");
                }
            }
            $ues->close();
        }
    }

        $affectedusers = groups_sync_with_enrolment('meta', $courseid);
    if ($verbose) {
        foreach ($affectedusers['removed'] as $gm) {
            mtrace("removing user from group: $gm->userid ==> $gm->courseid - $gm->groupname", 1);
        }
        foreach ($affectedusers['added'] as $ue) {
            mtrace("adding user to group: $ue->userid ==> $ue->courseid - $ue->groupname", 1);
        }
    }

    if ($verbose) {
        mtrace('...user enrolment synchronisation finished.');
    }

    return 0;
}


function enrol_meta_create_new_group($courseid, $linkedcourseid) {
    global $DB, $CFG;

    require_once($CFG->dirroot.'/group/lib.php');

    $coursename = $DB->get_field('course', 'fullname', array('id' => $linkedcourseid), MUST_EXIST);
    $a = new stdClass();
    $a->name = $coursename;
    $a->increment = '';
    $inc = 1;
    $groupname = trim(get_string('defaultgroupnametext', 'enrol_meta', $a));
        while ($DB->record_exists('groups', array('name' => $groupname, 'courseid' => $courseid))) {
        $a->increment = '(' . (++$inc) . ')';
        $groupname = trim(get_string('defaultgroupnametext', 'enrol_meta', $a));
    }
        $groupdata = new stdClass();
    $groupdata->courseid = $courseid;
    $groupdata->name = $groupname;
    $groupid = groups_create_group($groupdata);

    return $groupid;
}
