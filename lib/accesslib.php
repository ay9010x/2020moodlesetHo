<?php



defined('MOODLE_INTERNAL') || die();


define('CAP_INHERIT', 0);

define('CAP_ALLOW', 1);

define('CAP_PREVENT', -1);

define('CAP_PROHIBIT', -1000);


define('CONTEXT_SYSTEM', 10);

define('CONTEXT_USER', 30);

define('CONTEXT_COURSECAT', 40);

define('CONTEXT_COURSE', 50);

define('CONTEXT_MODULE', 70);

define('CONTEXT_BLOCK', 80);


define('RISK_MANAGETRUST', 0x0001);

define('RISK_CONFIG',      0x0002);

define('RISK_XSS',         0x0004);

define('RISK_PERSONAL',    0x0008);

define('RISK_SPAM',        0x0010);

define('RISK_DATALOSS',    0x0020);


define('ROLENAME_ORIGINAL', 0);

define('ROLENAME_ALIAS', 1);

define('ROLENAME_BOTH', 2);

define('ROLENAME_ORIGINALANDSHORT', 3);

define('ROLENAME_ALIAS_RAW', 4);

define('ROLENAME_SHORT', 5);

if (!defined('CONTEXT_CACHE_MAX_SIZE')) {
    
    define('CONTEXT_CACHE_MAX_SIZE', 2500);
}


global $ACCESSLIB_PRIVATE;
$ACCESSLIB_PRIVATE = new stdClass();
$ACCESSLIB_PRIVATE->dirtycontexts    = null;    $ACCESSLIB_PRIVATE->accessdatabyuser = array(); $ACCESSLIB_PRIVATE->rolepermissions  = array(); 

function accesslib_clear_all_caches_for_unit_testing() {
    global $USER;
    if (!PHPUNIT_TEST) {
        throw new coding_exception('You must not call clear_all_caches outside of unit tests.');
    }

    accesslib_clear_all_caches(true);

    unset($USER->access);
}


function accesslib_clear_all_caches($resetcontexts) {
    global $ACCESSLIB_PRIVATE;

    $ACCESSLIB_PRIVATE->dirtycontexts    = null;
    $ACCESSLIB_PRIVATE->accessdatabyuser = array();
    $ACCESSLIB_PRIVATE->rolepermissions  = array();

    if ($resetcontexts) {
        context_helper::reset_caches();
    }
}


function get_role_access($roleid) {
    global $DB, $ACCESSLIB_PRIVATE;

    

    
    $accessdata = get_empty_accessdata();

    $accessdata['ra']['/'.SYSCONTEXTID] = array((int)$roleid => (int)$roleid);

    
    

    
    $sql = "SELECT COALESCE(ctx.path, bctx.path) AS path, rc.capability, rc.permission
              FROM {role_capabilities} rc
         LEFT JOIN {context} ctx ON (ctx.id = rc.contextid AND ctx.contextlevel <= ".CONTEXT_COURSE.")
         LEFT JOIN ({context} bctx
                    JOIN {block_instances} bi ON (bi.id = bctx.instanceid)
                    JOIN {context} pctx ON (pctx.id = bi.parentcontextid AND pctx.contextlevel < ".CONTEXT_COURSE.")
                   ) ON (bctx.id = rc.contextid AND bctx.contextlevel = ".CONTEXT_BLOCK.")
             WHERE rc.roleid = :roleid AND (ctx.id IS NOT NULL OR bctx.id IS NOT NULL)";
    $params = array('roleid'=>$roleid);

        $rs = $DB->get_recordset_sql($sql, $params);
    foreach ($rs as $rd) {
        $k = "{$rd->path}:{$roleid}";
        $accessdata['rdef'][$k][$rd->capability] = (int)$rd->permission;
    }
    $rs->close();

        foreach ($accessdata['rdef'] as $k=>$unused) {
        if (!isset($ACCESSLIB_PRIVATE->rolepermissions[$k])) {
            $ACCESSLIB_PRIVATE->rolepermissions[$k] = $accessdata['rdef'][$k];
        }
        $accessdata['rdef_count']++;
        $accessdata['rdef'][$k] =& $ACCESSLIB_PRIVATE->rolepermissions[$k];
    }

    return $accessdata;
}


function get_guest_role() {
    global $CFG, $DB;

    if (empty($CFG->guestroleid)) {
        if ($roles = $DB->get_records('role', array('archetype'=>'guest'))) {
            $guestrole = array_shift($roles);               set_config('guestroleid', $guestrole->id);
            return $guestrole;
        } else {
            debugging('Can not find any guest role!');
            return false;
        }
    } else {
        if ($guestrole = $DB->get_record('role', array('id'=>$CFG->guestroleid))) {
            return $guestrole;
        } else {
                        set_config('guestroleid', '');
            return get_guest_role();
        }
    }
}


function get_modset_role() {
    global $CFG, $DB;

    if (empty($CFG->modsetwsroleid)) {
        if ($roles = $DB->get_records('role', array('archetype'=>'modsetws'))) {
            $modsetrole = array_shift($roles);               set_config('modsetwsroleid', $modsetrole->id);
            return $modsetrole;
        } else {
            debugging('Can not find any MoodleSET role!');
            return false;
        }
    } else {
        if ($modsetrole = $DB->get_record('role', array('id'=>$CFG->modsetwsroleid))) {
            return $modsetrole;
        } else {
                        set_config('modsetwsroleid', '');
            return get_guest_role();
        }
    }
}


function has_capability($capability, context $context, $user = null, $doanything = true) {
    global $USER, $CFG, $SCRIPT, $ACCESSLIB_PRIVATE;

    if (during_initial_install()) {
        if ($SCRIPT === "/$CFG->admin/index.php"
                or $SCRIPT === "/$CFG->admin/cli/install.php"
                or $SCRIPT === "/$CFG->admin/cli/install_database.php"
                or (defined('BEHAT_UTIL') and BEHAT_UTIL)
                or (defined('PHPUNIT_UTIL') and PHPUNIT_UTIL)) {
                        return true;
        } else {
            return false;
        }
    }

    if (strpos($capability, 'moodle/legacy:') === 0) {
        throw new coding_exception('Legacy capabilities can not be used any more!');
    }

    if (!is_bool($doanything)) {
        throw new coding_exception('Capability parameter "doanything" is wierd, only true or false is allowed. This has to be fixed in code.');
    }

        if (!$capinfo = get_capability_info($capability)) {
        debugging('Capability "'.$capability.'" was not found! This has to be fixed in code.');
        return false;
    }

    if (!isset($USER->id)) {
                $USER->id = 0;
        debugging('Capability check being performed on a user with no ID.', DEBUG_DEVELOPER);
    }

        if ($user === null) {
        $userid = $USER->id;
    } else {
        $userid = is_object($user) ? $user->id : $user;
    }

        if (!empty($CFG->forcelogin) and $userid == 0) {
        return false;
    }

        if (($capinfo->captype === 'write') or ($capinfo->riskbitmask & (RISK_XSS | RISK_CONFIG | RISK_DATALOSS))) {
        if (isguestuser($userid) or $userid == 0) {
            return false;
        }
    }

        if ($userid != 0) {
        if ($userid == $USER->id and isset($USER->deleted)) {
                                    if ($USER->deleted) {
                return false;
            }
        } else {
            if (!context_user::instance($userid, IGNORE_MISSING)) {
                                return false;
            }
        }
    }

        if (empty($context->path) or $context->depth == 0) {
                debugging('Context id '.$context->id.' does not have valid path, please use context_helper::build_all_paths()');
        if (is_siteadmin($userid)) {
            return true;
        } else {
            return false;
        }
    }

            if ($doanything) {
        if (is_siteadmin($userid)) {
            if ($userid != $USER->id) {
                return true;
            }
                        if (empty($USER->access['rsw'])) {
                return true;
            }
            $parts = explode('/', trim($context->path, '/'));
            $path = '';
            $switched = false;
            foreach ($parts as $part) {
                $path .= '/' . $part;
                if (!empty($USER->access['rsw'][$path])) {
                    $switched = true;
                    break;
                }
            }
            if (!$switched) {
                return true;
            }
                    }
    }

        $context->reload_if_dirty();

    if ($USER->id == $userid) {
        if (!isset($USER->access)) {
            load_all_capabilities();
        }
        $access =& $USER->access;

    } else {
                get_user_accessdata($userid, true);
        $access =& $ACCESSLIB_PRIVATE->accessdatabyuser[$userid];
    }


            if ($context->contextlevel != CONTEXT_COURSE and $coursecontext = $context->get_course_context(false)) {
        load_course_context($userid, $coursecontext, $access);
    }

    return has_capability_in_accessdata($capability, $context, $access);
}


function has_any_capability(array $capabilities, context $context, $user = null, $doanything = true) {
    foreach ($capabilities as $capability) {
        if (has_capability($capability, $context, $user, $doanything)) {
            return true;
        }
    }
    return false;
}


function has_all_capabilities(array $capabilities, context $context, $user = null, $doanything = true) {
    foreach ($capabilities as $capability) {
        if (!has_capability($capability, $context, $user, $doanything)) {
            return false;
        }
    }
    return true;
}


function guess_if_creator_will_have_course_capability($capability, context $context, $user = null) {
    global $CFG;

    if ($context->contextlevel != CONTEXT_COURSE and $context->contextlevel != CONTEXT_COURSECAT) {
        throw new coding_exception('Only course or course category context expected');
    }

    if (has_capability($capability, $context, $user)) {
                        return true;
    }

    if (!has_capability('moodle/course:create', $context, $user)) {
        return false;
    }

    if (!enrol_is_enabled('manual')) {
        return false;
    }

    if (empty($CFG->creatornewroleid)) {
        return false;
    }

    if ($context->contextlevel == CONTEXT_COURSE) {
        if (is_viewing($context, $user, 'moodle/role:assign') or is_enrolled($context, $user, 'moodle/role:assign')) {
            return false;
        }
    } else {
        if (has_capability('moodle/course:view', $context, $user) and has_capability('moodle/role:assign', $context, $user)) {
            return false;
        }
    }

            list($neededroles, $forbiddenroles) = get_roles_with_cap_in_context($context, $capability);
    return isset($neededroles[$CFG->creatornewroleid]);
}


function is_siteadmin($user_or_id = null) {
    global $CFG, $USER;

    if ($user_or_id === null) {
        $user_or_id = $USER;
    }

    if (empty($user_or_id)) {
        return false;
    }
    if (!empty($user_or_id->id)) {
        $userid = $user_or_id->id;
    } else {
        $userid = $user_or_id;
    }

                        static $knownid, $knownresult, $knownsiteadmins;
    if ($knownid === $userid && $knownsiteadmins === $CFG->siteadmins) {
        return $knownresult;
    }
    $knownid = $userid;
    $knownsiteadmins = $CFG->siteadmins;

    $siteadmins = explode(',', $CFG->siteadmins);
    $knownresult = in_array($userid, $siteadmins);
    return $knownresult;
}


function has_coursecontact_role($userid) {
    global $DB, $CFG;

    if (empty($CFG->coursecontact)) {
        return false;
    }
    $sql = "SELECT 1
              FROM {role_assignments}
             WHERE userid = :userid AND roleid IN ($CFG->coursecontact)";
    return $DB->record_exists_sql($sql, array('userid'=>$userid));
}


function has_capability_in_accessdata($capability, context $context, array &$accessdata) {
    global $CFG;

        $path = $context->path;
    $paths = array($path);
    while($path = rtrim($path, '0123456789')) {
        $path = rtrim($path, '/');
        if ($path === '') {
            break;
        }
        $paths[] = $path;
    }

    $roles = array();
    $switchedrole = false;

        if (!empty($accessdata['rsw'])) {
                foreach ($paths as $path) {
            if (isset($accessdata['rsw'][$path])) {
                                $roles = array($accessdata['rsw'][$path]=>null, $CFG->defaultuserroleid=>null);
                $switchedrole = true;
                break;
            }
        }
    }

    if (!$switchedrole) {
                foreach ($paths as $path) {
            if (isset($accessdata['ra'][$path])) {
                foreach ($accessdata['ra'][$path] as $roleid) {
                    $roles[$roleid] = null;
                }
            }
        }
    }

        $allowed = false;
    foreach ($roles as $roleid => $ignored) {
        foreach ($paths as $path) {
            if (isset($accessdata['rdef']["{$path}:$roleid"][$capability])) {
                $perm = (int)$accessdata['rdef']["{$path}:$roleid"][$capability];
                if ($perm === CAP_PROHIBIT) {
                                        return false;
                }
                if (is_null($roles[$roleid])) {
                    $roles[$roleid] = $perm;
                }
            }
        }
                $allowed = ($allowed or $roles[$roleid] === CAP_ALLOW);
    }

    return $allowed;
}


function require_capability($capability, context $context, $userid = null, $doanything = true,
                            $errormessage = 'nopermissions', $stringfile = '') {
    if (!has_capability($capability, $context, $userid, $doanything)) {
        throw new required_capability_exception($context, $capability, $errormessage, $stringfile);
    }
}


function get_user_access_sitewide($userid) {
    global $CFG, $DB, $ACCESSLIB_PRIVATE;

    

        $raparents = array();
    $accessdata = get_empty_accessdata();

        if (!empty($CFG->defaultuserroleid)) {
        $syscontext = context_system::instance();
        $accessdata['ra'][$syscontext->path][(int)$CFG->defaultuserroleid] = (int)$CFG->defaultuserroleid;
        $raparents[$CFG->defaultuserroleid][$syscontext->id] = $syscontext->id;
    }

        if (!empty($CFG->defaultfrontpageroleid)) {
        $frontpagecontext = context_course::instance(get_site()->id);
        if ($frontpagecontext->path) {
            $accessdata['ra'][$frontpagecontext->path][(int)$CFG->defaultfrontpageroleid] = (int)$CFG->defaultfrontpageroleid;
            $raparents[$CFG->defaultfrontpageroleid][$frontpagecontext->id] = $frontpagecontext->id;
        }
    }

        $sql = "SELECT ctx.path, ra.roleid, ra.contextid
              FROM {role_assignments} ra
              JOIN {context} ctx
                   ON ctx.id = ra.contextid
         LEFT JOIN {block_instances} bi
                   ON (ctx.contextlevel = ".CONTEXT_BLOCK." AND bi.id = ctx.instanceid)
         LEFT JOIN {context} bpctx
                   ON (bpctx.id = bi.parentcontextid)
             WHERE ra.userid = :userid
                   AND (ctx.contextlevel <= ".CONTEXT_COURSE." OR bpctx.contextlevel < ".CONTEXT_COURSE.")";
    $params = array('userid'=>$userid);
    $rs = $DB->get_recordset_sql($sql, $params);
    foreach ($rs as $ra) {
                $accessdata['ra'][$ra->path][(int)$ra->roleid] = (int)$ra->roleid;
        $raparents[$ra->roleid][$ra->contextid] = $ra->contextid;
    }
    $rs->close();

    if (empty($raparents)) {
        return $accessdata;
    }

                $sqls = array();
    $params = array();

    static $cp = 0;
    foreach ($raparents as $roleid=>$ras) {
        $cp++;
        list($sqlcids, $cids) = $DB->get_in_or_equal($ras, SQL_PARAMS_NAMED, 'c'.$cp.'_');
        $params = array_merge($params, $cids);
        $params['r'.$cp] = $roleid;
        $sqls[] = "(SELECT ctx.path, rc.roleid, rc.capability, rc.permission
                     FROM {role_capabilities} rc
                     JOIN {context} ctx
                          ON (ctx.id = rc.contextid)
                     JOIN {context} pctx
                          ON (pctx.id $sqlcids
                              AND (ctx.id = pctx.id
                                   OR ctx.path LIKE ".$DB->sql_concat('pctx.path',"'/%'")."
                                   OR pctx.path LIKE ".$DB->sql_concat('ctx.path',"'/%'")."))
                LEFT JOIN {block_instances} bi
                          ON (ctx.contextlevel = ".CONTEXT_BLOCK." AND bi.id = ctx.instanceid)
                LEFT JOIN {context} bpctx
                          ON (bpctx.id = bi.parentcontextid)
                    WHERE rc.roleid = :r{$cp}
                          AND (ctx.contextlevel <= ".CONTEXT_COURSE." OR bpctx.contextlevel < ".CONTEXT_COURSE.")
                   )";
    }

        $rs = $DB->get_recordset_sql(implode("\nUNION\n", $sqls). "ORDER BY capability", $params);

    foreach ($rs as $rd) {
        $k = $rd->path.':'.$rd->roleid;
        $accessdata['rdef'][$k][$rd->capability] = (int)$rd->permission;
    }
    $rs->close();

        foreach ($accessdata['rdef'] as $k=>$unused) {
        if (!isset($ACCESSLIB_PRIVATE->rolepermissions[$k])) {
            $ACCESSLIB_PRIVATE->rolepermissions[$k] = $accessdata['rdef'][$k];
        }
        $accessdata['rdef_count']++;
        $accessdata['rdef'][$k] =& $ACCESSLIB_PRIVATE->rolepermissions[$k];
    }

    return $accessdata;
}


function load_course_context($userid, context_course $coursecontext, &$accessdata) {
    global $DB, $CFG, $ACCESSLIB_PRIVATE;

    if (empty($coursecontext->path)) {
                return;
    }

    if (isset($accessdata['loaded'][$coursecontext->instanceid])) {
                return;
    }

    $roles = array();

    if (empty($userid)) {
        if (!empty($CFG->notloggedinroleid)) {
            $roles[$CFG->notloggedinroleid] = $CFG->notloggedinroleid;
        }

    } else if (isguestuser($userid)) {
        if ($guestrole = get_guest_role()) {
            $roles[$guestrole->id] = $guestrole->id;
        }

    } else {
                list($parentsaself, $params) = $DB->get_in_or_equal($coursecontext->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'pc_');
        $params['userid'] = $userid;
        $params['children'] = $coursecontext->path."/%";
        $sql = "SELECT ra.*, ctx.path
                  FROM {role_assignments} ra
                  JOIN {context} ctx ON ra.contextid = ctx.id
                 WHERE ra.userid = :userid AND (ctx.id $parentsaself OR ctx.path LIKE :children)";
        $rs = $DB->get_recordset_sql($sql, $params);

                foreach ($rs as $ra) {
            $accessdata['ra'][$ra->path][(int)$ra->roleid] = (int)$ra->roleid;
            $roles[$ra->roleid] = $ra->roleid;
        }
        $rs->close();

                if (!empty($CFG->defaultfrontpageroleid)) {
            $frontpagecontext = context_course::instance(get_site()->id);
            if ($frontpagecontext->id == $coursecontext->id) {
                $roles[$CFG->defaultfrontpageroleid] = $CFG->defaultfrontpageroleid;
            }
        }

                if (!empty($CFG->defaultuserroleid)) {
            $roles[$CFG->defaultuserroleid] = $CFG->defaultuserroleid;
        }
    }

    if (!$roles) {
                $accessdata['loaded'][$coursecontext->instanceid] = 1;
        return;
    }

        $params = array('pathprefix' => $coursecontext->path . '/%');
    list($parentsaself, $rparams) = $DB->get_in_or_equal($coursecontext->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'pc_');
    $params = array_merge($params, $rparams);
    list($roleids, $rparams) = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED, 'r_');
    $params = array_merge($params, $rparams);

    $sql = "SELECT ctx.path, rc.roleid, rc.capability, rc.permission
                 FROM {context} ctx
                 JOIN {role_capabilities} rc ON rc.contextid = ctx.id
                WHERE rc.roleid $roleids
                  AND (ctx.id $parentsaself OR ctx.path LIKE :pathprefix)
             ORDER BY rc.capability";     $rs = $DB->get_recordset_sql($sql, $params);

    $newrdefs = array();
    foreach ($rs as $rd) {
        $k = $rd->path.':'.$rd->roleid;
        if (isset($accessdata['rdef'][$k])) {
            continue;
        }
        $newrdefs[$k][$rd->capability] = (int)$rd->permission;
    }
    $rs->close();

        foreach ($newrdefs as $k=>$unused) {
        if (!isset($ACCESSLIB_PRIVATE->rolepermissions[$k])) {
            $ACCESSLIB_PRIVATE->rolepermissions[$k] = $newrdefs[$k];
        }
        $accessdata['rdef_count']++;
        $accessdata['rdef'][$k] =& $ACCESSLIB_PRIVATE->rolepermissions[$k];
    }

    $accessdata['loaded'][$coursecontext->instanceid] = 1;

            dedupe_user_access();
}


function load_role_access_by_context($roleid, context $context, &$accessdata) {
    global $DB, $ACCESSLIB_PRIVATE;

    

    if (empty($context->path)) {
                return;
    }

    list($parentsaself, $params) = $DB->get_in_or_equal($context->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'pc_');
    $params['roleid'] = $roleid;
    $params['childpath'] = $context->path.'/%';

    $sql = "SELECT ctx.path, rc.capability, rc.permission
              FROM {role_capabilities} rc
              JOIN {context} ctx ON (rc.contextid = ctx.id)
             WHERE rc.roleid = :roleid AND (ctx.id $parentsaself OR ctx.path LIKE :childpath)
          ORDER BY rc.capability";     $rs = $DB->get_recordset_sql($sql, $params);

    $newrdefs = array();
    foreach ($rs as $rd) {
        $k = $rd->path.':'.$roleid;
        if (isset($accessdata['rdef'][$k])) {
            continue;
        }
        $newrdefs[$k][$rd->capability] = (int)$rd->permission;
    }
    $rs->close();

        foreach ($newrdefs as $k=>$unused) {
        if (!isset($ACCESSLIB_PRIVATE->rolepermissions[$k])) {
            $ACCESSLIB_PRIVATE->rolepermissions[$k] = $newrdefs[$k];
        }
        $accessdata['rdef_count']++;
        $accessdata['rdef'][$k] =& $ACCESSLIB_PRIVATE->rolepermissions[$k];
    }
}


function get_empty_accessdata() {
    $accessdata               = array();     $accessdata['ra']         = array();
    $accessdata['rdef']       = array();
    $accessdata['rdef_count'] = 0;           $accessdata['rdef_lcc']   = 0;           $accessdata['loaded']     = array();     $accessdata['time']       = time();
    $accessdata['rsw']        = array();

    return $accessdata;
}


function get_user_accessdata($userid, $preloadonly=false) {
    global $CFG, $ACCESSLIB_PRIVATE, $USER;

    if (!empty($USER->access['rdef']) and empty($ACCESSLIB_PRIVATE->rolepermissions)) {
                foreach ($USER->access['rdef'] as $k=>$v) {
            $ACCESSLIB_PRIVATE->rolepermissions[$k] =& $USER->access['rdef'][$k];
        }
        $ACCESSLIB_PRIVATE->accessdatabyuser[$USER->id] = $USER->access;
    }

    if (!isset($ACCESSLIB_PRIVATE->accessdatabyuser[$userid])) {
        if (empty($userid)) {
            if (!empty($CFG->notloggedinroleid)) {
                $accessdata = get_role_access($CFG->notloggedinroleid);
            } else {
                                return get_empty_accessdata();
            }

        } else if (isguestuser($userid)) {
            if ($guestrole = get_guest_role()) {
                $accessdata = get_role_access($guestrole->id);
            } else {
                                return get_empty_accessdata();
            }

        } else {
            $accessdata = get_user_access_sitewide($userid);         }

        $ACCESSLIB_PRIVATE->accessdatabyuser[$userid] = $accessdata;
    }

    if ($preloadonly) {
        return;
    } else {
        return $ACCESSLIB_PRIVATE->accessdatabyuser[$userid];
    }
}


function dedupe_user_access() {
    global $USER;

    if (CLI_SCRIPT) {
                return;
    }

    if (empty($USER->access['rdef_count'])) {
                return;
    }

        if ($USER->access['rdef_count'] - $USER->access['rdef_lcc'] > 10) {
                return;
    }

    $hashmap = array();
    foreach ($USER->access['rdef'] as $k=>$def) {
        $hash = sha1(serialize($def));
        if (isset($hashmap[$hash])) {
            $USER->access['rdef'][$k] =& $hashmap[$hash];
        } else {
            $hashmap[$hash] =& $USER->access['rdef'][$k];
        }
    }

    $USER->access['rdef_lcc'] = $USER->access['rdef_count'];
}


function load_all_capabilities() {
    global $USER;

        if (during_initial_install()) {
        return;
    }

    if (!isset($USER->id)) {
                $USER->id = 0;
    }

    unset($USER->access);
    $USER->access = get_user_accessdata($USER->id);

        dedupe_user_access();

        unset($USER->mycourses);

        $USER->enrol = array('enrolled'=>array(), 'tempguest'=>array());
}


function reload_all_capabilities() {
    global $USER, $DB, $ACCESSLIB_PRIVATE;

        $sw = array();
    if (!empty($USER->access['rsw'])) {
        $sw = $USER->access['rsw'];
    }

    accesslib_clear_all_caches(true);
    unset($USER->access);
    $ACCESSLIB_PRIVATE->dirtycontexts = array(); 
    load_all_capabilities();

    foreach ($sw as $path => $roleid) {
        if ($record = $DB->get_record('context', array('path'=>$path))) {
            $context = context::instance_by_id($record->id);
            role_switch($roleid, $context);
        }
    }
}


function load_temp_course_role(context_course $coursecontext, $roleid) {
    global $USER, $SITE;

    if (empty($roleid)) {
        debugging('invalid role specified in load_temp_course_role()');
        return;
    }

    if ($coursecontext->instanceid == $SITE->id) {
        debugging('Can not use temp roles on the frontpage');
        return;
    }

    if (!isset($USER->access)) {
        load_all_capabilities();
    }

    $coursecontext->reload_if_dirty();

    if (isset($USER->access['ra'][$coursecontext->path][$roleid])) {
        return;
    }

        load_course_context($USER->id, $coursecontext, $USER->access);

    $USER->access['ra'][$coursecontext->path][(int)$roleid] = (int)$roleid;

    load_role_access_by_context($roleid, $coursecontext, $USER->access);
}


function remove_temp_course_roles(context_course $coursecontext) {
    global $DB, $USER, $SITE;

    if ($coursecontext->instanceid == $SITE->id) {
        debugging('Can not use temp roles on the frontpage');
        return;
    }

    if (empty($USER->access['ra'][$coursecontext->path])) {
                return;
    }

    $sql = "SELECT DISTINCT ra.roleid AS id
              FROM {role_assignments} ra
             WHERE ra.contextid = :contextid AND ra.userid = :userid";
    $ras = $DB->get_records_sql($sql, array('contextid'=>$coursecontext->id, 'userid'=>$USER->id));

    $USER->access['ra'][$coursecontext->path] = array();
    foreach($ras as $r) {
        $USER->access['ra'][$coursecontext->path][(int)$r->id] = (int)$r->id;
    }
}


function get_role_archetypes() {
    return array(
        'manager'           => 'manager',
        'coursecreator'     => 'coursecreator',
        'modsetws'          => 'modsetws',
        'departmentmanager' => 'departmentmanager',
        'departmentassistant' => 'departmentassistant',
        'editingteacher'    => 'editingteacher',
        'teacher'           => 'teacher',
        'teacherassistant'  => 'teacherassistant',
        'student'           => 'student',
        'auditor'           => 'auditor',
        'guest'             => 'guest',
        'user'              => 'user',
        'frontpage'         => 'frontpage'
    );
}


function assign_legacy_capabilities($capability, $legacyperms) {

    $archetypes = get_role_archetypes();

    foreach ($legacyperms as $type => $perm) {

        $systemcontext = context_system::instance();
        if ($type === 'admin') {
            debugging('Legacy type admin in access.php was renamed to manager, please update the code.');
            $type = 'manager';
        }
        
        if ($type === 'moodleset') {
                        $type = 'modsetws';
        }

        if (!array_key_exists($type, $archetypes)) {
            print_error('invalidlegacy', '', '', $type);
        }

        if ($roles = get_archetype_roles($type)) {
            foreach ($roles as $role) {
                                if (!assign_capability($capability, $perm, $role->id, $systemcontext->id)) {
                    return false;
                }
            }
        }
    }
    return true;
}


function is_safe_capability($capability) {
    return !((RISK_DATALOSS | RISK_MANAGETRUST | RISK_CONFIG | RISK_XSS | RISK_PERSONAL) & $capability->riskbitmask);
}


function get_local_override($roleid, $contextid, $capability) {
    global $DB;
    return $DB->get_record('role_capabilities', array('roleid'=>$roleid, 'capability'=>$capability, 'contextid'=>$contextid));
}


function get_context_info_array($contextid) {
    global $DB;

    $context = context::instance_by_id($contextid, MUST_EXIST);
    $course  = null;
    $cm      = null;

    if ($context->contextlevel == CONTEXT_COURSE) {
        $course = $DB->get_record('course', array('id'=>$context->instanceid), '*', MUST_EXIST);

    } else if ($context->contextlevel == CONTEXT_MODULE) {
        $cm = get_coursemodule_from_id('', $context->instanceid, 0, false, MUST_EXIST);
        $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

    } else if ($context->contextlevel == CONTEXT_BLOCK) {
        $parent = $context->get_parent_context();

        if ($parent->contextlevel == CONTEXT_COURSE) {
            $course = $DB->get_record('course', array('id'=>$parent->instanceid), '*', MUST_EXIST);
        } else if ($parent->contextlevel == CONTEXT_MODULE) {
            $cm = get_coursemodule_from_id('', $parent->instanceid, 0, false, MUST_EXIST);
            $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
        }
    }

    return array($context, $course, $cm);
}


function create_role($name, $shortname, $description, $archetype = '') {
    global $DB;

    if (strpos($archetype, 'moodle/legacy:') !== false) {
        throw new coding_exception('Use new role archetype parameter in create_role() instead of old legacy capabilities.');
    }

        $archetypes = get_role_archetypes();
    if (empty($archetypes[$archetype])) {
        $archetype = '';
    }

        $role = new stdClass();
    $role->name        = $name;
    $role->shortname   = $shortname;
    $role->description = $description;
    $role->archetype   = $archetype;

        $role->sortorder = $DB->get_field('role', 'MAX(sortorder) + 1', array());
    if (empty($role->sortorder)) {
        $role->sortorder = 1;
    }
    $id = $DB->insert_record('role', $role);

    return $id;
}


function delete_role($roleid) {
    global $DB;

        role_unassign_all(array('roleid'=>$roleid));

        $DB->delete_records('role_capabilities',   array('roleid'=>$roleid));
    $DB->delete_records('role_allow_assign',   array('roleid'=>$roleid));
    $DB->delete_records('role_allow_assign',   array('allowassign'=>$roleid));
    $DB->delete_records('role_allow_override', array('roleid'=>$roleid));
    $DB->delete_records('role_allow_override', array('allowoverride'=>$roleid));
    $DB->delete_records('role_names',          array('roleid'=>$roleid));
    $DB->delete_records('role_context_levels', array('roleid'=>$roleid));

        $role = $DB->get_record('role', array('id'=>$roleid));

        $DB->delete_records('role', array('id'=>$roleid));

        $event = \core\event\role_deleted::create(
        array(
            'context' => context_system::instance(),
            'objectid' => $roleid,
            'other' =>
                array(
                    'shortname' => $role->shortname,
                    'description' => $role->description,
                    'archetype' => $role->archetype
                )
            )
        );
    $event->add_record_snapshot('role', $role);
    $event->trigger();

    return true;
}


function assign_capability($capability, $permission, $roleid, $contextid, $overwrite = false) {
    global $USER, $DB;

    if ($contextid instanceof context) {
        $context = $contextid;
    } else {
        $context = context::instance_by_id($contextid);
    }

    if (empty($permission) || $permission == CAP_INHERIT) {         unassign_capability($capability, $roleid, $context->id);
        return true;
    }

    $existing = $DB->get_record('role_capabilities', array('contextid'=>$context->id, 'roleid'=>$roleid, 'capability'=>$capability));

    if ($existing and !$overwrite) {           return true;
    }

    $cap = new stdClass();
    $cap->contextid    = $context->id;
    $cap->roleid       = $roleid;
    $cap->capability   = $capability;
    $cap->permission   = $permission;
    $cap->timemodified = time();
    $cap->modifierid   = empty($USER->id) ? 0 : $USER->id;

    if ($existing) {
        $cap->id = $existing->id;
        $DB->update_record('role_capabilities', $cap);
    } else {
        if ($DB->record_exists('context', array('id'=>$context->id))) {
            $DB->insert_record('role_capabilities', $cap);
        }
    }
    return true;
}


function unassign_capability($capability, $roleid, $contextid = null) {
    global $DB;

    if (!empty($contextid)) {
        if ($contextid instanceof context) {
            $context = $contextid;
        } else {
            $context = context::instance_by_id($contextid);
        }
                $DB->delete_records('role_capabilities', array('capability'=>$capability, 'roleid'=>$roleid, 'contextid'=>$context->id));
    } else {
        $DB->delete_records('role_capabilities', array('capability'=>$capability, 'roleid'=>$roleid));
    }
    return true;
}


function get_roles_with_capability($capability, $permission = null, $context = null) {
    global $DB;

    if ($context) {
        $contexts = $context->get_parent_context_ids(true);
        list($insql, $params) = $DB->get_in_or_equal($contexts, SQL_PARAMS_NAMED, 'ctx');
        $contextsql = "AND rc.contextid $insql";
    } else {
        $params = array();
        $contextsql = '';
    }

    if ($permission) {
        $permissionsql = " AND rc.permission = :permission";
        $params['permission'] = $permission;
    } else {
        $permissionsql = '';
    }

    $sql = "SELECT r.*
              FROM {role} r
             WHERE r.id IN (SELECT rc.roleid
                              FROM {role_capabilities} rc
                             WHERE rc.capability = :capname
                                   $contextsql
                                   $permissionsql)";
    $params['capname'] = $capability;


    return $DB->get_records_sql($sql, $params);
}


function role_assign($roleid, $userid, $contextid, $component = '', $itemid = 0, $timemodified = '') {
    global $USER, $DB, $CFG;
        if ($contextid === 0 or is_numeric($component)) {
        throw new coding_exception('Invalid call to role_assign(), code needs to be updated to use new order of parameters');
    }

        if (empty($roleid)) {
        throw new coding_exception('Invalid call to role_assign(), roleid can not be empty');
    }

    if (empty($userid)) {
        throw new coding_exception('Invalid call to role_assign(), userid can not be empty');
    }

    if ($itemid) {
        if (strpos($component, '_') === false) {
            throw new coding_exception('Invalid call to role_assign(), component must start with plugin type such as"enrol_" when itemid specified', 'component:'.$component);
        }
    } else {
        $itemid = 0;
        if ($component !== '' and strpos($component, '_') === false) {
            throw new coding_exception('Invalid call to role_assign(), invalid component string', 'component:'.$component);
        }
    }

    if (!$DB->record_exists('user', array('id'=>$userid, 'deleted'=>0))) {
        throw new coding_exception('User ID does not exist or is deleted!', 'userid:'.$userid);
    }

    if ($contextid instanceof context) {
        $context = $contextid;
    } else {
        $context = context::instance_by_id($contextid, MUST_EXIST);
    }

    if (!$timemodified) {
        $timemodified = time();
    }

        $ras = $DB->get_records('role_assignments', array('roleid'=>$roleid, 'contextid'=>$context->id, 'userid'=>$userid, 'component'=>$component, 'itemid'=>$itemid), 'id');

    if ($ras) {
                if (count($ras) > 1) {
                        $ra = array_shift($ras);
            foreach ($ras as $r) {
                $DB->delete_records('role_assignments', array('id'=>$r->id));
            }
        } else {
            $ra = reset($ras);
        }

                return $ra->id;
    }

        $ra = new stdClass();
    $ra->roleid       = $roleid;
    $ra->contextid    = $context->id;
    $ra->userid       = $userid;
    $ra->component    = $component;
    $ra->itemid       = $itemid;
    $ra->timemodified = $timemodified;
    $ra->modifierid   = empty($USER->id) ? 0 : $USER->id;
    $ra->sortorder    = 0;

    $ra->id = $DB->insert_record('role_assignments', $ra);

        $context->mark_dirty();

    if (!empty($USER->id) && $USER->id == $userid) {
                reload_all_capabilities();
    }

    require_once($CFG->libdir . '/coursecatlib.php');
    coursecat::role_assignment_changed($roleid, $context);

    $event = \core\event\role_assigned::create(array(
        'context' => $context,
        'objectid' => $ra->roleid,
        'relateduserid' => $ra->userid,
        'other' => array(
            'id' => $ra->id,
            'component' => $ra->component,
            'itemid' => $ra->itemid
        )
    ));
    $event->add_record_snapshot('role_assignments', $ra);
    $event->trigger();

    return $ra->id;
}


function role_unassign($roleid, $userid, $contextid, $component = '', $itemid = 0) {
        if ($roleid == 0 or $userid == 0 or $contextid == 0) {
        throw new coding_exception('Invalid call to role_unassign(), please use role_unassign_all() when removing multiple role assignments');
    }

    if ($itemid) {
        if (strpos($component, '_') === false) {
            throw new coding_exception('Invalid call to role_assign(), component must start with plugin type such as "enrol_" when itemid specified', 'component:'.$component);
        }
    } else {
        $itemid = 0;
        if ($component !== '' and strpos($component, '_') === false) {
            throw new coding_exception('Invalid call to role_assign(), invalid component string', 'component:'.$component);
        }
    }

    role_unassign_all(array('roleid'=>$roleid, 'userid'=>$userid, 'contextid'=>$contextid, 'component'=>$component, 'itemid'=>$itemid), false, false);
}


function role_unassign_all(array $params, $subcontexts = false, $includemanual = false) {
    global $USER, $CFG, $DB;
    require_once($CFG->libdir . '/coursecatlib.php');

    if (!$params) {
        throw new coding_exception('Missing parameters in role_unsassign_all() call');
    }

    $allowed = array('roleid', 'userid', 'contextid', 'component', 'itemid');
    foreach ($params as $key=>$value) {
        if (!in_array($key, $allowed)) {
            throw new coding_exception('Unknown role_unsassign_all() parameter key', 'key:'.$key);
        }
    }

    if (isset($params['component']) and $params['component'] !== '' and strpos($params['component'], '_') === false) {
        throw new coding_exception('Invalid component paramter in role_unsassign_all() call', 'component:'.$params['component']);
    }

    if ($includemanual) {
        if (!isset($params['component']) or $params['component'] === '') {
            throw new coding_exception('include manual parameter requires component parameter in role_unsassign_all() call');
        }
    }

    if ($subcontexts) {
        if (empty($params['contextid'])) {
            throw new coding_exception('subcontexts paramtere requires component parameter in role_unsassign_all() call');
        }
    }

    $ras = $DB->get_records('role_assignments', $params);
    foreach($ras as $ra) {
        $DB->delete_records('role_assignments', array('id'=>$ra->id));
        if ($context = context::instance_by_id($ra->contextid, IGNORE_MISSING)) {
                        $context->mark_dirty();
                        if (!empty($USER->id) && $USER->id == $ra->userid) {
                reload_all_capabilities();
            }
            $event = \core\event\role_unassigned::create(array(
                'context' => $context,
                'objectid' => $ra->roleid,
                'relateduserid' => $ra->userid,
                'other' => array(
                    'id' => $ra->id,
                    'component' => $ra->component,
                    'itemid' => $ra->itemid
                )
            ));
            $event->add_record_snapshot('role_assignments', $ra);
            $event->trigger();
            coursecat::role_assignment_changed($ra->roleid, $context);
        }
    }
    unset($ras);

        if ($subcontexts and $context = context::instance_by_id($params['contextid'], IGNORE_MISSING)) {
        if ($params['contextid'] instanceof context) {
            $context = $params['contextid'];
        } else {
            $context = context::instance_by_id($params['contextid'], IGNORE_MISSING);
        }

        if ($context) {
            $contexts = $context->get_child_contexts();
            $mparams = $params;
            foreach($contexts as $context) {
                $mparams['contextid'] = $context->id;
                $ras = $DB->get_records('role_assignments', $mparams);
                foreach($ras as $ra) {
                    $DB->delete_records('role_assignments', array('id'=>$ra->id));
                                        $context->mark_dirty();
                                        if (!empty($USER->id) && $USER->id == $ra->userid) {
                        reload_all_capabilities();
                    }
                    $event = \core\event\role_unassigned::create(
                        array('context'=>$context, 'objectid'=>$ra->roleid, 'relateduserid'=>$ra->userid,
                            'other'=>array('id'=>$ra->id, 'component'=>$ra->component, 'itemid'=>$ra->itemid)));
                    $event->add_record_snapshot('role_assignments', $ra);
                    $event->trigger();
                    coursecat::role_assignment_changed($ra->roleid, $context);
                }
            }
        }
    }

        if ($includemanual) {
        $params['component'] = '';
        role_unassign_all($params, $subcontexts, false);
    }
}


function isloggedin() {
    global $USER;

    return (!empty($USER->id));
}


function isguestuser($user = null) {
    global $USER, $DB, $CFG;

        if (empty($CFG->siteguest)) {
        if (!$guestid = $DB->get_field('user', 'id', array('username'=>'guest', 'mnethostid'=>$CFG->mnet_localhost_id))) {
                        return false;
        }
        set_config('siteguest', $guestid);
    }
    if ($user === null) {
        $user = $USER;
    }

    if ($user === null) {
                return false;

    } else if (is_numeric($user)) {
        return ($CFG->siteguest == $user);

    } else if (is_object($user)) {
        if (empty($user->id)) {
            return false;         } else {
            return ($CFG->siteguest == $user->id);
        }

    } else {
        throw new coding_exception('Invalid user parameter supplied for isguestuser() function!');
    }
}


function is_guest(context $context, $user = null) {
    global $USER;

        $coursecontext = $context->get_course_context();

        if ($user === null) {
        $userid = isset($USER->id) ? $USER->id : 0;
    } else {
        $userid = is_object($user) ? $user->id : $user;
    }

    if (isguestuser($userid)) {
                return true;
    }

    if (has_capability('moodle/course:view', $coursecontext, $user)) {
                return false;
    }

        if (is_enrolled($coursecontext, $user, '', true)) {
        return false;
    }

    return true;
}


function is_viewing(context $context, $user = null, $withcapability = '') {
        $coursecontext = $context->get_course_context();

    if (isguestuser($user)) {
                return false;
    }

    if (!has_capability('moodle/course:view', $coursecontext, $user)) {
                return false;
    }

    if ($withcapability and !has_capability($withcapability, $context, $user)) {
                return false;
    }

    return true;
}


function is_enrolled(context $context, $user = null, $withcapability = '', $onlyactive = false) {
    global $USER, $DB;

        $coursecontext = $context->get_course_context();

        if ($user === null) {
        $userid = isset($USER->id) ? $USER->id : 0;
    } else {
        $userid = is_object($user) ? $user->id : $user;
    }

    if (empty($userid)) {
                return false;
    } else if (isguestuser($userid)) {
                return false;
    }

    if ($coursecontext->instanceid == SITEID) {
            } else {
                if ($USER->id == $userid) {
            $coursecontext->reload_if_dirty();
            if (isset($USER->enrol['enrolled'][$coursecontext->instanceid])) {
                if ($USER->enrol['enrolled'][$coursecontext->instanceid] > time()) {
                    if ($withcapability and !has_capability($withcapability, $context, $userid)) {
                        return false;
                    }
                    return true;
                }
            }
        }

        if ($onlyactive) {
                        $until = enrol_get_enrolment_end($coursecontext->instanceid, $userid);

            if ($until === false) {
                return false;
            }

            if ($USER->id == $userid) {
                if ($until == 0) {
                    $until = ENROL_MAX_TIMESTAMP;
                }
                $USER->enrol['enrolled'][$coursecontext->instanceid] = $until;
                if (isset($USER->enrol['tempguest'][$coursecontext->instanceid])) {
                    unset($USER->enrol['tempguest'][$coursecontext->instanceid]);
                    remove_temp_course_roles($coursecontext);
                }
            }

        } else {
                        $sql = "SELECT 'x'
                      FROM {user_enrolments} ue
                      JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = :courseid)
                      JOIN {user} u ON u.id = ue.userid
                     WHERE ue.userid = :userid AND u.deleted = 0";
            $params = array('userid'=>$userid, 'courseid'=>$coursecontext->instanceid);
            if (!$DB->record_exists_sql($sql, $params)) {
                return false;
            }
        }
    }

    if ($withcapability and !has_capability($withcapability, $context, $userid)) {
        return false;
    }

    return true;
}


function can_access_course(stdClass $course, $user = null, $withcapability = '', $onlyactive = false) {
    global $DB, $USER;

        if ($course instanceof context) {
        if ($course instanceof context_course) {
            debugging('deprecated context parameter, please use $course record');
            $coursecontext = $course;
            $course = $DB->get_record('course', array('id'=>$coursecontext->instanceid));
        } else {
            debugging('Invalid context parameter, please use $course record');
            return false;
        }
    } else {
        $coursecontext = context_course::instance($course->id);
    }

    if (!isset($USER->id)) {
                $USER->id = 0;
        debugging('Course access check being performed on a user with no ID.', DEBUG_DEVELOPER);
    }

        if ($user === null) {
        $userid = $USER->id;
    } else {
        $userid = is_object($user) ? $user->id : $user;
    }
    unset($user);

    if ($withcapability and !has_capability($withcapability, $coursecontext, $userid)) {
        return false;
    }

    if ($userid == $USER->id) {
        if (!empty($USER->access['rsw'][$coursecontext->path])) {
                        return true;
        }
    }

    if (!$course->visible and !has_capability('moodle/course:viewhiddencourses', $coursecontext, $userid)) {
        return false;
    }

    if (is_viewing($coursecontext, $userid)) {
        return true;
    }

    if ($userid != $USER->id) {
                return is_enrolled($coursecontext, $userid, '', $onlyactive);
    }

    
    $coursecontext->reload_if_dirty();

    if (isset($USER->enrol['enrolled'][$course->id])) {
        if ($USER->enrol['enrolled'][$course->id] > time()) {
            return true;
        }
    }
    if (isset($USER->enrol['tempguest'][$course->id])) {
        if ($USER->enrol['tempguest'][$course->id] > time()) {
            return true;
        }
    }

    if (is_enrolled($coursecontext, $USER, '', $onlyactive)) {
        return true;
    }

        $instances = $DB->get_records('enrol', array('courseid'=>$course->id, 'status'=>ENROL_INSTANCE_ENABLED), 'sortorder, id ASC');
    $enrols = enrol_get_plugins(true);
    foreach($instances as $instance) {
        if (!isset($enrols[$instance->enrol])) {
            continue;
        }
                $until = $enrols[$instance->enrol]->try_guestaccess($instance);
        if ($until !== false and $until > time()) {
            $USER->enrol['tempguest'][$course->id] = $until;
            return true;
        }
    }
    if (isset($USER->enrol['tempguest'][$course->id])) {
        unset($USER->enrol['tempguest'][$course->id]);
        remove_temp_course_roles($coursecontext);
    }

    return false;
}


function get_enrolled_sql(context $context, $withcapability = '', $groupid = 0, $onlyactive = false, $onlysuspended = false) {
    global $DB, $CFG;

        static $i = 0;
    $i++;
    $prefix = 'eu'.$i.'_';

        $coursecontext = $context->get_course_context();

    $isfrontpage = ($coursecontext->instanceid == SITEID);

    if ($onlyactive && $onlysuspended) {
        throw new coding_exception("Both onlyactive and onlysuspended are set, this is probably not what you want!");
    }
    if ($isfrontpage && $onlysuspended) {
        throw new coding_exception("onlysuspended is not supported on frontpage; please add your own early-exit!");
    }

    $joins  = array();
    $wheres = array();
    $params = array();

    list($contextids, $contextpaths) = get_context_info_list($context);

        if ($withcapability) {
        list($incontexts, $cparams) = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED, 'ctx');
        $cparams['cap'] = $withcapability;

        $defs = array();
        $sql = "SELECT rc.id, rc.roleid, rc.permission, ctx.path
                  FROM {role_capabilities} rc
                  JOIN {context} ctx on rc.contextid = ctx.id
                 WHERE rc.contextid $incontexts AND rc.capability = :cap";
        $rcs = $DB->get_records_sql($sql, $cparams);
        foreach ($rcs as $rc) {
            $defs[$rc->path][$rc->roleid] = $rc->permission;
        }

        $access = array();
        if (!empty($defs)) {
            foreach ($contextpaths as $path) {
                if (empty($defs[$path])) {
                    continue;
                }
                foreach($defs[$path] as $roleid => $perm) {
                    if ($perm == CAP_PROHIBIT) {
                        $access[$roleid] = CAP_PROHIBIT;
                        continue;
                    }
                    if (!isset($access[$roleid])) {
                        $access[$roleid] = (int)$perm;
                    }
                }
            }
        }

        unset($defs);

                $needed     = array();         $prohibited = array();         foreach ($access as $roleid => $perm) {
            if ($perm == CAP_PROHIBIT) {
                unset($needed[$roleid]);
                $prohibited[$roleid] = true;
            } else if ($perm == CAP_ALLOW and empty($prohibited[$roleid])) {
                $needed[$roleid] = true;
            }
        }

        $defaultuserroleid      = isset($CFG->defaultuserroleid) ? $CFG->defaultuserroleid : 0;
        $defaultfrontpageroleid = isset($CFG->defaultfrontpageroleid) ? $CFG->defaultfrontpageroleid : 0;

        $nobody = false;

        if ($isfrontpage) {
            if (!empty($prohibited[$defaultuserroleid]) or !empty($prohibited[$defaultfrontpageroleid])) {
                $nobody = true;
            } else if (!empty($needed[$defaultuserroleid]) or !empty($needed[$defaultfrontpageroleid])) {
                                $needed = array();
            } else if (empty($needed)) {
                $nobody = true;
            }
        } else {
            if (!empty($prohibited[$defaultuserroleid])) {
                $nobody = true;
            } else if (!empty($needed[$defaultuserroleid])) {
                                $needed = array();
            } else if (empty($needed)) {
                $nobody = true;
            }
        }

        if ($nobody) {
                        $wheres[] = "1 = 2";

        } else {

            if ($needed) {
                $ctxids = implode(',', $contextids);
                $roleids = implode(',', array_keys($needed));
                $joins[] = "JOIN {role_assignments} {$prefix}ra3 ON ({$prefix}ra3.userid = {$prefix}u.id AND {$prefix}ra3.roleid IN ($roleids) AND {$prefix}ra3.contextid IN ($ctxids))";
            }

            if ($prohibited) {
                $ctxids = implode(',', $contextids);
                $roleids = implode(',', array_keys($prohibited));
                $joins[] = "LEFT JOIN {role_assignments} {$prefix}ra4 ON ({$prefix}ra4.userid = {$prefix}u.id AND {$prefix}ra4.roleid IN ($roleids) AND {$prefix}ra4.contextid IN ($ctxids))";
                $wheres[] = "{$prefix}ra4.id IS NULL";
            }

            if ($groupid) {
                $joins[] = "JOIN {groups_members} {$prefix}gm ON ({$prefix}gm.userid = {$prefix}u.id AND {$prefix}gm.groupid = :{$prefix}gmid)";
                $params["{$prefix}gmid"] = $groupid;
            }
        }

    } else {
        if ($groupid) {
            $joins[] = "JOIN {groups_members} {$prefix}gm ON ({$prefix}gm.userid = {$prefix}u.id AND {$prefix}gm.groupid = :{$prefix}gmid)";
            $params["{$prefix}gmid"] = $groupid;
        }
    }

    $wheres[] = "{$prefix}u.deleted = 0 AND {$prefix}u.id <> :{$prefix}guestid";
    $params["{$prefix}guestid"] = $CFG->siteguest;

    if ($isfrontpage) {
            } else {
        $where1 = "{$prefix}ue.status = :{$prefix}active AND {$prefix}e.status = :{$prefix}enabled";
        $where2 = "{$prefix}ue.timestart < :{$prefix}now1 AND ({$prefix}ue.timeend = 0 OR {$prefix}ue.timeend > :{$prefix}now2)";
        $ejoin = "JOIN {enrol} {$prefix}e ON ({$prefix}e.id = {$prefix}ue.enrolid AND {$prefix}e.courseid = :{$prefix}courseid)";
        $params[$prefix.'courseid'] = $coursecontext->instanceid;

        if (!$onlysuspended) {
            $joins[] = "JOIN {user_enrolments} {$prefix}ue ON {$prefix}ue.userid = {$prefix}u.id";
            $joins[] = $ejoin;
            if ($onlyactive) {
                $wheres[] = "$where1 AND $where2";
            }
        } else {
                                    $enrolselect = "SELECT DISTINCT {$prefix}ue.userid FROM {user_enrolments} {$prefix}ue $ejoin WHERE $where1 AND $where2";
            $joins[] = "JOIN {user_enrolments} {$prefix}ue1 ON {$prefix}ue1.userid = {$prefix}u.id";
            $joins[] = "JOIN {enrol} {$prefix}e1 ON ({$prefix}e1.id = {$prefix}ue1.enrolid AND {$prefix}e1.courseid = :{$prefix}_e1_courseid)";
            $params["{$prefix}_e1_courseid"] = $coursecontext->instanceid;
            $wheres[] = "{$prefix}u.id NOT IN ($enrolselect)";
        }

        if ($onlyactive || $onlysuspended) {
            $now = round(time(), -2);             $params = array_merge($params, array($prefix.'enabled'=>ENROL_INSTANCE_ENABLED,
                                                 $prefix.'active'=>ENROL_USER_ACTIVE,
                                                 $prefix.'now1'=>$now, $prefix.'now2'=>$now));
        }
    }

    $joins = implode("\n", $joins);
    $wheres = "WHERE ".implode(" AND ", $wheres);

    $sql = "SELECT DISTINCT {$prefix}u.id
              FROM {user} {$prefix}u
            $joins
           $wheres";

    return array($sql, $params);
}


function get_enrolled_users(context $context, $withcapability = '', $groupid = 0, $userfields = 'u.*', $orderby = null,
        $limitfrom = 0, $limitnum = 0, $onlyactive = false) {
    global $DB;

    list($esql, $params) = get_enrolled_sql($context, $withcapability, $groupid, $onlyactive);
    $sql = "SELECT $userfields
              FROM {user} u
              JOIN ($esql) je ON je.id = u.id
             WHERE u.deleted = 0";

    if ($orderby) {
        $sql = "$sql ORDER BY $orderby";
    } else {
        list($sort, $sortparams) = users_order_by_sql('u');
        $sql = "$sql ORDER BY $sort";
        $params = array_merge($params, $sortparams);
    }

    return $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
}


function count_enrolled_users(context $context, $withcapability = '', $groupid = 0, $onlyactive = false) {
    global $DB;

    list($esql, $params) = get_enrolled_sql($context, $withcapability, $groupid, $onlyactive);
    $sql = "SELECT count(u.id)
              FROM {user} u
              JOIN ($esql) je ON je.id = u.id
             WHERE u.deleted = 0";

    return $DB->count_records_sql($sql, $params);
}


function load_capability_def($component) {
    $defpath = core_component::get_component_directory($component).'/db/access.php';

    $capabilities = array();
    if (file_exists($defpath)) {
        require($defpath);
        if (!empty(${$component.'_capabilities'})) {
                                    debugging('componentname_capabilities array is deprecated, please use $capabilities array only in access.php files');
            $capabilities = ${$component.'_capabilities'};
        }
    }

    return $capabilities;
}


function get_cached_capabilities($component = 'moodle') {
    global $DB;
    $caps = get_all_capabilities();
    $componentcaps = array();
    foreach ($caps as $cap) {
        if ($cap['component'] == $component) {
            $componentcaps[] = (object) $cap;
        }
    }
    return $componentcaps;
}


function get_default_capabilities($archetype) {
    global $DB;

    if (!$archetype) {
        return array();
    }

    $alldefs = array();
    $defaults = array();
    $components = array();
    $allcaps = get_all_capabilities();

    foreach ($allcaps as $cap) {
        if (!in_array($cap['component'], $components)) {
            $components[] = $cap['component'];
            $alldefs = array_merge($alldefs, load_capability_def($cap['component']));
        }
    }
    foreach($alldefs as $name=>$def) {
                if (isset($def['archetypes'])) {
            if (isset($def['archetypes'][$archetype])) {
                $defaults[$name] = $def['archetypes'][$archetype];
            }
                } else {
            if (isset($def['legacy'][$archetype])) {
                $defaults[$name] = $def['legacy'][$archetype];
            }
        }
    }

    return $defaults;
}


function get_default_role_archetype_allows($type, $archetype) {
    global $DB;

    if (empty($archetype)) {
        return array();
    }

    $roles = $DB->get_records('role');
    $archetypemap = array();
    foreach ($roles as $role) {
        if ($role->archetype) {
            $archetypemap[$role->archetype][$role->id] = $role->id;
        }
    }

    $defaults = array(
        'assign' => array(
            'manager'             => array('manager', 'coursecreator', 'departmentmanager', 'editingteacher', 'teacher', 'teacherassistant', 'student', 'auditor'),
            'coursecreator'       => array(),
            'modsetws'            => array('editingteacher', 'teacherassistant', 'student', 'auditor'),
            'departmentmanager'   => array('departmentassistant', 'editingteacher', 'teacherassistant', 'student', 'auditor'),
            'departmentassistant' => array('editingteacher', 'teacherassistant', 'student', 'auditor'),
            'editingteacher'      => array('teacher', 'teacherassistant', 'auditor'),
            'teacher'             => array('teacherassistant', 'auditor'),
            'teacherassistant'    => array('auditor'),
            'student'             => array(),
            'auditor'             => array(),
            'guest'               => array(),
            'user'                => array(),
            'frontpage'           => array(),
        ),
        'override' => array(
            'manager'             => array('manager', 'coursecreator', 'departmentmanager', 'editingteacher', 'teacher', 'teacherassistant', 'student', 'auditor', 'guest', 'user', 'frontpage'),
            'coursecreator'       => array(),
            'departmentmanager'   => array('departmentassistant', 'editingteacher', 'teacherassistant', 'student', 'auditor', 'guest'),
            'departmentassistant' => array('editingteacher', 'teacherassistant', 'student', 'auditor', 'guest'),
            'editingteacher'      => array('teacher', 'teacherassistant', 'student', 'auditor', 'guest'),
            'teacher'             => array(),
            'teacherassistant'    => array(),
            'student'             => array(),
            'auditor'             => array(),
            'guest'               => array(),
            'user'                => array(),
            'frontpage'           => array(),
        ),
        'switch' => array(
            'manager'             => array('departmentmanager', 'editingteacher', 'teacher', 'teacherassistant', 'student', 'auditor', 'guest'),
            'coursecreator'       => array(),
            'departmentmanager'   => array('departmentassistant', 'editingteacher', 'teacher', 'teacherassistant', 'student', 'auditor', 'guest'),
            'departmentassistant' => array('editingteacher', 'teacher', 'teacherassistant', 'student', 'auditor', 'guest'),
            'editingteacher'      => array('teacher', 'teacherassistant', 'student', 'auditor', 'guest'),
            'teacher'             => array('teacherassistant', 'student', 'auditor', 'guest'),
            'teacherassistant'    => array('student', 'auditor', 'guest'),
            'student'             => array(),
            'auditor'             => array(),
            'guest'               => array(),
            'user'                => array(),
            'frontpage'           => array(),
        ),
    );

    if (!isset($defaults[$type][$archetype])) {
        debugging("Unknown type '$type'' or archetype '$archetype''");
        return array();
    }

    $return = array();
    foreach ($defaults[$type][$archetype] as $at) {
        if (isset($archetypemap[$at])) {
            foreach ($archetypemap[$at] as $roleid) {
                $return[$roleid] = $roleid;
            }
        }
    }

    return $return;
}


function reset_role_capabilities($roleid) {
    global $DB;

    $role = $DB->get_record('role', array('id'=>$roleid), '*', MUST_EXIST);
    $defaultcaps = get_default_capabilities($role->archetype);

    $systemcontext = context_system::instance();

    $DB->delete_records('role_capabilities',
            array('roleid' => $roleid, 'contextid' => $systemcontext->id));

    foreach($defaultcaps as $cap=>$permission) {
        assign_capability($cap, $permission, $roleid, $systemcontext->id);
    }

        context_system::instance()->mark_dirty();
}


function update_capabilities($component = 'moodle') {
    global $DB, $OUTPUT;

    $storedcaps = array();

    $filecaps = load_capability_def($component);
    foreach($filecaps as $capname=>$unused) {
        if (!preg_match('|^[a-z]+/[a-z_0-9]+:[a-z_0-9]+$|', $capname)) {
            debugging("Coding problem: Invalid capability name '$capname', use 'clonepermissionsfrom' field for migration.");
        }
    }

            cache::make('core', 'capabilities')->delete('core_capabilities');

    $cachedcaps = get_cached_capabilities($component);
    if ($cachedcaps) {
        foreach ($cachedcaps as $cachedcap) {
            array_push($storedcaps, $cachedcap->name);
                        if (array_key_exists($cachedcap->name, $filecaps)) {
                if (!array_key_exists('riskbitmask', $filecaps[$cachedcap->name])) {
                    $filecaps[$cachedcap->name]['riskbitmask'] = 0;                 }
                if ($cachedcap->captype != $filecaps[$cachedcap->name]['captype']) {
                    $updatecap = new stdClass();
                    $updatecap->id = $cachedcap->id;
                    $updatecap->captype = $filecaps[$cachedcap->name]['captype'];
                    $DB->update_record('capabilities', $updatecap);
                }
                if ($cachedcap->riskbitmask != $filecaps[$cachedcap->name]['riskbitmask']) {
                    $updatecap = new stdClass();
                    $updatecap->id = $cachedcap->id;
                    $updatecap->riskbitmask = $filecaps[$cachedcap->name]['riskbitmask'];
                    $DB->update_record('capabilities', $updatecap);
                }

                if (!array_key_exists('contextlevel', $filecaps[$cachedcap->name])) {
                    $filecaps[$cachedcap->name]['contextlevel'] = 0;                 }
                if ($cachedcap->contextlevel != $filecaps[$cachedcap->name]['contextlevel']) {
                    $updatecap = new stdClass();
                    $updatecap->id = $cachedcap->id;
                    $updatecap->contextlevel = $filecaps[$cachedcap->name]['contextlevel'];
                    $DB->update_record('capabilities', $updatecap);
                }
            }
        }
    }

        cache::make('core', 'capabilities')->delete('core_capabilities');

        $newcaps = array();

    foreach ($filecaps as $filecap => $def) {
        if (!$storedcaps ||
                ($storedcaps && in_array($filecap, $storedcaps) === false)) {
            if (!array_key_exists('riskbitmask', $def)) {
                $def['riskbitmask'] = 0;             }
            $newcaps[$filecap] = $def;
        }
    }
        $existingcaps = $DB->get_records_menu('capabilities', array(), 'id', 'id, name');
    foreach ($newcaps as $capname => $capdef) {
        $capability = new stdClass();
        $capability->name         = $capname;
        $capability->captype      = $capdef['captype'];
        $capability->contextlevel = $capdef['contextlevel'];
        $capability->component    = $component;
        $capability->riskbitmask  = $capdef['riskbitmask'];

        $DB->insert_record('capabilities', $capability, false);

        if (isset($capdef['clonepermissionsfrom']) && in_array($capdef['clonepermissionsfrom'], $existingcaps)){
            if ($rolecapabilities = $DB->get_records('role_capabilities', array('capability'=>$capdef['clonepermissionsfrom']))){
                foreach ($rolecapabilities as $rolecapability){
                                        if (!assign_capability($capname, $rolecapability->permission,
                                            $rolecapability->roleid, $rolecapability->contextid, true)){
                         echo $OUTPUT->notification('Could not clone capabilities for '.$capname);
                    }
                }
            }
                } else if (isset($capdef['archetypes']) && is_array($capdef['archetypes'])) {
            assign_legacy_capabilities($capname, $capdef['archetypes']);
                } else if (isset($capdef['legacy']) && is_array($capdef['legacy'])) {
            assign_legacy_capabilities($capname, $capdef['legacy']);
        }
    }
                capabilities_cleanup($component, $filecaps);

        accesslib_clear_all_caches(false);

        cache::make('core', 'capabilities')->delete('core_capabilities');

    return true;
}


function capabilities_cleanup($component, $newcapdef = null) {
    global $DB;

    $removedcount = 0;

    if ($cachedcaps = get_cached_capabilities($component)) {
        foreach ($cachedcaps as $cachedcap) {
            if (empty($newcapdef) ||
                        array_key_exists($cachedcap->name, $newcapdef) === false) {

                                $DB->delete_records('capabilities', array('name'=>$cachedcap->name));
                $removedcount++;
                                if ($roles = get_roles_with_capability($cachedcap->name)) {
                    foreach($roles as $role) {
                        if (!unassign_capability($cachedcap->name, $role->id)) {
                            print_error('cannotunassigncap', 'error', '', (object)array('cap'=>$cachedcap->name, 'role'=>$role->name));
                        }
                    }
                }
            }         }
    }
    if ($removedcount) {
        cache::make('core', 'capabilities')->delete('core_capabilities');
    }
    return $removedcount;
}


function get_all_risks() {
    return array(
        'riskmanagetrust' => RISK_MANAGETRUST,
        'riskconfig'      => RISK_CONFIG,
        'riskxss'         => RISK_XSS,
        'riskpersonal'    => RISK_PERSONAL,
        'riskspam'        => RISK_SPAM,
        'riskdataloss'    => RISK_DATALOSS,
    );
}


function get_capability_docs_link($capability) {
    $url = get_docs_url('Capabilities/' . $capability->name);
    return '<a onclick="this.target=\'docspopup\'" href="' . $url . '">' . get_capability_string($capability->name) . '</a>';
}


function role_context_capabilities($roleid, context $context, $cap = '') {
    global $DB;

    $contexts = $context->get_parent_context_ids(true);
    $contexts = '('.implode(',', $contexts).')';

    $params = array($roleid);

    if ($cap) {
        $search = " AND rc.capability = ? ";
        $params[] = $cap;
    } else {
        $search = '';
    }

    $sql = "SELECT rc.*
              FROM {role_capabilities} rc, {context} c
             WHERE rc.contextid in $contexts
                   AND rc.roleid = ?
                   AND rc.contextid = c.id $search
          ORDER BY c.contextlevel DESC, rc.capability DESC";

    $capabilities = array();

    if ($records = $DB->get_records_sql($sql, $params)) {
                foreach ($records as $record) {
                        if (!isset($capabilities[$record->capability]) || $record->permission<-500) {
                $capabilities[$record->capability] = $record->permission;
            }
        }
    }
    return $capabilities;
}


function get_context_info_list(context $context) {
    $contextids = explode('/', ltrim($context->path, '/'));
    $contextpaths = array();
    $contextids2 = $contextids;
    while ($contextids2) {
        $contextpaths[] = '/' . implode('/', $contextids2);
        array_pop($contextids2);
    }
    return array($contextids, $contextpaths);
}


function is_inside_frontpage(context $context) {
    $frontpagecontext = context_course::instance(SITEID);
    return strpos($context->path . '/', $frontpagecontext->path . '/') === 0;
}


function get_capability_info($capabilityname) {
    global $ACCESSLIB_PRIVATE, $DB; 
    $caps = get_all_capabilities();

    if (!isset($caps[$capabilityname])) {
        return null;
    }

    return (object) $caps[$capabilityname];
}


function get_all_capabilities() {
    global $DB;
    $cache = cache::make('core', 'capabilities');
    if (!$allcaps = $cache->get('core_capabilities')) {
        $rs = $DB->get_recordset('capabilities');
        $allcaps = array();
        foreach ($rs as $capability) {
            $capability->riskbitmask = (int) $capability->riskbitmask;
            $allcaps[$capability->name] = (array) $capability;
        }
        $rs->close();
        $cache->set('core_capabilities', $allcaps);
    }
    return $allcaps;
}


function get_capability_string($capabilityname) {

        list($type, $name, $capname) = preg_split('|[/:]|', $capabilityname);

    if ($type === 'moodle') {
        $component = 'core_role';
    } else if ($type === 'quizreport') {
                $component = 'quiz_'.$name;
    } else {
        $component = $type.'_'.$name;
    }

    $stringname = $name.':'.$capname;

    if ($component === 'core_role' or get_string_manager()->string_exists($stringname, $component)) {
        return get_string($stringname, $component);
    }

    $dir = core_component::get_component_directory($component);
    if (!file_exists($dir)) {
                return $capabilityname.' ???';
    }

        return get_string($stringname, $component);
}


function get_component_string($component, $contextlevel) {

    if ($component === 'moodle' or $component === 'core') {
        switch ($contextlevel) {
                        case CONTEXT_SYSTEM:    return get_string('coresystem');
            case CONTEXT_USER:      return get_string('users');
            case CONTEXT_COURSECAT: return get_string('categories');
            case CONTEXT_COURSE:    return get_string('course');
            case CONTEXT_MODULE:    return get_string('activities');
            case CONTEXT_BLOCK:     return get_string('block');
            default:                print_error('unknowncontext');
        }
    }

    list($type, $name) = core_component::normalize_component($component);
    $dir = core_component::get_plugin_directory($type, $name);
    if (!file_exists($dir)) {
                return $component.' ???';
    }

    switch ($type) {
                case 'quiz':         return get_string($name.':componentname', $component);        case 'repository':   return get_string('repository', 'repository').': '.get_string('pluginname', $component);
        case 'gradeimport':  return get_string('gradeimport', 'grades').': '.get_string('pluginname', $component);
        case 'gradeexport':  return get_string('gradeexport', 'grades').': '.get_string('pluginname', $component);
        case 'gradereport':  return get_string('gradereport', 'grades').': '.get_string('pluginname', $component);
        case 'webservice':   return get_string('webservice', 'webservice').': '.get_string('pluginname', $component);
        case 'block':        return get_string('block').': '.get_string('pluginname', basename($component));
        case 'mod':
            if (get_string_manager()->string_exists('pluginname', $component)) {
                return get_string('activity').': '.get_string('pluginname', $component);
            } else {
                return get_string('activity').': '.get_string('modulename', $component);
            }
        default: return get_string('pluginname', $component);
    }
}


function get_profile_roles(context $context) {
    global $CFG, $DB;

    if (empty($CFG->profileroles)) {
        return array();
    }

    list($rallowed, $params) = $DB->get_in_or_equal(explode(',', $CFG->profileroles), SQL_PARAMS_NAMED, 'a');
    list($contextlist, $cparams) = $DB->get_in_or_equal($context->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'p');
    $params = array_merge($params, $cparams);

    if ($coursecontext = $context->get_course_context(false)) {
        $params['coursecontext'] = $coursecontext->id;
    } else {
        $params['coursecontext'] = 0;
    }

    $sql = "SELECT DISTINCT r.id, r.name, r.shortname, r.sortorder, rn.name AS coursealias
              FROM {role_assignments} ra, {role} r
         LEFT JOIN {role_names} rn ON (rn.contextid = :coursecontext AND rn.roleid = r.id)
             WHERE r.id = ra.roleid
                   AND ra.contextid $contextlist
                   AND r.id $rallowed
          ORDER BY r.sortorder ASC";

    return $DB->get_records_sql($sql, $params);
}


function get_roles_used_in_context(context $context) {
    global $DB;

    list($contextlist, $params) = $DB->get_in_or_equal($context->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'cl');

    if ($coursecontext = $context->get_course_context(false)) {
        $params['coursecontext'] = $coursecontext->id;
    } else {
        $params['coursecontext'] = 0;
    }

    $sql = "SELECT DISTINCT r.id, r.name, r.shortname, r.sortorder, rn.name AS coursealias
              FROM {role_assignments} ra, {role} r
         LEFT JOIN {role_names} rn ON (rn.contextid = :coursecontext AND rn.roleid = r.id)
             WHERE r.id = ra.roleid
                   AND ra.contextid $contextlist
          ORDER BY r.sortorder ASC";

    return $DB->get_records_sql($sql, $params);
}


function get_user_roles_in_course($userid, $courseid) {
    global $CFG, $DB;

    if (empty($CFG->profileroles)) {
        return '';
    }

    if ($courseid == SITEID) {
        $context = context_system::instance();
    } else {
        $context = context_course::instance($courseid);
    }

    list($rallowed, $params) = $DB->get_in_or_equal(explode(',', $CFG->profileroles), SQL_PARAMS_NAMED, 'a');
    list($contextlist, $cparams) = $DB->get_in_or_equal($context->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'p');
    $params = array_merge($params, $cparams);

    if ($coursecontext = $context->get_course_context(false)) {
        $params['coursecontext'] = $coursecontext->id;
    } else {
        $params['coursecontext'] = 0;
    }

    $sql = "SELECT DISTINCT r.id, r.name, r.shortname, r.sortorder, rn.name AS coursealias
              FROM {role_assignments} ra, {role} r
         LEFT JOIN {role_names} rn ON (rn.contextid = :coursecontext AND rn.roleid = r.id)
             WHERE r.id = ra.roleid
                   AND ra.contextid $contextlist
                   AND r.id $rallowed
                   AND ra.userid = :userid
          ORDER BY r.sortorder ASC";
    $params['userid'] = $userid;

    $rolestring = '';

    if ($roles = $DB->get_records_sql($sql, $params)) {
        $rolenames = role_fix_names($roles, $context, ROLENAME_ALIAS, true);   
        foreach ($rolenames as $roleid => $rolename) {
            $rolenames[$roleid] = '<a href="'.$CFG->wwwroot.'/user/index.php?contextid='.$context->id.'&amp;roleid='.$roleid.'">'.$rolename.'</a>';
        }
        $rolestring = implode(',', $rolenames);
    }

    return $rolestring;
}


function user_can_assign(context $context, $targetroleid) {
    global $DB;

        if (is_siteadmin()) {
        return true;
    }

            if (!has_capability('moodle/role:assign', $context)) {
        return false;
    }
        if ($userroles = get_user_roles($context)) {
        foreach ($userroles as $userrole) {
                        if ($DB->get_record('role_allow_assign', array('roleid'=>$userrole->roleid, 'allowassign'=>$targetroleid))) {
                return true;
            }
        }
    }

    return false;
}


function get_all_roles(context $context = null) {
    global $DB;

    if (!$context or !$coursecontext = $context->get_course_context(false)) {
        $coursecontext = null;
    }

    if ($coursecontext) {
        $sql = "SELECT r.*, rn.name AS coursealias
                  FROM {role} r
             LEFT JOIN {role_names} rn ON (rn.contextid = :coursecontext AND rn.roleid = r.id)
              ORDER BY r.sortorder ASC";
        return $DB->get_records_sql($sql, array('coursecontext'=>$coursecontext->id));

    } else {
        return $DB->get_records('role', array(), 'sortorder ASC');
    }
}


function get_archetype_roles($archetype) {
    global $DB;
    return $DB->get_records('role', array('archetype'=>$archetype), 'sortorder ASC');
}


function get_archetype_role($archetype) {
    global $DB;
    return $DB->get_record('role', array('archetype'=>$archetype));
}


function get_user_roles(context $context, $userid = 0, $checkparentcontexts = true, $order = 'c.contextlevel DESC, r.sortorder ASC') {
    global $USER, $DB;

    if (empty($userid)) {
        if (empty($USER->id)) {
            return array();
        }
        $userid = $USER->id;
    }

    if ($checkparentcontexts) {
        $contextids = $context->get_parent_context_ids();
    } else {
        $contextids = array();
    }
    $contextids[] = $context->id;

    list($contextids, $params) = $DB->get_in_or_equal($contextids, SQL_PARAMS_QM);

    array_unshift($params, $userid);

    $sql = "SELECT ra.*, r.name, r.shortname, r.archetype
              FROM {role_assignments} ra, {role} r, {context} c
             WHERE ra.userid = ?
                   AND ra.roleid = r.id
                   AND ra.contextid = c.id
                   AND ra.contextid $contextids
          ORDER BY $order";

    return $DB->get_records_sql($sql ,$params);
}


function get_user_roles_with_special(context $context, $userid = 0) {
    global $CFG, $USER;

    if (empty($userid)) {
        if (empty($USER->id)) {
            return array();
        }
        $userid = $USER->id;
    }

    $ras = get_user_roles($context, $userid);

        $defaultfrontpageroleid = isset($CFG->defaultfrontpageroleid) ? $CFG->defaultfrontpageroleid : 0;
    $isfrontpage = ($context->contextlevel == CONTEXT_COURSE && $context->instanceid == SITEID) ||
            is_inside_frontpage($context);
    if ($defaultfrontpageroleid && $isfrontpage) {
        $frontpagecontext = context_course::instance(SITEID);
        $ra = new stdClass();
        $ra->userid = $userid;
        $ra->contextid = $frontpagecontext->id;
        $ra->roleid = $defaultfrontpageroleid;
        $ras[] = $ra;
    }

        $defaultuserroleid      = isset($CFG->defaultuserroleid) ? $CFG->defaultuserroleid : 0;
    if ($defaultuserroleid && !isguestuser($userid)) {
        $systemcontext = context_system::instance();
        $ra = new stdClass();
        $ra->userid = $userid;
        $ra->contextid = $systemcontext->id;
        $ra->roleid = $defaultuserroleid;
        $ras[] = $ra;
    }

    return $ras;
}


function allow_override($sroleid, $troleid) {
    global $DB;

    $record = new stdClass();
    $record->roleid        = $sroleid;
    $record->allowoverride = $troleid;
    $DB->insert_record('role_allow_override', $record);
}


function allow_assign($fromroleid, $targetroleid) {
    global $DB;
    $record = new stdClass();
    $record->roleid      = $fromroleid;
    $record->allowassign = $targetroleid;
    $DB->insert_record('role_allow_assign', $record);
}


function allow_switch($fromroleid, $targetroleid) {
    global $DB;

    $record = new stdClass();
    $record->roleid      = $fromroleid;
    $record->allowswitch = $targetroleid;
    $DB->insert_record('role_allow_switch', $record);
}


function get_assignable_roles(context $context, $rolenamedisplay = ROLENAME_ALIAS, $withusercounts = false, $user = null) {
    global $USER, $DB;

        if ($user === null) {
        $userid = isset($USER->id) ? $USER->id : 0;
    } else {
        $userid = is_object($user) ? $user->id : $user;
    }

    if (!has_capability('moodle/role:assign', $context, $userid)) {
        if ($withusercounts) {
            return array(array(), array(), array());
        } else {
            return array();
        }
    }

    $params = array();
    $extrafields = '';

    if ($withusercounts) {
        $extrafields = ', (SELECT count(u.id)
                             FROM {role_assignments} cra JOIN {user} u ON cra.userid = u.id
                            WHERE cra.roleid = r.id AND cra.contextid = :conid AND u.deleted = 0
                          ) AS usercount';
        $params['conid'] = $context->id;
    }

    if (is_siteadmin($userid)) {
                $assignrestriction = "";
    } else {
        $parents = $context->get_parent_context_ids(true);
        $contexts = implode(',' , $parents);
        $assignrestriction = "JOIN (SELECT DISTINCT raa.allowassign AS id
                                      FROM {role_allow_assign} raa
                                      JOIN {role_assignments} ra ON ra.roleid = raa.roleid
                                     WHERE ra.userid = :userid AND ra.contextid IN ($contexts)
                                   ) ar ON ar.id = r.id";
        $params['userid'] = $userid;
    }
    $params['contextlevel'] = $context->contextlevel;

    if ($coursecontext = $context->get_course_context(false)) {
        $params['coursecontext'] = $coursecontext->id;
    } else {
        $params['coursecontext'] = 0;         $coursecontext = null;
    }
    $sql = "SELECT r.id, r.name, r.shortname, rn.name AS coursealias $extrafields
              FROM {role} r
              $assignrestriction
              JOIN {role_context_levels} rcl ON (rcl.contextlevel = :contextlevel AND r.id = rcl.roleid)
         LEFT JOIN {role_names} rn ON (rn.contextid = :coursecontext AND rn.roleid = r.id)
          ORDER BY r.sortorder ASC";
    $roles = $DB->get_records_sql($sql, $params);

    $rolenames = role_fix_names($roles, $coursecontext, $rolenamedisplay, true);

    if (!$withusercounts) {
        return $rolenames;
    }

    $rolecounts = array();
    $nameswithcounts = array();
    foreach ($roles as $role) {
        $nameswithcounts[$role->id] = $rolenames[$role->id] . ' (' . $roles[$role->id]->usercount . ')';
        $rolecounts[$role->id] = $roles[$role->id]->usercount;
    }
    return array($rolenames, $rolecounts, $nameswithcounts);
}


function get_switchable_roles(context $context) {
    global $USER, $DB;

    $params = array();
    $extrajoins = '';
    $extrawhere = '';
    if (!is_siteadmin()) {
                                $parents = $context->get_parent_context_ids(true);
        $contexts = implode(',' , $parents);

        $extrajoins = "JOIN {role_allow_switch} ras ON ras.allowswitch = rc.roleid
        JOIN {role_assignments} ra ON ra.roleid = ras.roleid";
        $extrawhere = "WHERE ra.userid = :userid AND ra.contextid IN ($contexts)";
        $params['userid'] = $USER->id;
    }

    if ($coursecontext = $context->get_course_context(false)) {
        $params['coursecontext'] = $coursecontext->id;
    } else {
        $params['coursecontext'] = 0;         $coursecontext = null;
    }

    $query = "
        SELECT r.id, r.name, r.shortname, rn.name AS coursealias
          FROM (SELECT DISTINCT rc.roleid
                  FROM {role_capabilities} rc
                  $extrajoins
                  $extrawhere) idlist
          JOIN {role} r ON r.id = idlist.roleid
     LEFT JOIN {role_names} rn ON (rn.contextid = :coursecontext AND rn.roleid = r.id)
      ORDER BY r.sortorder";
    $roles = $DB->get_records_sql($query, $params);

    return role_fix_names($roles, $context, ROLENAME_ALIAS, true);
}


function get_overridable_roles(context $context, $rolenamedisplay = ROLENAME_ALIAS, $withcounts = false) {
    global $USER, $DB;

    if (!has_any_capability(array('moodle/role:safeoverride', 'moodle/role:override'), $context)) {
        if ($withcounts) {
            return array(array(), array(), array());
        } else {
            return array();
        }
    }

    $parents = $context->get_parent_context_ids(true);
    $contexts = implode(',' , $parents);

    $params = array();
    $extrafields = '';

    $params['userid'] = $USER->id;
    if ($withcounts) {
        $extrafields = ', (SELECT COUNT(rc.id) FROM {role_capabilities} rc
                WHERE rc.roleid = ro.id AND rc.contextid = :conid) AS overridecount';
        $params['conid'] = $context->id;
    }

    if ($coursecontext = $context->get_course_context(false)) {
        $params['coursecontext'] = $coursecontext->id;
    } else {
        $params['coursecontext'] = 0;         $coursecontext = null;
    }

    if (is_siteadmin()) {
                $roles = $DB->get_records_sql("
            SELECT ro.id, ro.name, ro.shortname, rn.name AS coursealias $extrafields
              FROM {role} ro
         LEFT JOIN {role_names} rn ON (rn.contextid = :coursecontext AND rn.roleid = ro.id)
          ORDER BY ro.sortorder ASC", $params);

    } else {
        $roles = $DB->get_records_sql("
            SELECT ro.id, ro.name, ro.shortname, rn.name AS coursealias $extrafields
              FROM {role} ro
              JOIN (SELECT DISTINCT r.id
                      FROM {role} r
                      JOIN {role_allow_override} rao ON r.id = rao.allowoverride
                      JOIN {role_assignments} ra ON rao.roleid = ra.roleid
                     WHERE ra.userid = :userid AND ra.contextid IN ($contexts)
                   ) inline_view ON ro.id = inline_view.id
         LEFT JOIN {role_names} rn ON (rn.contextid = :coursecontext AND rn.roleid = ro.id)
          ORDER BY ro.sortorder ASC", $params);
    }

    $rolenames = role_fix_names($roles, $context, $rolenamedisplay, true);

    if (!$withcounts) {
        return $rolenames;
    }

    $rolecounts = array();
    $nameswithcounts = array();
    foreach ($roles as $role) {
        $nameswithcounts[$role->id] = $rolenames[$role->id] . ' (' . $roles[$role->id]->overridecount . ')';
        $rolecounts[$role->id] = $roles[$role->id]->overridecount;
    }
    return array($rolenames, $rolecounts, $nameswithcounts);
}


function get_default_enrol_roles(context $context, $addroleid = null) {
    global $DB;

    $params = array('contextlevel'=>CONTEXT_COURSE);

    if ($coursecontext = $context->get_course_context(false)) {
        $params['coursecontext'] = $coursecontext->id;
    } else {
        $params['coursecontext'] = 0;         $coursecontext = null;
    }

    if ($addroleid) {
        $addrole = "OR r.id = :addroleid";
        $params['addroleid'] = $addroleid;
    } else {
        $addrole = "";
    }

    $sql = "SELECT r.id, r.name, r.shortname, rn.name AS coursealias
              FROM {role} r
         LEFT JOIN {role_context_levels} rcl ON (rcl.roleid = r.id AND rcl.contextlevel = :contextlevel)
         LEFT JOIN {role_names} rn ON (rn.contextid = :coursecontext AND rn.roleid = r.id)
             WHERE rcl.id IS NOT NULL $addrole
          ORDER BY sortorder DESC";

    $roles = $DB->get_records_sql($sql, $params);

    return role_fix_names($roles, $context, ROLENAME_BOTH, true);
}


function get_default_enrol_role_shortname(context $context, $addshortname = null) {
    global $DB;
    $params = array('contextlevel'=>CONTEXT_COURSE);

    if ($coursecontext = $context->get_course_context(false)) {
        $params['coursecontext'] = $coursecontext->id;
    } else {
        $params['coursecontext'] = 0;         $coursecontext = null;
    }

    if ($addshortname) {
        $addrole = "AND r.shortname = :addshortname";
        $params['addshortname'] = $addshortname;
    } else {
        $addrole = "";
    }

    $sql = "SELECT r.id, r.name, r.shortname, rn.name AS coursealias
              FROM {role} r
         LEFT JOIN {role_context_levels} rcl ON (rcl.roleid = r.id AND rcl.contextlevel = :contextlevel)
         LEFT JOIN {role_names} rn ON (rn.contextid = :coursecontext AND rn.roleid = r.id)
             WHERE rcl.id IS NOT NULL $addrole ";

    $roles = $DB->get_records_sql($sql, $params);

    return role_fix_names($roles, $context, ROLENAME_BOTH, true);
}


function get_role_contextlevels($roleid) {
    global $DB;
    return $DB->get_records_menu('role_context_levels', array('roleid' => $roleid),
            'contextlevel', 'id,contextlevel');
}


function get_roles_for_contextlevels($contextlevel) {
    global $DB;
    return $DB->get_records_menu('role_context_levels', array('contextlevel' => $contextlevel), '', 'id,roleid');
}


function get_default_contextlevels($rolearchetype) {
    static $defaults = array(
        'manager'           => array(CONTEXT_SYSTEM, CONTEXT_COURSECAT, CONTEXT_COURSE),
        'coursecreator'     => array(CONTEXT_SYSTEM, CONTEXT_COURSECAT),
        'modsetws'          => array(CONTEXT_SYSTEM, CONTEXT_COURSECAT, CONTEXT_COURSE),
        'departmentmanager' => array(CONTEXT_COURSECAT, CONTEXT_COURSE),
        'departmentassistant' => array(CONTEXT_COURSECAT, CONTEXT_COURSE),
        'editingteacher'    => array(CONTEXT_COURSE, CONTEXT_MODULE),
        'teacherassistant'  => array(CONTEXT_COURSE, CONTEXT_MODULE),
        'student'           => array(CONTEXT_COURSE, CONTEXT_MODULE),
        'auditor'           => array(CONTEXT_COURSE, CONTEXT_MODULE),
        'guest'             => array(),
        'user'              => array(),
        'frontpage'         => array());

    if (isset($defaults[$rolearchetype])) {
        return $defaults[$rolearchetype];
    } else {
        return array();
    }
}


function set_role_contextlevels($roleid, array $contextlevels) {
    global $DB;
    $DB->delete_records('role_context_levels', array('roleid' => $roleid));
    $rcl = new stdClass();
    $rcl->roleid = $roleid;
    $contextlevels = array_unique($contextlevels);
    foreach ($contextlevels as $level) {
        $rcl->contextlevel = $level;
        $DB->insert_record('role_context_levels', $rcl, false, true);
    }
}


function get_users_by_capability(context $context, $capability, $fields = '', $sort = '', $limitfrom = '', $limitnum = '',
                                 $groups = '', $exceptions = '', $doanything_ignored = null, $view_ignored = null, $useviewallgroups = false) {
    global $CFG, $DB;

    $defaultuserroleid      = isset($CFG->defaultuserroleid) ? $CFG->defaultuserroleid : 0;
    $defaultfrontpageroleid = isset($CFG->defaultfrontpageroleid) ? $CFG->defaultfrontpageroleid : 0;

    $ctxids = trim($context->path, '/');
    $ctxids = str_replace('/', ',', $ctxids);

        $iscoursepage = false;     $isfrontpage = false;
    if ($context->contextlevel == CONTEXT_COURSE) {
        if ($context->instanceid == SITEID) {
            $isfrontpage = true;
        } else {
            $iscoursepage = true;
        }
    }
    $isfrontpage = ($isfrontpage || is_inside_frontpage($context));

    $caps = (array)$capability;

        list($contextids, $paths) = get_context_info_list($context);

        $defs = array();
    list($incontexts, $params) = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED, 'con');
    list($incaps, $params2) = $DB->get_in_or_equal($caps, SQL_PARAMS_NAMED, 'cap');
    $params = array_merge($params, $params2);
    $sql = "SELECT rc.id, rc.roleid, rc.permission, rc.capability, ctx.path
              FROM {role_capabilities} rc
              JOIN {context} ctx on rc.contextid = ctx.id
             WHERE rc.contextid $incontexts AND rc.capability $incaps";

    $rcs = $DB->get_records_sql($sql, $params);
    foreach ($rcs as $rc) {
        $defs[$rc->capability][$rc->path][$rc->roleid] = $rc->permission;
    }

            $access = array();
    foreach ($caps as $cap) {
        foreach ($paths as $path) {
            if (empty($defs[$cap][$path])) {
                continue;
            }
            foreach($defs[$cap][$path] as $roleid => $perm) {
                if ($perm == CAP_PROHIBIT) {
                    $access[$cap][$roleid] = CAP_PROHIBIT;
                    continue;
                }
                if (!isset($access[$cap][$roleid])) {
                    $access[$cap][$roleid] = (int)$perm;
                }
            }
        }
    }

        $needed = array();     $prohibited = array();     foreach ($caps as $cap) {
        if (empty($access[$cap])) {
            continue;
        }
        foreach ($access[$cap] as $roleid => $perm) {
            if ($perm == CAP_PROHIBIT) {
                unset($needed[$cap][$roleid]);
                $prohibited[$cap][$roleid] = true;
            } else if ($perm == CAP_ALLOW and empty($prohibited[$cap][$roleid])) {
                $needed[$cap][$roleid] = true;
            }
        }
        if (empty($needed[$cap]) or !empty($prohibited[$cap][$defaultuserroleid])) {
                        unset($needed[$cap]);
            unset($prohibited[$cap]);
        } else if ($isfrontpage and !empty($prohibited[$cap][$defaultfrontpageroleid])) {
                        unset($needed[$cap]);
            unset($prohibited[$cap]);
        }
        if (empty($prohibited[$cap])) {
            unset($prohibited[$cap]);
        }
    }

    if (empty($needed)) {
                return array();
    }

    if (empty($prohibited)) {
                $n = array();
        foreach ($needed as $cap) {
            foreach ($cap as $roleid=>$unused) {
                $n[$roleid] = true;
            }
        }
        $needed = array('any'=>$n);
        unset($n);
    }

        if (empty($fields)) {
        if ($iscoursepage) {
            $fields = 'u.*, ul.timeaccess AS lastaccess';
        } else {
            $fields = 'u.*';
        }
    } else {
        if ($CFG->debugdeveloper && strpos($fields, 'u.*') === false && strpos($fields, 'u.id') === false) {
            debugging('u.id must be included in the list of fields passed to get_users_by_capability().', DEBUG_DEVELOPER);
        }
    }

        if (empty($sort)) {         if ($iscoursepage) {
            $sort = 'ul.timeaccess';
        } else {
            $sort = 'u.lastaccess';
        }
    }

        $wherecond = array();
    $params    = array();
    $joins     = array();

        if ((strpos($sort, 'ul.timeaccess') === false) and (strpos($fields, 'ul.timeaccess') === false)) {
             } else {
        if ($iscoursepage) {
            $joins[] = "LEFT OUTER JOIN {user_lastaccess} ul ON (ul.userid = u.id AND ul.courseid = {$context->instanceid})";
        } else {
            throw new coding_exception('Invalid sort in get_users_by_capability(), ul.timeaccess allowed only for course contexts.');
        }
    }

        $wherecond[] = "u.deleted = 0 AND u.id <> :guestid";
    $params['guestid'] = $CFG->siteguest;

        if ($groups) {
        $groups = (array)$groups;
        list($grouptest, $grpparams) = $DB->get_in_or_equal($groups, SQL_PARAMS_NAMED, 'grp');
        $grouptest = "u.id IN (SELECT userid FROM {groups_members} gm WHERE gm.groupid $grouptest)";
        $params = array_merge($params, $grpparams);

        if ($useviewallgroups) {
            $viewallgroupsusers = get_users_by_capability($context, 'moodle/site:accessallgroups', 'u.id, u.id', '', '', '', '', $exceptions);
            if (!empty($viewallgroupsusers)) {
                $wherecond[] =  "($grouptest OR u.id IN (" . implode(',', array_keys($viewallgroupsusers)) . '))';
            } else {
                $wherecond[] =  "($grouptest)";
            }
        } else {
            $wherecond[] =  "($grouptest)";
        }
    }

        if (!empty($exceptions)) {
        $exceptions = (array)$exceptions;
        list($exsql, $exparams) = $DB->get_in_or_equal($exceptions, SQL_PARAMS_NAMED, 'exc', false);
        $params = array_merge($params, $exparams);
        $wherecond[] = "u.id $exsql";
    }

        if (!empty($needed['any'])) {
                if (!empty($needed['any'][$defaultuserroleid]) or ($isfrontpage and !empty($needed['any'][$defaultfrontpageroleid]))) {
                    } else {
            $joins[] = "JOIN (SELECT DISTINCT userid
                                FROM {role_assignments}
                               WHERE contextid IN ($ctxids)
                                     AND roleid IN (".implode(',', array_keys($needed['any'])) .")
                             ) ra ON ra.userid = u.id";
        }
    } else {
        $unions = array();
        $everybody = false;
        foreach ($needed as $cap=>$unused) {
            if (empty($prohibited[$cap])) {
                if (!empty($needed[$cap][$defaultuserroleid]) or ($isfrontpage and !empty($needed[$cap][$defaultfrontpageroleid]))) {
                    $everybody = true;
                    break;
                } else {
                    $unions[] = "SELECT userid
                                   FROM {role_assignments}
                                  WHERE contextid IN ($ctxids)
                                        AND roleid IN (".implode(',', array_keys($needed[$cap])) .")";
                }
            } else {
                if (!empty($prohibited[$cap][$defaultuserroleid]) or ($isfrontpage and !empty($prohibited[$cap][$defaultfrontpageroleid]))) {
                                        continue;

                } else if (!empty($needed[$cap][$defaultuserroleid]) or ($isfrontpage and !empty($needed[$cap][$defaultfrontpageroleid]))) {
                                        $unions[] = "SELECT id AS userid
                                   FROM {user}
                                  WHERE id NOT IN (SELECT userid
                                                     FROM {role_assignments}
                                                    WHERE contextid IN ($ctxids)
                                                          AND roleid IN (".implode(',', array_keys($prohibited[$cap])) ."))";

                } else {
                    $unions[] = "SELECT userid
                                   FROM {role_assignments}
                                  WHERE contextid IN ($ctxids)
                                        AND roleid IN (".implode(',', array_keys($needed[$cap])) .")
                                        AND roleid NOT IN (".implode(',', array_keys($prohibited[$cap])) .")";
                }
            }
        }
        if (!$everybody) {
            if ($unions) {
                $joins[] = "JOIN (SELECT DISTINCT userid FROM ( ".implode(' UNION ', $unions)." ) us) ra ON ra.userid = u.id";
            } else {
                                $wherecond[] = "1 = 2";
            }
        }
    }

        $where = implode(' AND ', $wherecond);
    if ($where !== '') {
        $where = 'WHERE ' . $where;
    }
    $joins = implode("\n", $joins);

        $sql = "SELECT $fields
              FROM {user} u
            $joins
            $where
          ORDER BY $sort";

    return $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
}


function sort_by_roleassignment_authority($users, context $context, $roles = array(), $sortpolicy = 'locality') {
    global $DB;

    $userswhere = ' ra.userid IN (' . implode(',',array_keys($users)) . ')';
    $contextwhere = 'AND ra.contextid IN ('.str_replace('/', ',',substr($context->path, 1)).')';
    if (empty($roles)) {
        $roleswhere = '';
    } else {
        $roleswhere = ' AND ra.roleid IN ('.implode(',',$roles).')';
    }

    $sql = "SELECT ra.userid
              FROM {role_assignments} ra
              JOIN {role} r
                   ON ra.roleid=r.id
              JOIN {context} ctx
                   ON ra.contextid=ctx.id
             WHERE $userswhere
                   $contextwhere
                   $roleswhere";

            $orderby = 'ORDER BY '
                    .'ctx.depth DESC, '  
                    .'r.sortorder ASC, ' 
                    .'ra.id';            
    if ($sortpolicy === 'sortorder') {
        $orderby = 'ORDER BY '
                        .'r.sortorder ASC, ' 
                        .'ra.id';            
    }

    $sortedids = $DB->get_fieldset_sql($sql . $orderby);
    $sortedusers = array();
    $seen = array();

    foreach ($sortedids as $id) {
                if (isset($seen[$id])) {
            continue;
        }
        $seen[$id] = true;

                $sortedusers[$id] = $users[$id];
    }
    return $sortedusers;
}


function get_role_users($roleid, context $context, $parent = false, $fields = '',
        $sort = null, $all = true, $group = '',
        $limitfrom = '', $limitnum = '', $extrawheretest = '', $whereorsortparams = array()) {
    global $DB;

    if (empty($fields)) {
        $allnames = get_all_user_name_fields(true, 'u');
        $fields = 'u.id, u.confirmed, u.username, '. $allnames . ', ' .
                  'u.maildisplay, u.mailformat, u.maildigest, u.email, u.emailstop, u.city, '.
                  'u.country, u.picture, u.idnumber, u.department, u.institution, '.
                  'u.lang, u.timezone, u.lastaccess, u.mnethostid, r.name AS rolename, r.sortorder, '.
                  'r.shortname AS roleshortname, rn.name AS rolecoursealias';
    }

        if ((empty($roleid) || is_array($roleid)) && strpos($fields, 'ra.id') !== 0) {
        debugging('get_role_users() without specifying one single roleid needs to be called prefixing ' .
            'role assignments id (ra.id) as unique field, you can use $fields param for it.');

        if (!empty($roleid)) {
                        $users = array();
            foreach ($roleid as $id) {
                                $users = $users + get_role_users($id, $context, $parent, $fields, $sort, $all, $group,
                    $limitfrom, $limitnum, $extrawheretest, $whereorsortparams);
            }
            return $users;
        }
    }

    $parentcontexts = '';
    if ($parent) {
        $parentcontexts = substr($context->path, 1);         $parentcontexts = str_replace('/', ',', $parentcontexts);
        if ($parentcontexts !== '') {
            $parentcontexts = ' OR ra.contextid IN ('.$parentcontexts.' )';
        }
    }

    if ($roleid) {
        list($rids, $params) = $DB->get_in_or_equal($roleid, SQL_PARAMS_NAMED, 'r');
        $roleselect = "AND ra.roleid $rids";
    } else {
        $params = array();
        $roleselect = '';
    }

    if ($coursecontext = $context->get_course_context(false)) {
        $params['coursecontext'] = $coursecontext->id;
    } else {
        $params['coursecontext'] = 0;
    }

    if ($group) {
        $groupjoin   = "JOIN {groups_members} gm ON gm.userid = u.id";
        $groupselect = " AND gm.groupid = :groupid ";
        $params['groupid'] = $group;
    } else {
        $groupjoin   = '';
        $groupselect = '';
    }

    $params['contextid'] = $context->id;

    if ($extrawheretest) {
        $extrawheretest = ' AND ' . $extrawheretest;
    }

    if ($whereorsortparams) {
        $params = array_merge($params, $whereorsortparams);
    }

    if (!$sort) {
        list($sort, $sortparams) = users_order_by_sql('u');
        $params = array_merge($params, $sortparams);
    }

        $sortarray = preg_split('/,\s*/', $sort);
    $fieldsarray = preg_split('/,\s*/', $fields);

        $fieldnames = array();
    foreach ($fieldsarray as $key => $field) {
        list($fieldnames[$key]) = explode(' ', $field);
    }

    $addedfields = array();
    foreach ($sortarray as $sortfield) {
                list($sortfield) = explode(' ', $sortfield);
        list($tableprefix) = explode('.', $sortfield);
        $fieldpresent = false;
        foreach ($fieldnames as $fieldname) {
            if ($fieldname === $sortfield || $fieldname === $tableprefix.'.*') {
                $fieldpresent = true;
                break;
            }
        }

        if (!$fieldpresent) {
            $fieldsarray[] = $sortfield;
            $addedfields[] = $sortfield;
        }
    }

    $fields = implode(', ', $fieldsarray);
    if (!empty($addedfields)) {
        $addedfields = implode(', ', $addedfields);
        debugging('get_role_users() adding '.$addedfields.' to the query result because they were required by $sort but missing in $fields');
    }

    if ($all === null) {
                $all = true;
    }
    if (!$all and $coursecontext) {
                $ejoin = "JOIN {user_enrolments} ue ON ue.userid = u.id
                  JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = :ecourseid)";
        $params['ecourseid'] = $coursecontext->instanceid;
    } else {
        $ejoin = "";
    }

    $sql = "SELECT DISTINCT $fields, ra.roleid
              FROM {role_assignments} ra
              JOIN {user} u ON u.id = ra.userid
              JOIN {role} r ON ra.roleid = r.id
            $ejoin
         LEFT JOIN {role_names} rn ON (rn.contextid = :coursecontext AND rn.roleid = r.id)
        $groupjoin
             WHERE (ra.contextid = :contextid $parentcontexts)
                   $roleselect
                   $groupselect
                   $extrawheretest
          ORDER BY $sort";                  
    return $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
}


function count_role_users($roleid, context $context, $parent = false) {
    global $DB;

    if ($parent) {
        if ($contexts = $context->get_parent_context_ids()) {
            $parentcontexts = ' OR r.contextid IN ('.implode(',', $contexts).')';
        } else {
            $parentcontexts = '';
        }
    } else {
        $parentcontexts = '';
    }

    if ($roleid) {
        list($rids, $params) = $DB->get_in_or_equal($roleid, SQL_PARAMS_QM);
        $roleselect = "AND r.roleid $rids";
    } else {
        $params = array();
        $roleselect = '';
    }

    array_unshift($params, $context->id);

    $sql = "SELECT COUNT(DISTINCT u.id)
              FROM {role_assignments} r
              JOIN {user} u ON u.id = r.userid
             WHERE (r.contextid = ? $parentcontexts)
                   $roleselect
                   AND u.deleted = 0";

    return $DB->count_records_sql($sql, $params);
}


function get_user_capability_course($capability, $userid = null, $doanything = true, $fieldsexceptid = '', $orderby = '') {
    global $DB;

        $fieldlist = '';
    if ($fieldsexceptid) {
        $fields = explode(',', $fieldsexceptid);
        foreach($fields as $field) {
            $fieldlist .= ',c.'.$field;
        }
    }
    if ($orderby) {
        $fields = explode(',', $orderby);
        $orderby = '';
        foreach($fields as $field) {
            if ($orderby) {
                $orderby .= ',';
            }
            $orderby .= 'c.'.$field;
        }
        $orderby = 'ORDER BY '.$orderby;
    }

            
    $contextpreload = context_helper::get_preload_record_columns_sql('x');

    $courses = array();
    $rs = $DB->get_recordset_sql("SELECT c.id $fieldlist, $contextpreload
                                    FROM {course} c
                                    JOIN {context} x ON (c.id=x.instanceid AND x.contextlevel=".CONTEXT_COURSE.")
                                $orderby");
        foreach ($rs as $course) {
        context_helper::preload_from_record($course);
        $context = context_course::instance($course->id);
        if (has_capability($capability, $context, $userid, $doanything)) {
                                    $courses[] = $course;
        }
    }
    $rs->close();
    return empty($courses) ? false : $courses;
}


function get_roles_on_exact_context(context $context) {
    global $DB;

    return $DB->get_records_sql("SELECT r.*
                                   FROM {role_assignments} ra, {role} r
                                  WHERE ra.roleid = r.id AND ra.contextid = ?",
                                array($context->id));
}


function role_switch($roleid, context $context) {
    global $USER;

                                                                                            
    if (!isset($USER->access)) {
        load_all_capabilities();
    }


        if ($roleid == 0) {
        unset($USER->access['rsw'][$context->path]);
        return true;
    }

    $USER->access['rsw'][$context->path] = $roleid;

        load_role_access_by_context($roleid, $context, $USER->access);

    return true;
}


function is_role_switched($courseid) {
    global $USER;
    $context = context_course::instance($courseid, MUST_EXIST);
    return (!empty($USER->access['rsw'][$context->path]));
}


function get_roles_with_override_on_context(context $context) {
    global $DB;

    return $DB->get_records_sql("SELECT r.*
                                   FROM {role_capabilities} rc, {role} r
                                  WHERE rc.roleid = r.id AND rc.contextid = ?",
                                array($context->id));
}


function get_capabilities_from_role_on_context($role, context $context) {
    global $DB;

    return $DB->get_records_sql("SELECT *
                                   FROM {role_capabilities}
                                  WHERE contextid = ? AND roleid = ?",
                                array($context->id, $role->id));
}


function get_roles_with_assignment_on_context(context $context) {
    global $DB;

    return $DB->get_records_sql("SELECT r.*
                                   FROM {role_assignments} ra, {role} r
                                  WHERE ra.roleid = r.id AND ra.contextid = ?",
                                array($context->id));
}


function get_users_from_role_on_context($role, context $context) {
    global $DB;

    return $DB->get_records_sql("SELECT *
                                   FROM {role_assignments}
                                  WHERE contextid = ? AND roleid = ?",
                                array($context->id, $role->id));
}


function user_has_role_assignment($userid, $roleid, $contextid = 0) {
    global $DB;

    if ($contextid) {
        if (!$context = context::instance_by_id($contextid, IGNORE_MISSING)) {
            return false;
        }
        $parents = $context->get_parent_context_ids(true);
        list($contexts, $params) = $DB->get_in_or_equal($parents, SQL_PARAMS_NAMED, 'r');
        $params['userid'] = $userid;
        $params['roleid'] = $roleid;

        $sql = "SELECT COUNT(ra.id)
                  FROM {role_assignments} ra
                 WHERE ra.userid = :userid AND ra.roleid = :roleid AND ra.contextid $contexts";

        $count = $DB->get_field_sql($sql, $params);
        return ($count > 0);

    } else {
        return $DB->record_exists('role_assignments', array('userid'=>$userid, 'roleid'=>$roleid));
    }
}


function role_get_name(stdClass $role, $context = null, $rolenamedisplay = ROLENAME_ALIAS) {
    global $DB;

    if ($rolenamedisplay == ROLENAME_SHORT) {
        return $role->shortname;
    }

    if (!$context or !$coursecontext = $context->get_course_context(false)) {
        $coursecontext = null;
    }

    if ($coursecontext and !property_exists($role, 'coursealias') and ($rolenamedisplay == ROLENAME_ALIAS or $rolenamedisplay == ROLENAME_BOTH or $rolenamedisplay == ROLENAME_ALIAS_RAW)) {
        $role = clone($role);         if ($r = $DB->get_record('role_names', array('roleid'=>$role->id, 'contextid'=>$coursecontext->id))) {
            $role->coursealias = $r->name;
        } else {
            $role->coursealias = null;
        }
    }

    if ($rolenamedisplay == ROLENAME_ALIAS_RAW) {
        if ($coursecontext) {
            return $role->coursealias;
        } else {
            return null;
        }
    }

    if (trim($role->name) !== '') {
                $original = format_string($role->name, true, array('context'=>context_system::instance()));

    } else {
                        switch ($role->shortname) {
            case 'manager':           $original = get_string('manager', 'role'); break;
            case 'coursecreator':     $original = get_string('coursecreators'); break;
            case 'departmentmanager': $original = get_string('departmentmanager'); break;
            case 'departmentassistant': $original = get_string('departmentassistant'); break;
            case 'editingteacher':    $original = get_string('defaultcourseteacher'); break;
            case 'teacher':           $original = get_string('noneditingteacher'); break;
            case 'teacherassistant':  $original = get_string('teacherassistant'); break;
            case 'student':           $original = get_string('defaultcoursestudent'); break;
            case 'auditor':           $original = get_string('auditor'); break;
            case 'guest':             $original = get_string('guest'); break;
            case 'user':              $original = get_string('authenticateduser'); break;
            case 'frontpage':         $original = get_string('frontpageuser', 'role'); break;
                        default:                $original = $role->shortname; break;
        }
    }

    if ($rolenamedisplay == ROLENAME_ORIGINAL) {
        return $original;
    }

    if ($rolenamedisplay == ROLENAME_ORIGINALANDSHORT) {
        return "$original ($role->shortname)";
    }

    if ($rolenamedisplay == ROLENAME_ALIAS) {
        if ($coursecontext and trim($role->coursealias) !== '') {
            return format_string($role->coursealias, true, array('context'=>$coursecontext));
        } else {
            return $original;
        }
    }

    if ($rolenamedisplay == ROLENAME_BOTH) {
        if ($coursecontext and trim($role->coursealias) !== '') {
            return format_string($role->coursealias, true, array('context'=>$coursecontext)) . " ($original)";
        } else {
            return $original;
        }
    }

    throw new coding_exception('Invalid $rolenamedisplay parameter specified in role_get_name()');
}


function role_get_description(stdClass $role) {
    if (!html_is_blank($role->description)) {
        return format_text($role->description, FORMAT_HTML, array('context'=>context_system::instance()));
    }

    switch ($role->shortname) {
        case 'manager':           return get_string('managerdescription', 'role');
        case 'coursecreator':     return get_string('coursecreatorsdescription');
        case 'departmentmanager': return get_string('departmentmanagerdescription');
        case 'departmentassistant': return get_string('departmentassistantdescription');
        case 'editingteacher':    return get_string('defaultcourseteacherdescription');
        case 'teacher':           return get_string('noneditingteacherdescription');
        case 'teacherassistant':  return get_string('teacherassistantdescription');
        case 'student':           return get_string('defaultcoursestudentdescription');
        case 'auditor':           return get_string('auditordescription');
        case 'guest':             return get_string('guestdescription');
        case 'user':              return get_string('authenticateduserdescription');
        case 'frontpage':         return get_string('frontpageuserdescription', 'role');
        default:                  return '';
    }
}


function role_get_names(context $context = null, $rolenamedisplay = ROLENAME_ALIAS, $returnmenu = null) {
    return role_fix_names(get_all_roles($context), $context, $rolenamedisplay, $returnmenu);
}


function role_fix_names($roleoptions, context $context = null, $rolenamedisplay = ROLENAME_ALIAS, $returnmenu = null) {
    global $DB;

    if (empty($roleoptions)) {
        return array();
    }

    if (!$context or !$coursecontext = $context->get_course_context(false)) {
        $coursecontext = null;
    }

        $first = reset($roleoptions);
    if ($returnmenu === null) {
        $returnmenu = !is_object($first);
    }

    if (!is_object($first) or !property_exists($first, 'shortname')) {
        $allroles = get_all_roles($context);
        foreach ($roleoptions as $rid => $unused) {
            $roleoptions[$rid] = $allroles[$rid];
        }
    }

        if ($coursecontext and ($rolenamedisplay == ROLENAME_ALIAS_RAW or $rolenamedisplay == ROLENAME_ALIAS or $rolenamedisplay == ROLENAME_BOTH)) {
        $first = reset($roleoptions);
        if (!property_exists($first, 'coursealias')) {
            $aliasnames = $DB->get_records('role_names', array('contextid'=>$coursecontext->id));
            foreach ($aliasnames as $alias) {
                if (isset($roleoptions[$alias->roleid])) {
                    $roleoptions[$alias->roleid]->coursealias = $alias->name;
                }
            }
        }
    }

        foreach ($roleoptions as $rid => $role) {
        $roleoptions[$rid]->localname = role_get_name($role, $coursecontext, $rolenamedisplay);
    }

    if (!$returnmenu) {
        return $roleoptions;
    }

    $menu = array();
    foreach ($roleoptions as $rid => $role) {
        $menu[$rid] = $role->localname;
    }

    return $menu;
}


function component_level_changed($cap, $comp, $contextlevel) {

    if (strstr($cap->component, '/') && strstr($comp, '/')) {
        $compsa = explode('/', $cap->component);
        $compsb = explode('/', $comp);

                if (($compsa[0] == 'report') && ($compsb[0] == 'report')) {
            return false;
        }

                if (($compsa[0] == 'gradeexport' || $compsa[0] == 'gradeimport' || $compsa[0] == 'gradereport') &&
            ($compsb[0] == 'gradeexport' || $compsb[0] == 'gradeimport' || $compsb[0] == 'gradereport')) {
            return false;
        }

        if (($compsa[0] == 'coursereport') && ($compsb[0] == 'coursereport')) {
            return false;
        }
    }

    return ($cap->component != $comp || $cap->contextlevel != $contextlevel);
}


function fix_role_sortorder($allroles) {
    global $DB;

    $rolesort = array();
    $i = 0;
    foreach ($allroles as $role) {
        $rolesort[$i] = $role->id;
        if ($role->sortorder != $i) {
            $r = new stdClass();
            $r->id = $role->id;
            $r->sortorder = $i;
            $DB->update_record('role', $r);
            $allroles[$role->id]->sortorder = $i;
        }
        $i++;
    }
    return $rolesort;
}


function switch_roles($first, $second) {
    global $DB;
    $temp = $DB->get_field('role', 'MAX(sortorder) + 1', array());
    $result = $DB->set_field('role', 'sortorder', $temp, array('sortorder' => $first->sortorder));
    $result = $result && $DB->set_field('role', 'sortorder', $first->sortorder, array('sortorder' => $second->sortorder));
    $result = $result && $DB->set_field('role', 'sortorder', $second->sortorder, array('sortorder' => $temp));
    return $result;
}


function role_cap_duplicate($sourcerole, $targetrole) {
    global $DB;

    $systemcontext = context_system::instance();
    $caps = $DB->get_records_sql("SELECT *
                                    FROM {role_capabilities}
                                   WHERE roleid = ? AND contextid = ?",
                                 array($sourcerole->id, $systemcontext->id));
        foreach ($caps as $cap) {
        unset($cap->id);
        $cap->roleid = $targetrole;
        $DB->insert_record('role_capabilities', $cap);
    }
}


function get_roles_with_cap_in_context($context, $capability) {
    global $DB;

    $ctxids = trim($context->path, '/');     $ctxids = str_replace('/', ',', $ctxids);

    $sql = "SELECT rc.id, rc.roleid, rc.permission, ctx.depth
              FROM {role_capabilities} rc
              JOIN {context} ctx ON ctx.id = rc.contextid
             WHERE rc.capability = :cap AND ctx.id IN ($ctxids)
          ORDER BY rc.roleid ASC, ctx.depth DESC";
    $params = array('cap'=>$capability);

    if (!$capdefs = $DB->get_records_sql($sql, $params)) {
                return array(array(), array());
    }

    $forbidden = array();
    $needed    = array();
    foreach($capdefs as $def) {
        if (isset($forbidden[$def->roleid])) {
            continue;
        }
        if ($def->permission == CAP_PROHIBIT) {
            $forbidden[$def->roleid] = $def->roleid;
            unset($needed[$def->roleid]);
            continue;
        }
        if (!isset($needed[$def->roleid])) {
            if ($def->permission == CAP_ALLOW) {
                $needed[$def->roleid] = true;
            } else if ($def->permission == CAP_PREVENT) {
                $needed[$def->roleid] = false;
            }
        }
    }
    unset($capdefs);

        foreach($needed as $key=>$value) {
        if (!$value) {
            unset($needed[$key]);
        } else {
            $needed[$key] = $key;
        }
    }

    return array($needed, $forbidden);
}


function get_roles_with_caps_in_context($context, $capabilities) {
    $neededarr = array();
    $forbiddenarr = array();
    foreach($capabilities as $caprequired) {
        list($neededarr[], $forbiddenarr[]) = get_roles_with_cap_in_context($context, $caprequired);
    }

    $rolesthatcanrate = array();
    if (!empty($neededarr)) {
        foreach ($neededarr as $needed) {
            if (empty($rolesthatcanrate)) {
                $rolesthatcanrate = $needed;
            } else {
                                $rolesthatcanrate = array_intersect_key($rolesthatcanrate,$needed);
            }
        }
    }
    if (!empty($forbiddenarr) && !empty($rolesthatcanrate)) {
        foreach ($forbiddenarr as $forbidden) {
                      $rolesthatcanrate = array_diff($rolesthatcanrate, $forbidden);
        }
    }
    return $rolesthatcanrate;
}


function get_role_names_with_caps_in_context($context, $capabilities) {
    global $DB;

    $rolesthatcanrate = get_roles_with_caps_in_context($context, $capabilities);
    $allroles = $DB->get_records('role', null, 'sortorder DESC');

    $roles = array();
    foreach ($rolesthatcanrate as $r) {
        $roles[$r] = $allroles[$r];
    }

    return role_fix_names($roles, $context, ROLENAME_ALIAS, true);
}


function prohibit_is_removable($roleid, context $context, $capability) {
    global $DB;

    $ctxids = trim($context->path, '/');     $ctxids = str_replace('/', ',', $ctxids);

    $params = array('roleid'=>$roleid, 'cap'=>$capability, 'prohibit'=>CAP_PROHIBIT);

    $sql = "SELECT ctx.id
              FROM {role_capabilities} rc
              JOIN {context} ctx ON ctx.id = rc.contextid
             WHERE rc.roleid = :roleid AND rc.permission = :prohibit AND rc.capability = :cap AND ctx.id IN ($ctxids)
          ORDER BY ctx.depth DESC";

    if (!$prohibits = $DB->get_records_sql($sql, $params)) {
                return true;
    }

    if (count($prohibits) > 1) {
                return false;
    }

    return !empty($prohibits[$context->id]);
}


function role_change_permission($roleid, $context, $capname, $permission) {
    global $DB;

    if ($permission == CAP_INHERIT) {
        unassign_capability($capname, $roleid, $context->id);
        $context->mark_dirty();
        return;
    }

    $ctxids = trim($context->path, '/');     $ctxids = str_replace('/', ',', $ctxids);

    $params = array('roleid'=>$roleid, 'cap'=>$capname);

    $sql = "SELECT ctx.id, rc.permission, ctx.depth
              FROM {role_capabilities} rc
              JOIN {context} ctx ON ctx.id = rc.contextid
             WHERE rc.roleid = :roleid AND rc.capability = :cap AND ctx.id IN ($ctxids)
          ORDER BY ctx.depth DESC";

    if ($existing = $DB->get_records_sql($sql, $params)) {
        foreach($existing as $e) {
            if ($e->permission == CAP_PROHIBIT) {
                                return;
            }
        }
        $lowest = array_shift($existing);
        if ($lowest->permission == $permission) {
                        return;
        }
        if ($existing) {
            $parent = array_shift($existing);
            if ($parent->permission == $permission) {
                                                unassign_capability($capname, $roleid, $context->id);
                $context->mark_dirty();
                return;
            }
        }

    } else {
        if ($permission == CAP_PREVENT) {
                        return;
        }
    }

        assign_capability($capname, $permission, $roleid, $context->id, true);

        $context->mark_dirty();
}



abstract class context extends stdClass implements IteratorAggregate {

    
    protected $_id;

    
    protected $_contextlevel;

    
    protected $_instanceid;

    
    protected $_path;

    
    protected $_depth;

    
    private static $cache_contextsbyid = array();

    
    private static $cache_contexts     = array();

    
    protected static $cache_count      = 0;

    
    protected static $cache_preloaded  = array();

    
    protected static $systemcontext    = null;

    
    protected static function reset_caches() {
        self::$cache_contextsbyid = array();
        self::$cache_contexts     = array();
        self::$cache_count        = 0;
        self::$cache_preloaded    = array();

        self::$systemcontext = null;
    }

    
    protected static function cache_add(context $context) {
        if (isset(self::$cache_contextsbyid[$context->id])) {
                        return;
        }

        if (self::$cache_count >= CONTEXT_CACHE_MAX_SIZE) {
            $i = 0;
            foreach(self::$cache_contextsbyid as $ctx) {
                $i++;
                if ($i <= 100) {
                                        continue;
                }
                if ($i > (CONTEXT_CACHE_MAX_SIZE / 3)) {
                                        break;
                }
                unset(self::$cache_contextsbyid[$ctx->id]);
                unset(self::$cache_contexts[$ctx->contextlevel][$ctx->instanceid]);
                self::$cache_count--;
            }
        }

        self::$cache_contexts[$context->contextlevel][$context->instanceid] = $context;
        self::$cache_contextsbyid[$context->id] = $context;
        self::$cache_count++;
    }

    
    protected static function cache_remove(context $context) {
        if (!isset(self::$cache_contextsbyid[$context->id])) {
                        return;
        }
        unset(self::$cache_contexts[$context->contextlevel][$context->instanceid]);
        unset(self::$cache_contextsbyid[$context->id]);

        self::$cache_count--;

        if (self::$cache_count < 0) {
            self::$cache_count = 0;
        }
    }

    
    protected static function cache_get($contextlevel, $instance) {
        if (isset(self::$cache_contexts[$contextlevel][$instance])) {
            return self::$cache_contexts[$contextlevel][$instance];
        }
        return false;
    }

    
    protected static function cache_get_by_id($id) {
        if (isset(self::$cache_contextsbyid[$id])) {
            return self::$cache_contextsbyid[$id];
        }
        return false;
    }

    
     protected static function preload_from_record(stdClass $rec) {
         if (empty($rec->ctxid) or empty($rec->ctxlevel) or !isset($rec->ctxinstance) or empty($rec->ctxpath) or empty($rec->ctxdepth)) {
                          return;
         }

                  $record = new stdClass();
         $record->id           = $rec->ctxid;       unset($rec->ctxid);
         $record->contextlevel = $rec->ctxlevel;    unset($rec->ctxlevel);
         $record->instanceid   = $rec->ctxinstance; unset($rec->ctxinstance);
         $record->path         = $rec->ctxpath;     unset($rec->ctxpath);
         $record->depth        = $rec->ctxdepth;    unset($rec->ctxdepth);

         return context::create_instance_from_record($record);
     }


    
    
    public function __set($name, $value) {
        debugging('Can not change context instance properties!');
    }

    
    public function __get($name) {
        switch ($name) {
            case 'id':           return $this->_id;
            case 'contextlevel': return $this->_contextlevel;
            case 'instanceid':   return $this->_instanceid;
            case 'path':         return $this->_path;
            case 'depth':        return $this->_depth;

            default:
                debugging('Invalid context property accessed! '.$name);
                return null;
        }
    }

    
    public function __isset($name) {
        switch ($name) {
            case 'id':           return isset($this->_id);
            case 'contextlevel': return isset($this->_contextlevel);
            case 'instanceid':   return isset($this->_instanceid);
            case 'path':         return isset($this->_path);
            case 'depth':        return isset($this->_depth);

            default: return false;
        }

    }

    
    public function __unset($name) {
        debugging('Can not unset context instance properties!');
    }

    
    
    public function getIterator() {
        $ret = array(
            'id'           => $this->id,
            'contextlevel' => $this->contextlevel,
            'instanceid'   => $this->instanceid,
            'path'         => $this->path,
            'depth'        => $this->depth
        );
        return new ArrayIterator($ret);
    }

    
    
    protected function __construct(stdClass $record) {
        $this->_id           = (int)$record->id;
        $this->_contextlevel = (int)$record->contextlevel;
        $this->_instanceid   = $record->instanceid;
        $this->_path         = $record->path;
        $this->_depth        = $record->depth;
    }

    
    protected static function create_instance_from_record(stdClass $record) {
        $classname = context_helper::get_class_for_level($record->contextlevel);

        if ($context = context::cache_get_by_id($record->id)) {
            return $context;
        }

        $context = new $classname($record);
        context::cache_add($context);

        return $context;
    }

    
    protected static function merge_context_temp_table() {
        global $DB;

        

        $dbfamily = $DB->get_dbfamily();
        if ($dbfamily == 'mysql') {
            $updatesql = "UPDATE {context} ct, {context_temp} temp
                             SET ct.path     = temp.path,
                                 ct.depth    = temp.depth
                           WHERE ct.id = temp.id";
        } else if ($dbfamily == 'oracle') {
            $updatesql = "UPDATE {context} ct
                             SET (ct.path, ct.depth) =
                                 (SELECT temp.path, temp.depth
                                    FROM {context_temp} temp
                                   WHERE temp.id=ct.id)
                           WHERE EXISTS (SELECT 'x'
                                           FROM {context_temp} temp
                                           WHERE temp.id = ct.id)";
        } else if ($dbfamily == 'postgres' or $dbfamily == 'mssql') {
            $updatesql = "UPDATE {context}
                             SET path     = temp.path,
                                 depth    = temp.depth
                            FROM {context_temp} temp
                           WHERE temp.id={context}.id";
        } else {
                        $updatesql = "UPDATE {context}
                             SET path     = (SELECT path FROM {context_temp} WHERE id = {context}.id),
                                 depth    = (SELECT depth FROM {context_temp} WHERE id = {context}.id)
                             WHERE id IN (SELECT id FROM {context_temp})";
        }

        $DB->execute($updatesql);
    }

   
    public static function instance_by_id($id, $strictness = MUST_EXIST) {
        global $DB;

        if (get_called_class() !== 'context' and get_called_class() !== 'context_helper') {
                        throw new coding_exception('use only context::instance_by_id() for real context levels use ::instance() methods');
        }

        if ($id == SYSCONTEXTID) {
            return context_system::instance(0, $strictness);
        }

        if (is_array($id) or is_object($id) or empty($id)) {
            throw new coding_exception('Invalid context id specified context::instance_by_id()');
        }

        if ($context = context::cache_get_by_id($id)) {
            return $context;
        }

        if ($record = $DB->get_record('context', array('id'=>$id), '*', $strictness)) {
            return context::create_instance_from_record($record);
        }

        return false;
    }

    
    public function update_moved(context $newparent) {
        global $DB;

        $frompath = $this->_path;
        $newpath  = $newparent->path . '/' . $this->_id;

        $trans = $DB->start_delegated_transaction();

        $this->mark_dirty();

        $setdepth = '';
        if (($newparent->depth +1) != $this->_depth) {
            $diff = $newparent->depth - $this->_depth + 1;
            $setdepth = ", depth = depth + $diff";
        }
        $sql = "UPDATE {context}
                   SET path = ?
                       $setdepth
                 WHERE id = ?";
        $params = array($newpath, $this->_id);
        $DB->execute($sql, $params);

        $this->_path  = $newpath;
        $this->_depth = $newparent->depth + 1;

        $sql = "UPDATE {context}
                   SET path = ".$DB->sql_concat("?", $DB->sql_substr("path", strlen($frompath)+1))."
                       $setdepth
                 WHERE path LIKE ?";
        $params = array($newpath, "{$frompath}/%");
        $DB->execute($sql, $params);

        $this->mark_dirty();

        context::reset_caches();

        $trans->allow_commit();
    }

    
    public function reset_paths($rebuild = true) {
        global $DB;

        if ($this->_path) {
            $this->mark_dirty();
        }
        $DB->set_field_select('context', 'depth', 0, "path LIKE '%/$this->_id/%'");
        $DB->set_field_select('context', 'path', NULL, "path LIKE '%/$this->_id/%'");
        if ($this->_contextlevel != CONTEXT_SYSTEM) {
            $DB->set_field('context', 'depth', 0, array('id'=>$this->_id));
            $DB->set_field('context', 'path', NULL, array('id'=>$this->_id));
            $this->_depth = 0;
            $this->_path = null;
        }

        if ($rebuild) {
            context_helper::build_all_paths(false);
        }

        context::reset_caches();
    }

    
    public function delete_content() {
        global $CFG, $DB;

        blocks_delete_all_for_context($this->_id);
        filter_delete_all_for_context($this->_id);

        require_once($CFG->dirroot . '/comment/lib.php');
        comment::delete_comments(array('contextid'=>$this->_id));

        require_once($CFG->dirroot.'/rating/lib.php');
        $delopt = new stdclass();
        $delopt->contextid = $this->_id;
        $rm = new rating_manager();
        $rm->delete_ratings($delopt);

                $fs = get_file_storage();
        $fs->delete_area_files($this->_id);

                require_once($CFG->dirroot . '/repository/lib.php');
        repository::delete_all_for_context($this->_id);

                require_once($CFG->dirroot.'/grade/grading/lib.php');
        grading_manager::delete_all_for_context($this->_id);

                        $DB->delete_records('role_assignments', array('contextid'=>$this->_id));
        $DB->delete_records('role_capabilities', array('contextid'=>$this->_id));
        $DB->delete_records('role_names', array('contextid'=>$this->_id));
    }

    
    public function delete() {
        global $DB;

        if ($this->_contextlevel <= CONTEXT_SYSTEM) {
            throw new coding_exception('Cannot delete system context');
        }

                if (!$DB->record_exists('context', array('id'=>$this->_id))) {
            context::cache_remove($this);
            return;
        }

        $this->delete_content();
        $DB->delete_records('context', array('id'=>$this->_id));
                context::cache_remove($this);

                if (!is_null($this->_path) and $this->_depth > 0) {
            $this->mark_dirty();
        }
    }

    
    
    protected static function insert_context_record($contextlevel, $instanceid, $parentpath) {
        global $DB;

        $record = new stdClass();
        $record->contextlevel = $contextlevel;
        $record->instanceid   = $instanceid;
        $record->depth        = 0;
        $record->path         = null; 
        $record->id = $DB->insert_record('context', $record);

                if (!is_null($parentpath)) {
            $record->path = $parentpath.'/'.$record->id;
            $record->depth = substr_count($record->path, '/');
            $DB->update_record('context', $record);
        }

        return $record;
    }

    
    public function get_context_name($withprefix = true, $short = false) {
                throw new coding_exception('can not get name of abstract context');
    }

    
    public abstract function get_url();

    
    public abstract function get_capabilities();

    
    public function get_child_contexts() {
        global $DB;

        if (empty($this->_path) or empty($this->_depth)) {
            debugging('Can not find child contexts of context '.$this->_id.' try rebuilding of context paths');
            return array();
        }

        $sql = "SELECT ctx.*
                  FROM {context} ctx
                 WHERE ctx.path LIKE ?";
        $params = array($this->_path.'/%');
        $records = $DB->get_records_sql($sql, $params);

        $result = array();
        foreach ($records as $record) {
            $result[$record->id] = context::create_instance_from_record($record);
        }

        return $result;
    }

    
    public function get_parent_contexts($includeself = false) {
        if (!$contextids = $this->get_parent_context_ids($includeself)) {
            return array();
        }

        $result = array();
        foreach ($contextids as $contextid) {
            $parent = context::instance_by_id($contextid, MUST_EXIST);
            $result[$parent->id] = $parent;
        }

        return $result;
    }

    
    public function get_parent_context_ids($includeself = false) {
        if (empty($this->_path)) {
            return array();
        }

        $parentcontexts = trim($this->_path, '/');         $parentcontexts = explode('/', $parentcontexts);
        if (!$includeself) {
            array_pop($parentcontexts);         }

        return array_reverse($parentcontexts);
    }

    
    public function get_parent_context() {
        if (empty($this->_path) or $this->_id == SYSCONTEXTID) {
            return false;
        }

        $parentcontexts = trim($this->_path, '/');         $parentcontexts = explode('/', $parentcontexts);
        array_pop($parentcontexts);         $contextid = array_pop($parentcontexts); 
        return context::instance_by_id($contextid, MUST_EXIST);
    }

    
    public function get_course_context($strict = true) {
        if ($strict) {
            throw new coding_exception('Context does not belong to any course.');
        } else {
            return false;
        }
    }

    
    protected static function get_cleanup_sql() {
        throw new coding_exception('get_cleanup_sql() method must be implemented in all context levels');
    }

    
    protected static function build_paths($force) {
        throw new coding_exception('build_paths() method must be implemented in all context levels');
    }

    
    protected static function create_level_instances() {
        throw new coding_exception('create_level_instances() method must be implemented in all context levels');
    }

    
    public function reload_if_dirty() {
        global $ACCESSLIB_PRIVATE, $USER;

                if (CLI_SCRIPT) {
            if (!isset($ACCESSLIB_PRIVATE->dirtycontexts)) {
                                $ACCESSLIB_PRIVATE->dirtycontexts = array();
            }
        } else {
            if (!isset($ACCESSLIB_PRIVATE->dirtycontexts)) {
                if (!isset($USER->access['time'])) {
                                        return;
                }
                                $ACCESSLIB_PRIVATE->dirtycontexts = get_cache_flags('accesslib/dirtycontexts', $USER->access['time']-2);
            }
        }

        foreach ($ACCESSLIB_PRIVATE->dirtycontexts as $path=>$unused) {
            if ($path === $this->_path or strpos($this->_path, $path.'/') === 0) {
                                                reload_all_capabilities();
                break;
            }
        }
    }

    
    public function mark_dirty() {
        global $CFG, $USER, $ACCESSLIB_PRIVATE;

        if (during_initial_install()) {
            return;
        }

                if (is_string($this->_path) && $this->_path !== '') {
            set_cache_flag('accesslib/dirtycontexts', $this->_path, 1, time()+$CFG->sessiontimeout);
            if (isset($ACCESSLIB_PRIVATE->dirtycontexts)) {
                $ACCESSLIB_PRIVATE->dirtycontexts[$this->_path] = 1;
            } else {
                if (CLI_SCRIPT) {
                    $ACCESSLIB_PRIVATE->dirtycontexts = array($this->_path => 1);
                } else {
                    if (isset($USER->access['time'])) {
                        $ACCESSLIB_PRIVATE->dirtycontexts = get_cache_flags('accesslib/dirtycontexts', $USER->access['time']-2);
                    } else {
                        $ACCESSLIB_PRIVATE->dirtycontexts = array($this->_path => 1);
                    }
                                    }
            }
        }
    }
}



class context_helper extends context {

    
    private static $alllevels;

    
    protected function __construct() {
    }

    
    public static function reset_levels() {
        self::$alllevels = null;
    }

    
    private static function init_levels() {
        global $CFG;

        if (isset(self::$alllevels)) {
            return;
        }
        self::$alllevels = array(
            CONTEXT_SYSTEM    => 'context_system',
            CONTEXT_USER      => 'context_user',
            CONTEXT_COURSECAT => 'context_coursecat',
            CONTEXT_COURSE    => 'context_course',
            CONTEXT_MODULE    => 'context_module',
            CONTEXT_BLOCK     => 'context_block',
        );

        if (empty($CFG->custom_context_classes)) {
            return;
        }

        $levels = $CFG->custom_context_classes;
        if (!is_array($levels)) {
            $levels = @unserialize($levels);
        }
        if (!is_array($levels)) {
            debugging('Invalid $CFG->custom_context_classes detected, value ignored.', DEBUG_DEVELOPER);
            return;
        }

                foreach ($levels as $level => $classname) {
            self::$alllevels[$level] = $classname;
        }
        ksort(self::$alllevels);
    }

    
    public static function get_class_for_level($contextlevel) {
        self::init_levels();
        if (isset(self::$alllevels[$contextlevel])) {
            return self::$alllevels[$contextlevel];
        } else {
            throw new coding_exception('Invalid context level specified');
        }
    }

    
    public static function get_all_levels() {
        self::init_levels();
        return self::$alllevels;
    }

    
    public static function cleanup_instances() {
        global $DB;
        self::init_levels();

        $sqls = array();
        foreach (self::$alllevels as $level=>$classname) {
            $sqls[] = $classname::get_cleanup_sql();
        }

        $sql = implode(" UNION ", $sqls);

                $transaction = $DB->start_delegated_transaction();

        $rs = $DB->get_recordset_sql($sql);
        foreach ($rs as $record) {
            $context = context::create_instance_from_record($record);
            $context->delete();
        }
        $rs->close();

        $transaction->allow_commit();
    }

    
    public static function create_instances($contextlevel = null, $buildpaths = true) {
        self::init_levels();
        foreach (self::$alllevels as $level=>$classname) {
            if ($contextlevel and $level > $contextlevel) {
                                continue;
            }
            $classname::create_level_instances();
            if ($buildpaths) {
                $classname::build_paths(false);
            }
        }
    }

    
    public static function build_all_paths($force = false) {
        self::init_levels();
        foreach (self::$alllevels as $classname) {
            $classname::build_paths($force);
        }

                accesslib_clear_all_caches(true);
    }

    
    public static function reset_caches() {
        context::reset_caches();
    }

    
    public static function get_preload_record_columns($tablealias) {
        return array("$tablealias.id"=>"ctxid", "$tablealias.path"=>"ctxpath", "$tablealias.depth"=>"ctxdepth", "$tablealias.contextlevel"=>"ctxlevel", "$tablealias.instanceid"=>"ctxinstance");
    }

    
    public static function get_preload_record_columns_sql($tablealias) {
        return "$tablealias.id AS ctxid, $tablealias.path AS ctxpath, $tablealias.depth AS ctxdepth, $tablealias.contextlevel AS ctxlevel, $tablealias.instanceid AS ctxinstance";
    }

    
     public static function preload_from_record(stdClass $rec) {
         context::preload_from_record($rec);
     }

    
    public static function preload_course($courseid) {
                if (isset(context::$cache_preloaded[$courseid])) {
            return;
        }
        $coursecontext = context_course::instance($courseid);
        $coursecontext->get_child_contexts();

        context::$cache_preloaded[$courseid] = true;
    }

    
    public static function delete_instance($contextlevel, $instanceid) {
        global $DB;

                if ($record = $DB->get_record('context', array('contextlevel'=>$contextlevel, 'instanceid'=>$instanceid))) {
            $context = context::create_instance_from_record($record);
            $context->delete();
        } else {
                    }
    }

    
    public static function get_level_name($contextlevel) {
        $classname = context_helper::get_class_for_level($contextlevel);
        return $classname::get_level_name();
    }

    
    public function get_url() {
    }

    
    public function get_capabilities() {
    }
}



class context_system extends context {
    
    protected function __construct(stdClass $record) {
        parent::__construct($record);
        if ($record->contextlevel != CONTEXT_SYSTEM) {
            throw new coding_exception('Invalid $record->contextlevel in context_system constructor.');
        }
    }

    
    public static function get_level_name() {
        return get_string('coresystem');
    }

    
    public function get_context_name($withprefix = true, $short = false) {
        return self::get_level_name();
    }

    
    public function get_url() {
        return new moodle_url('/');
    }

    
    public function get_capabilities() {
        global $DB;

        $sort = 'ORDER BY contextlevel,component,name';   
        $params = array();
        $sql = "SELECT *
                  FROM {capabilities}";

        return $DB->get_records_sql($sql.' '.$sort, $params);
    }

    
    protected static function create_level_instances() {
                self::instance(0);
    }

    
    public static function instance($instanceid = 0, $strictness = MUST_EXIST, $cache = true) {
        global $DB;

        if ($instanceid != 0) {
            debugging('context_system::instance(): invalid $id parameter detected, should be 0');
        }

        if (defined('SYSCONTEXTID') and $cache) {             if (!isset(context::$systemcontext)) {
                $record = new stdClass();
                $record->id           = SYSCONTEXTID;
                $record->contextlevel = CONTEXT_SYSTEM;
                $record->instanceid   = 0;
                $record->path         = '/'.SYSCONTEXTID;
                $record->depth        = 1;
                context::$systemcontext = new context_system($record);
            }
            return context::$systemcontext;
        }


        try {
                        $record = $DB->get_record('context', array('contextlevel'=>CONTEXT_SYSTEM), '*', MUST_EXIST);
        } catch (dml_exception $e) {
                        if (!during_initial_install()) {
                                throw $e;
            }
            $record = null;
        }

        if (!$record) {
            $record = new stdClass();
            $record->contextlevel = CONTEXT_SYSTEM;
            $record->instanceid   = 0;
            $record->depth        = 1;
            $record->path         = null; 
            try {
                if ($DB->count_records('context')) {
                                        return null;
                }
                if (defined('SYSCONTEXTID')) {
                                        $record->id = SYSCONTEXTID;
                    $DB->import_record('context', $record);
                    $DB->get_manager()->reset_sequence('context');
                } else {
                    $record->id = $DB->insert_record('context', $record);
                }
            } catch (dml_exception $e) {
                                return null;
            }
        }

        if ($record->instanceid != 0) {
                        debugging('Invalid system context detected');
        }

        if ($record->depth != 1 or $record->path != '/'.$record->id) {
                        $record->depth = 1;
            $record->path  = '/'.$record->id;
            $DB->update_record('context', $record);
        }

        if (!defined('SYSCONTEXTID')) {
            define('SYSCONTEXTID', $record->id);
        }

        context::$systemcontext = new context_system($record);
        return context::$systemcontext;
    }

    
    public function get_child_contexts() {
        global $DB;

        debugging('Fetching of system context child courses is strongly discouraged on production servers (it may eat all available memory)!');

                        $sql = "SELECT c.*
                  FROM {context} c
                 WHERE contextlevel > ".CONTEXT_SYSTEM;
        $records = $DB->get_records_sql($sql);

        $result = array();
        foreach ($records as $record) {
            $result[$record->id] = context::create_instance_from_record($record);
        }

        return $result;
    }

    
    protected static function get_cleanup_sql() {
        $sql = "
                  SELECT c.*
                    FROM {context} c
                   WHERE 1=2
               ";

        return $sql;
    }

    
    protected static function build_paths($force) {
        global $DB;

        

                $record = $DB->get_record('context', array('contextlevel'=>CONTEXT_SYSTEM), '*', MUST_EXIST);

        if ($record->instanceid != 0) {
            debugging('Invalid system context detected');
        }

        if (defined('SYSCONTEXTID') and $record->id != SYSCONTEXTID) {
            debugging('Invalid SYSCONTEXTID detected');
        }

        if ($record->depth != 1 or $record->path != '/'.$record->id) {
                        $record->depth    = 1;
            $record->path     = '/'.$record->id;
            $DB->update_record('context', $record);
        }
    }
}



class context_user extends context {
    
    protected function __construct(stdClass $record) {
        parent::__construct($record);
        if ($record->contextlevel != CONTEXT_USER) {
            throw new coding_exception('Invalid $record->contextlevel in context_user constructor.');
        }
    }

    
    public static function get_level_name() {
        return get_string('user');
    }

    
    public function get_context_name($withprefix = true, $short = false) {
        global $DB;

        $name = '';
        if ($user = $DB->get_record('user', array('id'=>$this->_instanceid, 'deleted'=>0))) {
            if ($withprefix){
                $name = get_string('user').': ';
            }
            $name .= fullname($user);
        }
        return $name;
    }

    
    public function get_url() {
        global $COURSE;

        if ($COURSE->id == SITEID) {
            $url = new moodle_url('/user/profile.php', array('id'=>$this->_instanceid));
        } else {
            $url = new moodle_url('/user/view.php', array('id'=>$this->_instanceid, 'courseid'=>$COURSE->id));
        }
        return $url;
    }

    
    public function get_capabilities() {
        global $DB;

        $sort = 'ORDER BY contextlevel,component,name';   
        $extracaps = array('moodle/grade:viewall');
        list($extra, $params) = $DB->get_in_or_equal($extracaps, SQL_PARAMS_NAMED, 'cap');
        $sql = "SELECT *
                  FROM {capabilities}
                 WHERE contextlevel = ".CONTEXT_USER."
                       OR name $extra";

        return $records = $DB->get_records_sql($sql.' '.$sort, $params);
    }

    
    public static function instance($userid, $strictness = MUST_EXIST) {
        global $DB;

        if ($context = context::cache_get(CONTEXT_USER, $userid)) {
            return $context;
        }

        if (!$record = $DB->get_record('context', array('contextlevel' => CONTEXT_USER, 'instanceid' => $userid))) {
            if ($user = $DB->get_record('user', array('id' => $userid, 'deleted' => 0), 'id', $strictness)) {
                $record = context::insert_context_record(CONTEXT_USER, $user->id, '/'.SYSCONTEXTID, 0);
            }
        }

        if ($record) {
            $context = new context_user($record);
            context::cache_add($context);
            return $context;
        }

        return false;
    }

    
    protected static function create_level_instances() {
        global $DB;

        $sql = "SELECT ".CONTEXT_USER.", u.id
                  FROM {user} u
                 WHERE u.deleted = 0
                       AND NOT EXISTS (SELECT 'x'
                                         FROM {context} cx
                                        WHERE u.id = cx.instanceid AND cx.contextlevel=".CONTEXT_USER.")";
        $contextdata = $DB->get_recordset_sql($sql);
        foreach ($contextdata as $context) {
            context::insert_context_record(CONTEXT_USER, $context->id, null);
        }
        $contextdata->close();
    }

    
    protected static function get_cleanup_sql() {
        $sql = "
                  SELECT c.*
                    FROM {context} c
         LEFT OUTER JOIN {user} u ON (c.instanceid = u.id AND u.deleted = 0)
                   WHERE u.id IS NULL AND c.contextlevel = ".CONTEXT_USER."
               ";

        return $sql;
    }

    
    protected static function build_paths($force) {
        global $DB;

                $path = $DB->sql_concat('?', 'id');
        $pathstart = '/' . SYSCONTEXTID . '/';
        $params = array($pathstart);

        if ($force) {
            $where = "depth <> 2 OR path IS NULL OR path <> ({$path})";
            $params[] = $pathstart;
        } else {
            $where = "depth = 0 OR path IS NULL";
        }

        $sql = "UPDATE {context}
                   SET depth = 2,
                       path = {$path}
                 WHERE contextlevel = " . CONTEXT_USER . "
                   AND ($where)";
        $DB->execute($sql, $params);
    }
}



class context_coursecat extends context {
    
    protected function __construct(stdClass $record) {
        parent::__construct($record);
        if ($record->contextlevel != CONTEXT_COURSECAT) {
            throw new coding_exception('Invalid $record->contextlevel in context_coursecat constructor.');
        }
    }

    
    public static function get_level_name() {
        return get_string('category');
    }

    
    public function get_context_name($withprefix = true, $short = false) {
        global $DB;

        $name = '';
        if ($category = $DB->get_record('course_categories', array('id'=>$this->_instanceid))) {
            if ($withprefix){
                $name = get_string('category').': ';
            }
            $name .= format_string($category->name, true, array('context' => $this));
        }
        return $name;
    }

    
    public function get_url() {
        return new moodle_url('/course/index.php', array('categoryid' => $this->_instanceid));
    }

    
    public function get_capabilities() {
        global $DB;

        $sort = 'ORDER BY contextlevel,component,name';   
        $params = array();
        $sql = "SELECT *
                  FROM {capabilities}
                 WHERE contextlevel IN (".CONTEXT_COURSECAT.",".CONTEXT_COURSE.",".CONTEXT_MODULE.",".CONTEXT_BLOCK.")";

        return $DB->get_records_sql($sql.' '.$sort, $params);
    }

    
    public static function instance($categoryid, $strictness = MUST_EXIST) {
        global $DB;

        if ($context = context::cache_get(CONTEXT_COURSECAT, $categoryid)) {
            return $context;
        }

        if (!$record = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSECAT, 'instanceid' => $categoryid))) {
            if ($category = $DB->get_record('course_categories', array('id' => $categoryid), 'id,parent', $strictness)) {
                if ($category->parent) {
                    $parentcontext = context_coursecat::instance($category->parent);
                    $record = context::insert_context_record(CONTEXT_COURSECAT, $category->id, $parentcontext->path);
                } else {
                    $record = context::insert_context_record(CONTEXT_COURSECAT, $category->id, '/'.SYSCONTEXTID, 0);
                }
            }
        }

        if ($record) {
            $context = new context_coursecat($record);
            context::cache_add($context);
            return $context;
        }

        return false;
    }

    
    public function get_child_contexts() {
        global $DB;

        if (empty($this->_path) or empty($this->_depth)) {
            debugging('Can not find child contexts of context '.$this->_id.' try rebuilding of context paths');
            return array();
        }

        $sql = "SELECT ctx.*
                  FROM {context} ctx
                 WHERE ctx.path LIKE ? AND (ctx.depth = ? OR ctx.contextlevel = ?)";
        $params = array($this->_path.'/%', $this->depth+1, CONTEXT_COURSECAT);
        $records = $DB->get_records_sql($sql, $params);

        $result = array();
        foreach ($records as $record) {
            $result[$record->id] = context::create_instance_from_record($record);
        }

        return $result;
    }

    
    protected static function create_level_instances() {
        global $DB;

        $sql = "SELECT ".CONTEXT_COURSECAT.", cc.id
                  FROM {course_categories} cc
                 WHERE NOT EXISTS (SELECT 'x'
                                     FROM {context} cx
                                    WHERE cc.id = cx.instanceid AND cx.contextlevel=".CONTEXT_COURSECAT.")";
        $contextdata = $DB->get_recordset_sql($sql);
        foreach ($contextdata as $context) {
            context::insert_context_record(CONTEXT_COURSECAT, $context->id, null);
        }
        $contextdata->close();
    }

    
    protected static function get_cleanup_sql() {
        $sql = "
                  SELECT c.*
                    FROM {context} c
         LEFT OUTER JOIN {course_categories} cc ON c.instanceid = cc.id
                   WHERE cc.id IS NULL AND c.contextlevel = ".CONTEXT_COURSECAT."
               ";

        return $sql;
    }

    
    protected static function build_paths($force) {
        global $DB;

        if ($force or $DB->record_exists_select('context', "contextlevel = ".CONTEXT_COURSECAT." AND (depth = 0 OR path IS NULL)")) {
            if ($force) {
                $ctxemptyclause = $emptyclause = '';
            } else {
                $ctxemptyclause = "AND (ctx.path IS NULL OR ctx.depth = 0)";
                $emptyclause    = "AND ({context}.path IS NULL OR {context}.depth = 0)";
            }

            $base = '/'.SYSCONTEXTID;

                        $sql = "UPDATE {context}
                       SET depth=2,
                           path=".$DB->sql_concat("'$base/'", 'id')."
                     WHERE contextlevel=".CONTEXT_COURSECAT."
                           AND EXISTS (SELECT 'x'
                                         FROM {course_categories} cc
                                        WHERE cc.id = {context}.instanceid AND cc.depth=1)
                           $emptyclause";
            $DB->execute($sql);

                        $maxdepth = $DB->get_field_sql("SELECT MAX(depth) FROM {course_categories}");
            for ($n=2; $n<=$maxdepth; $n++) {
                $sql = "INSERT INTO {context_temp} (id, path, depth)
                        SELECT ctx.id, ".$DB->sql_concat('pctx.path', "'/'", 'ctx.id').", pctx.depth+1
                          FROM {context} ctx
                          JOIN {course_categories} cc ON (cc.id = ctx.instanceid AND ctx.contextlevel = ".CONTEXT_COURSECAT." AND cc.depth = $n)
                          JOIN {context} pctx ON (pctx.instanceid = cc.parent AND pctx.contextlevel = ".CONTEXT_COURSECAT.")
                         WHERE pctx.path IS NOT NULL AND pctx.depth > 0
                               $ctxemptyclause";
                $trans = $DB->start_delegated_transaction();
                $DB->delete_records('context_temp');
                $DB->execute($sql);
                context::merge_context_temp_table();
                $DB->delete_records('context_temp');
                $trans->allow_commit();

            }
        }
    }
}



class context_course extends context {
    
    protected function __construct(stdClass $record) {
        parent::__construct($record);
        if ($record->contextlevel != CONTEXT_COURSE) {
            throw new coding_exception('Invalid $record->contextlevel in context_course constructor.');
        }
    }

    
    public static function get_level_name() {
        return get_string('course');
    }

    
    public function get_context_name($withprefix = true, $short = false) {
        global $DB;

        $name = '';
        if ($this->_instanceid == SITEID) {
            $name = get_string('frontpage', 'admin');
        } else {
            if ($course = $DB->get_record('course', array('id'=>$this->_instanceid))) {
                if ($withprefix){
                    $name = get_string('course').': ';
                }
                if ($short){
                    $name .= format_string($course->shortname, true, array('context' => $this));
                } else {
                    $name .= format_string(get_course_display_name_for_list($course));
               }
            }
        }
        return $name;
    }

    
    public function get_url() {
        if ($this->_instanceid != SITEID) {
            return new moodle_url('/course/view.php', array('id'=>$this->_instanceid));
        }

        return new moodle_url('/');
    }

    
    public function get_capabilities() {
        global $DB;

        $sort = 'ORDER BY contextlevel,component,name';   
        $params = array();
        $sql = "SELECT *
                  FROM {capabilities}
                 WHERE contextlevel IN (".CONTEXT_COURSE.",".CONTEXT_MODULE.",".CONTEXT_BLOCK.")";

        return $DB->get_records_sql($sql.' '.$sort, $params);
    }

    
    public function get_course_context($strict = true) {
        return $this;
    }

    
    public static function instance($courseid, $strictness = MUST_EXIST) {
        global $DB;

        if ($context = context::cache_get(CONTEXT_COURSE, $courseid)) {
            return $context;
        }

        if (!$record = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSE, 'instanceid' => $courseid))) {
            if ($course = $DB->get_record('course', array('id' => $courseid), 'id,category', $strictness)) {
                if ($course->category) {
                    $parentcontext = context_coursecat::instance($course->category);
                    $record = context::insert_context_record(CONTEXT_COURSE, $course->id, $parentcontext->path);
                } else {
                    $record = context::insert_context_record(CONTEXT_COURSE, $course->id, '/'.SYSCONTEXTID, 0);
                }
            }
        }

        if ($record) {
            $context = new context_course($record);
            context::cache_add($context);
            return $context;
        }

        return false;
    }

    
    protected static function create_level_instances() {
        global $DB;

        $sql = "SELECT ".CONTEXT_COURSE.", c.id
                  FROM {course} c
                 WHERE NOT EXISTS (SELECT 'x'
                                     FROM {context} cx
                                    WHERE c.id = cx.instanceid AND cx.contextlevel=".CONTEXT_COURSE.")";
        $contextdata = $DB->get_recordset_sql($sql);
        foreach ($contextdata as $context) {
            context::insert_context_record(CONTEXT_COURSE, $context->id, null);
        }
        $contextdata->close();
    }

    
    protected static function get_cleanup_sql() {
        $sql = "
                  SELECT c.*
                    FROM {context} c
         LEFT OUTER JOIN {course} co ON c.instanceid = co.id
                   WHERE co.id IS NULL AND c.contextlevel = ".CONTEXT_COURSE."
               ";

        return $sql;
    }

    
    protected static function build_paths($force) {
        global $DB;

        if ($force or $DB->record_exists_select('context', "contextlevel = ".CONTEXT_COURSE." AND (depth = 0 OR path IS NULL)")) {
            if ($force) {
                $ctxemptyclause = $emptyclause = '';
            } else {
                $ctxemptyclause = "AND (ctx.path IS NULL OR ctx.depth = 0)";
                $emptyclause    = "AND ({context}.path IS NULL OR {context}.depth = 0)";
            }

            $base = '/'.SYSCONTEXTID;

                        $sql = "UPDATE {context}
                       SET depth = 2,
                           path = ".$DB->sql_concat("'$base/'", 'id')."
                     WHERE contextlevel = ".CONTEXT_COURSE."
                           AND EXISTS (SELECT 'x'
                                         FROM {course} c
                                        WHERE c.id = {context}.instanceid AND c.category = 0)
                           $emptyclause";
            $DB->execute($sql);

                        $sql = "INSERT INTO {context_temp} (id, path, depth)
                    SELECT ctx.id, ".$DB->sql_concat('pctx.path', "'/'", 'ctx.id').", pctx.depth+1
                      FROM {context} ctx
                      JOIN {course} c ON (c.id = ctx.instanceid AND ctx.contextlevel = ".CONTEXT_COURSE." AND c.category <> 0)
                      JOIN {context} pctx ON (pctx.instanceid = c.category AND pctx.contextlevel = ".CONTEXT_COURSECAT.")
                     WHERE pctx.path IS NOT NULL AND pctx.depth > 0
                           $ctxemptyclause";
            $trans = $DB->start_delegated_transaction();
            $DB->delete_records('context_temp');
            $DB->execute($sql);
            context::merge_context_temp_table();
            $DB->delete_records('context_temp');
            $trans->allow_commit();
        }
    }
}



class context_module extends context {
    
    protected function __construct(stdClass $record) {
        parent::__construct($record);
        if ($record->contextlevel != CONTEXT_MODULE) {
            throw new coding_exception('Invalid $record->contextlevel in context_module constructor.');
        }
    }

    
    public static function get_level_name() {
        return get_string('activitymodule');
    }

    
    public function get_context_name($withprefix = true, $short = false) {
        global $DB;

        $name = '';
        if ($cm = $DB->get_record_sql("SELECT cm.*, md.name AS modname
                                         FROM {course_modules} cm
                                         JOIN {modules} md ON md.id = cm.module
                                        WHERE cm.id = ?", array($this->_instanceid))) {
            if ($mod = $DB->get_record($cm->modname, array('id' => $cm->instance))) {
                    if ($withprefix){
                        $name = get_string('modulename', $cm->modname).': ';
                    }
                    $name .= format_string($mod->name, true, array('context' => $this));
                }
            }
        return $name;
    }

    
    public function get_url() {
        global $DB;

        if ($modname = $DB->get_field_sql("SELECT md.name AS modname
                                             FROM {course_modules} cm
                                             JOIN {modules} md ON md.id = cm.module
                                            WHERE cm.id = ?", array($this->_instanceid))) {
            return new moodle_url('/mod/' . $modname . '/view.php', array('id'=>$this->_instanceid));
        }

        return new moodle_url('/');
    }

    
    public function get_capabilities() {
        global $DB, $CFG;

        $sort = 'ORDER BY contextlevel,component,name';   
        $cm = $DB->get_record('course_modules', array('id'=>$this->_instanceid));
        $module = $DB->get_record('modules', array('id'=>$cm->module));

        $subcaps = array();
        $subpluginsfile = "$CFG->dirroot/mod/$module->name/db/subplugins.php";
        if (file_exists($subpluginsfile)) {
            $subplugins = array();              include($subpluginsfile);
            if (!empty($subplugins)) {
                foreach (array_keys($subplugins) as $subplugintype) {
                    foreach (array_keys(core_component::get_plugin_list($subplugintype)) as $subpluginname) {
                        $subcaps = array_merge($subcaps, array_keys(load_capability_def($subplugintype.'_'.$subpluginname)));
                    }
                }
            }
        }

        $modfile = "$CFG->dirroot/mod/$module->name/lib.php";
        $extracaps = array();
        if (file_exists($modfile)) {
            include_once($modfile);
            $modfunction = $module->name.'_get_extra_capabilities';
            if (function_exists($modfunction)) {
                $extracaps = $modfunction();
            }
        }

        $extracaps = array_merge($subcaps, $extracaps);
        $extra = '';
        list($extra, $params) = $DB->get_in_or_equal(
            $extracaps, SQL_PARAMS_NAMED, 'cap0', true, '');
        if (!empty($extra)) {
            $extra = "OR name $extra";
        }
        $sql = "SELECT *
                  FROM {capabilities}
                 WHERE (contextlevel = ".CONTEXT_MODULE."
                       AND (component = :component OR component = 'moodle'))
                       $extra";
        $params['component'] = "mod_$module->name";

        return $DB->get_records_sql($sql.' '.$sort, $params);
    }

    
    public function get_course_context($strict = true) {
        return $this->get_parent_context();
    }

    
    public static function instance($cmid, $strictness = MUST_EXIST) {
        global $DB;

        if ($context = context::cache_get(CONTEXT_MODULE, $cmid)) {
            return $context;
        }

        if (!$record = $DB->get_record('context', array('contextlevel' => CONTEXT_MODULE, 'instanceid' => $cmid))) {
            if ($cm = $DB->get_record('course_modules', array('id' => $cmid), 'id,course', $strictness)) {
                $parentcontext = context_course::instance($cm->course);
                $record = context::insert_context_record(CONTEXT_MODULE, $cm->id, $parentcontext->path);
            }
        }

        if ($record) {
            $context = new context_module($record);
            context::cache_add($context);
            return $context;
        }

        return false;
    }

    
    protected static function create_level_instances() {
        global $DB;

        $sql = "SELECT ".CONTEXT_MODULE.", cm.id
                  FROM {course_modules} cm
                 WHERE NOT EXISTS (SELECT 'x'
                                     FROM {context} cx
                                    WHERE cm.id = cx.instanceid AND cx.contextlevel=".CONTEXT_MODULE.")";
        $contextdata = $DB->get_recordset_sql($sql);
        foreach ($contextdata as $context) {
            context::insert_context_record(CONTEXT_MODULE, $context->id, null);
        }
        $contextdata->close();
    }

    
    protected static function get_cleanup_sql() {
        $sql = "
                  SELECT c.*
                    FROM {context} c
         LEFT OUTER JOIN {course_modules} cm ON c.instanceid = cm.id
                   WHERE cm.id IS NULL AND c.contextlevel = ".CONTEXT_MODULE."
               ";

        return $sql;
    }

    
    protected static function build_paths($force) {
        global $DB;

        if ($force or $DB->record_exists_select('context', "contextlevel = ".CONTEXT_MODULE." AND (depth = 0 OR path IS NULL)")) {
            if ($force) {
                $ctxemptyclause = '';
            } else {
                $ctxemptyclause = "AND (ctx.path IS NULL OR ctx.depth = 0)";
            }

            $sql = "INSERT INTO {context_temp} (id, path, depth)
                    SELECT ctx.id, ".$DB->sql_concat('pctx.path', "'/'", 'ctx.id').", pctx.depth+1
                      FROM {context} ctx
                      JOIN {course_modules} cm ON (cm.id = ctx.instanceid AND ctx.contextlevel = ".CONTEXT_MODULE.")
                      JOIN {context} pctx ON (pctx.instanceid = cm.course AND pctx.contextlevel = ".CONTEXT_COURSE.")
                     WHERE pctx.path IS NOT NULL AND pctx.depth > 0
                           $ctxemptyclause";
            $trans = $DB->start_delegated_transaction();
            $DB->delete_records('context_temp');
            $DB->execute($sql);
            context::merge_context_temp_table();
            $DB->delete_records('context_temp');
            $trans->allow_commit();
        }
    }
}



class context_block extends context {
    
    protected function __construct(stdClass $record) {
        parent::__construct($record);
        if ($record->contextlevel != CONTEXT_BLOCK) {
            throw new coding_exception('Invalid $record->contextlevel in context_block constructor');
        }
    }

    
    public static function get_level_name() {
        return get_string('block');
    }

    
    public function get_context_name($withprefix = true, $short = false) {
        global $DB, $CFG;

        $name = '';
        if ($blockinstance = $DB->get_record('block_instances', array('id'=>$this->_instanceid))) {
            global $CFG;
            require_once("$CFG->dirroot/blocks/moodleblock.class.php");
            require_once("$CFG->dirroot/blocks/$blockinstance->blockname/block_$blockinstance->blockname.php");
            $blockname = "block_$blockinstance->blockname";
            if ($blockobject = new $blockname()) {
                if ($withprefix){
                    $name = get_string('block').': ';
                }
                $name .= $blockobject->title;
            }
        }

        return $name;
    }

    
    public function get_url() {
        $parentcontexts = $this->get_parent_context();
        return $parentcontexts->get_url();
    }

    
    public function get_capabilities() {
        global $DB;

        $sort = 'ORDER BY contextlevel,component,name';   
        $params = array();
        $bi = $DB->get_record('block_instances', array('id' => $this->_instanceid));

        $extra = '';
        $extracaps = block_method_result($bi->blockname, 'get_extra_capabilities');
        if ($extracaps) {
            list($extra, $params) = $DB->get_in_or_equal($extracaps, SQL_PARAMS_NAMED, 'cap');
            $extra = "OR name $extra";
        }

        $sql = "SELECT *
                  FROM {capabilities}
                 WHERE (contextlevel = ".CONTEXT_BLOCK."
                       AND component = :component)
                       $extra";
        $params['component'] = 'block_' . $bi->blockname;

        return $DB->get_records_sql($sql.' '.$sort, $params);
    }

    
    public function get_course_context($strict = true) {
        $parentcontext = $this->get_parent_context();
        return $parentcontext->get_course_context($strict);
    }

    
    public static function instance($blockinstanceid, $strictness = MUST_EXIST) {
        global $DB;

        if ($context = context::cache_get(CONTEXT_BLOCK, $blockinstanceid)) {
            return $context;
        }

        if (!$record = $DB->get_record('context', array('contextlevel' => CONTEXT_BLOCK, 'instanceid' => $blockinstanceid))) {
            if ($bi = $DB->get_record('block_instances', array('id' => $blockinstanceid), 'id,parentcontextid', $strictness)) {
                $parentcontext = context::instance_by_id($bi->parentcontextid);
                $record = context::insert_context_record(CONTEXT_BLOCK, $bi->id, $parentcontext->path);
            }
        }

        if ($record) {
            $context = new context_block($record);
            context::cache_add($context);
            return $context;
        }

        return false;
    }

    
    public function get_child_contexts() {
        return array();
    }

    
    protected static function create_level_instances() {
        global $DB;

        $sql = "SELECT ".CONTEXT_BLOCK.", bi.id
                  FROM {block_instances} bi
                 WHERE NOT EXISTS (SELECT 'x'
                                     FROM {context} cx
                                    WHERE bi.id = cx.instanceid AND cx.contextlevel=".CONTEXT_BLOCK.")";
        $contextdata = $DB->get_recordset_sql($sql);
        foreach ($contextdata as $context) {
            context::insert_context_record(CONTEXT_BLOCK, $context->id, null);
        }
        $contextdata->close();
    }

    
    protected static function get_cleanup_sql() {
        $sql = "
                  SELECT c.*
                    FROM {context} c
         LEFT OUTER JOIN {block_instances} bi ON c.instanceid = bi.id
                   WHERE bi.id IS NULL AND c.contextlevel = ".CONTEXT_BLOCK."
               ";

        return $sql;
    }

    
    protected static function build_paths($force) {
        global $DB;

        if ($force or $DB->record_exists_select('context', "contextlevel = ".CONTEXT_BLOCK." AND (depth = 0 OR path IS NULL)")) {
            if ($force) {
                $ctxemptyclause = '';
            } else {
                $ctxemptyclause = "AND (ctx.path IS NULL OR ctx.depth = 0)";
            }

                        $sql = "INSERT INTO {context_temp} (id, path, depth)
                    SELECT ctx.id, ".$DB->sql_concat('pctx.path', "'/'", 'ctx.id').", pctx.depth+1
                      FROM {context} ctx
                      JOIN {block_instances} bi ON (bi.id = ctx.instanceid AND ctx.contextlevel = ".CONTEXT_BLOCK.")
                      JOIN {context} pctx ON (pctx.id = bi.parentcontextid)
                     WHERE (pctx.path IS NOT NULL AND pctx.depth > 0)
                           $ctxemptyclause";
            $trans = $DB->start_delegated_transaction();
            $DB->delete_records('context_temp');
            $DB->execute($sql);
            context::merge_context_temp_table();
            $DB->delete_records('context_temp');
            $trans->allow_commit();
        }
    }
}




function get_sorted_contexts($select, $params = array()) {

    
    global $DB;
    if ($select) {
        $select = 'WHERE ' . $select;
    }
    return $DB->get_records_sql("
            SELECT ctx.*
              FROM {context} ctx
              LEFT JOIN {user} u ON ctx.contextlevel = " . CONTEXT_USER . " AND u.id = ctx.instanceid
              LEFT JOIN {course_categories} cat ON ctx.contextlevel = " . CONTEXT_COURSECAT . " AND cat.id = ctx.instanceid
              LEFT JOIN {course} c ON ctx.contextlevel = " . CONTEXT_COURSE . " AND c.id = ctx.instanceid
              LEFT JOIN {course_modules} cm ON ctx.contextlevel = " . CONTEXT_MODULE . " AND cm.id = ctx.instanceid
              LEFT JOIN {block_instances} bi ON ctx.contextlevel = " . CONTEXT_BLOCK . " AND bi.id = ctx.instanceid
           $select
          ORDER BY ctx.contextlevel, bi.defaultregion, COALESCE(cat.sortorder, c.sortorder, cm.section, bi.defaultweight), u.lastname, u.firstname, cm.id
            ", $params);
}


function extract_suspended_users($context, &$users, $ignoreusers=array()) {
    global $DB;

        list($sql, $params) = get_enrolled_sql($context, null, null, true);
    $activeusers = $DB->get_records_sql($sql, $params);

        $susers = array();
    if (sizeof($activeusers)) {
        foreach ($users as $userid => $user) {
            if (!array_key_exists($userid, $activeusers) && !in_array($userid, $ignoreusers)) {
                $susers[$userid] = $user;
                unset($users[$userid]);
            }
        }
    }
    return $susers;
}


function get_suspended_userids(context $context, $usecache = false) {
    global $DB;

    if ($usecache) {
        $cache = cache::make('core', 'suspended_userids');
        $susers = $cache->get($context->id);
        if ($susers !== false) {
            return $susers;
        }
    }

    $coursecontext = $context->get_course_context();
    $susers = array();

        if ($coursecontext->instanceid != SITEID) {
        list($sql, $params) = get_enrolled_sql($context, null, null, false, true);
        $susers = $DB->get_fieldset_sql($sql, $params);
        $susers = array_combine($susers, $susers);
    }

        if ($usecache) {
        $cache->set($context->id, $susers);
    }

    return $susers;
}
