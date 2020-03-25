<?php




defined('MOODLE_INTERNAL') || die();


define('ENROL_INSTANCE_ENABLED', 0);


define('ENROL_INSTANCE_DISABLED', 1);


define('ENROL_USER_ACTIVE', 0);


define('ENROL_USER_SUSPENDED', 1);


define('ENROL_REQUIRE_LOGIN_CACHE_PERIOD', 1800);


define('ENROL_MAX_TIMESTAMP', 2147483647);


define('ENROL_EXT_REMOVED_UNENROL', 0);


define('ENROL_EXT_REMOVED_KEEP', 1);


define('ENROL_RESTORE_TYPE', 'enrolrestore');


define('ENROL_EXT_REMOVED_SUSPEND', 2);


define('ENROL_EXT_REMOVED_SUSPENDNOROLES', 3);


function enrol_get_plugins($enabled) {
    global $CFG;

    $result = array();

    if ($enabled) {
                $enabled = explode(',', $CFG->enrol_plugins_enabled);
        $plugins = array();
        foreach ($enabled as $plugin) {
            $plugins[$plugin] = "$CFG->dirroot/enrol/$plugin";
        }
    } else {
                $plugins = core_component::get_plugin_list('enrol');
        ksort($plugins);
    }

    foreach ($plugins as $plugin=>$location) {
        $class = "enrol_{$plugin}_plugin";
        if (!class_exists($class)) {
            if (!file_exists("$location/lib.php")) {
                continue;
            }
            include_once("$location/lib.php");
            if (!class_exists($class)) {
                continue;
            }
        }

        $result[$plugin] = new $class();
    }

    return $result;
}


function enrol_get_plugin($name) {
    global $CFG;

    $name = clean_param($name, PARAM_PLUGIN);

    if (empty($name)) {
                return null;
    }

    $location = "$CFG->dirroot/enrol/$name";

    $class = "enrol_{$name}_plugin";
    if (!class_exists($class)) {
        if (!file_exists("$location/lib.php")) {
            return null;
        }
        include_once("$location/lib.php");
        if (!class_exists($class)) {
            return null;
        }
    }

    return new $class();
}


function enrol_get_instances($courseid, $enabled) {
    global $DB, $CFG;

    if (!$enabled) {
        return $DB->get_records('enrol', array('courseid'=>$courseid), 'sortorder,id');
    }

    $result = $DB->get_records('enrol', array('courseid'=>$courseid, 'status'=>ENROL_INSTANCE_ENABLED), 'sortorder,id');

    $enabled = explode(',', $CFG->enrol_plugins_enabled);
    foreach ($result as $key=>$instance) {
        if (!in_array($instance->enrol, $enabled)) {
            unset($result[$key]);
            continue;
        }
        if (!file_exists("$CFG->dirroot/enrol/$instance->enrol/lib.php")) {
                        unset($result[$key]);
            continue;
        }
    }

    return $result;
}


function enrol_is_enabled($enrol) {
    global $CFG;

    if (empty($CFG->enrol_plugins_enabled)) {
        return false;
    }
    return in_array($enrol, explode(',', $CFG->enrol_plugins_enabled));
}


function enrol_check_plugins($user) {
    global $CFG;

    if (empty($user->id) or isguestuser($user)) {
                return;
    }

        
    static $inprogress = array();  
    if (!empty($inprogress[$user->id])) {
        return;
    }

    $inprogress[$user->id] = true;  
    $enabled = enrol_get_plugins(true);

    foreach($enabled as $enrol) {
        $enrol->sync_user_enrolments($user);
    }

    unset($inprogress[$user->id]);  }


function enrol_sharing_course($user1, $user2) {
    return enrol_get_shared_courses($user1, $user2, false, true);
}


function enrol_get_shared_courses($user1, $user2, $preloadcontexts = false, $checkexistsonly = false) {
    global $DB, $CFG;

    $user1 = isset($user1->id) ? $user1->id : $user1;
    $user2 = isset($user2->id) ? $user2->id : $user2;

    if (empty($user1) or empty($user2)) {
        return false;
    }

    if (!$plugins = explode(',', $CFG->enrol_plugins_enabled)) {
        return false;
    }

    list($plugins, $params) = $DB->get_in_or_equal($plugins, SQL_PARAMS_NAMED, 'ee');
    $params['enabled'] = ENROL_INSTANCE_ENABLED;
    $params['active1'] = ENROL_USER_ACTIVE;
    $params['active2'] = ENROL_USER_ACTIVE;
    $params['user1']   = $user1;
    $params['user2']   = $user2;

    $ctxselect = '';
    $ctxjoin = '';
    if ($preloadcontexts) {
        $ctxselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
        $ctxjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
        $params['contextlevel'] = CONTEXT_COURSE;
    }

    $sql = "SELECT c.* $ctxselect
              FROM {course} c
              JOIN (
                SELECT DISTINCT c.id
                  FROM {enrol} e
                  JOIN {user_enrolments} ue1 ON (ue1.enrolid = e.id AND ue1.status = :active1 AND ue1.userid = :user1)
                  JOIN {user_enrolments} ue2 ON (ue2.enrolid = e.id AND ue2.status = :active2 AND ue2.userid = :user2)
                  JOIN {course} c ON (c.id = e.courseid AND c.visible = 1)
                 WHERE e.status = :enabled AND e.enrol $plugins
              ) ec ON ec.id = c.id
              $ctxjoin";

    if ($checkexistsonly) {
        return $DB->record_exists_sql($sql, $params);
    } else {
        $courses = $DB->get_records_sql($sql, $params);
        if ($preloadcontexts) {
            array_map('context_helper::preload_from_record', $courses);
        }
        return $courses;
    }
}


function enrol_course_edit_form(MoodleQuickForm $mform, $data, $context) {
    $plugins = enrol_get_plugins(true);
    if (!empty($data->id)) {
        $instances = enrol_get_instances($data->id, false);
        foreach ($instances as $instance) {
            if (!isset($plugins[$instance->enrol])) {
                continue;
            }
            $plugin = $plugins[$instance->enrol];
            $plugin->course_edit_form($instance, $mform, $data, $context);
        }
    } else {
        foreach ($plugins as $plugin) {
            $plugin->course_edit_form(NULL, $mform, $data, $context);
        }
    }
}


function enrol_course_edit_validation(array $data, $context) {
    $errors = array();
    $plugins = enrol_get_plugins(true);

    if (!empty($data['id'])) {
        $instances = enrol_get_instances($data['id'], false);
        foreach ($instances as $instance) {
            if (!isset($plugins[$instance->enrol])) {
                continue;
            }
            $plugin = $plugins[$instance->enrol];
            $errors = array_merge($errors, $plugin->course_edit_validation($instance, $data, $context));
        }
    } else {
        foreach ($plugins as $plugin) {
            $errors = array_merge($errors, $plugin->course_edit_validation(NULL, $data, $context));
        }
    }

    return $errors;
}


function enrol_course_updated($inserted, $course, $data) {
    global $DB, $CFG;

    $plugins = enrol_get_plugins(true);

    foreach ($plugins as $plugin) {
        $plugin->course_updated($inserted, $course, $data);
    }
}


function enrol_add_course_navigation(navigation_node $coursenode, $course) {
    global $CFG;

    $coursecontext = context_course::instance($course->id);

    $instances = enrol_get_instances($course->id, true);
    $plugins   = enrol_get_plugins(true);

        foreach ($instances as $k=>$instance) {
        if (!isset($plugins[$instance->enrol])) {
            unset($instances[$k]);
        }
    }

    $usersnode = $coursenode->add(get_string('users'), null, navigation_node::TYPE_CONTAINER, null, 'users');

    if ($course->id != SITEID) {
                if (has_capability('moodle/course:enrolreview', $coursecontext)) {
            $url = new moodle_url('/enrol/users.php', array('id'=>$course->id));
            $usersnode->add(get_string('enrolledusers', 'enrol'), $url, navigation_node::TYPE_SETTING, null, 'review', new pix_icon('i/enrolusers', ''));
        }

                if (has_capability('moodle/course:enrolconfig', $coursecontext) or has_capability('moodle/course:enrolreview', $coursecontext)) {
            $url = new moodle_url('/enrol/instances.php', array('id'=>$course->id));
        } else {
            $url = NULL;
        }
        $instancesnode = $usersnode->add(get_string('enrolmentinstances', 'enrol'), $url, navigation_node::TYPE_SETTING, null, 'manageinstances');

                foreach ($instances as $instance) {
            if (!isset($plugins[$instance->enrol])) {
                continue;
            }
            $plugins[$instance->enrol]->add_course_navigation($instancesnode, $instance);
        }

        if (!$url) {
            $instancesnode->trim_if_empty();
        }
    }

        if (($course->groupmode || !$course->groupmodeforce) && has_capability('moodle/course:managegroups', $coursecontext)) {
        $url = new moodle_url('/group/index.php', array('id'=>$course->id));
        $usersnode->add(get_string('groups'), $url, navigation_node::TYPE_SETTING, null, 'groups', new pix_icon('i/group', ''));
    }

     if (has_any_capability(array( 'moodle/role:assign', 'moodle/role:safeoverride','moodle/role:override', 'moodle/role:review'), $coursecontext)) {
                if (has_capability('moodle/role:review', $coursecontext)) {
            $url = new moodle_url('/admin/roles/permissions.php', array('contextid'=>$coursecontext->id));
        } else {
            $url = NULL;
        }
        $permissionsnode = $usersnode->add(get_string('permissions', 'role'), $url, navigation_node::TYPE_SETTING, null, 'override');

                if ($course->id == SITEID or (!empty($CFG->adminsassignrolesincourse) and is_siteadmin())) {
            if (has_capability('moodle/role:assign', $coursecontext)) {
                $url = new moodle_url('/admin/roles/assign.php', array('contextid'=>$coursecontext->id));
                $permissionsnode->add(get_string('assignedroles', 'role'), $url, navigation_node::TYPE_SETTING, null, 'roles', new pix_icon('i/assignroles', ''));
            }
        }
                if (has_any_capability(array('moodle/role:assign', 'moodle/role:safeoverride', 'moodle/role:override'), $coursecontext)) {
            $url = new moodle_url('/admin/roles/check.php', array('contextid'=>$coursecontext->id));
            $permissionsnode->add(get_string('checkpermissions', 'role'), $url, navigation_node::TYPE_SETTING, null, 'permissions', new pix_icon('i/checkpermissions', ''));
        }
     }

         if ($course->id != SITEID) {
                if (has_capability('moodle/course:reviewotherusers', $coursecontext)) {
            $url = new moodle_url('/enrol/otherusers.php', array('id'=>$course->id));
            $usersnode->add(get_string('notenrolledusers', 'enrol'), $url, navigation_node::TYPE_SETTING, null, 'otherusers', new pix_icon('i/assignroles', ''));
        }
    }

        $usersnode->trim_if_empty();

    if ($course->id != SITEID) {
        if (isguestuser() or !isloggedin()) {
                    } else if (is_enrolled($coursecontext)) {
                        foreach ($instances as $instance) {
                if (!isset($plugins[$instance->enrol])) {
                    continue;
                }
                $plugin = $plugins[$instance->enrol];
                if ($unenrollink = $plugin->get_unenrolself_link($instance)) {
                    $shortname = format_string($course->shortname, true, array('context' => $coursecontext));
                    $coursenode->add(get_string('unenrolme', 'core_enrol', $shortname), $unenrollink, navigation_node::TYPE_SETTING, null, 'unenrolself', new pix_icon('i/user', ''));
                    break;
                                    }
            }
        } else {
                        if (is_viewing($coursecontext)) {
                            } else {
                foreach ($instances as $instance) {
                    if (!isset($plugins[$instance->enrol])) {
                        continue;
                    }
                    $plugin = $plugins[$instance->enrol];
                    if ($plugin->show_enrolme_link($instance)) {
                        $url = new moodle_url('/enrol/index.php', array('id'=>$course->id));
                        $shortname = format_string($course->shortname, true, array('context' => $coursecontext));
                        $coursenode->add(get_string('enrolme', 'core_enrol', $shortname), $url, navigation_node::TYPE_SETTING, null, 'enrolself', new pix_icon('i/user', ''));
                        break;
                    }
                }
            }
        }
    }
}


function enrol_get_my_courses($fields = NULL, $sort = 'visible DESC,sortorder ASC', $limit = 0) {
    global $DB, $USER;

        if (isguestuser() or !isloggedin()) {
        return(array());
    }

    $basefields = array('id', 'category', 'sortorder',
                        'shortname', 'fullname', 'idnumber',
                        'startdate', 'visible',
                        'groupmode', 'groupmodeforce', 'cacherev');

    if (empty($fields)) {
        $fields = $basefields;
    } else if (is_string($fields)) {
                $fields = explode(',', $fields);
        $fields = array_map('trim', $fields);
        $fields = array_unique(array_merge($basefields, $fields));
    } else if (is_array($fields)) {
        $fields = array_unique(array_merge($basefields, $fields));
    } else {
        throw new coding_exception('Invalid $fileds parameter in enrol_get_my_courses()');
    }
    if (in_array('*', $fields)) {
        $fields = array('*');
    }

    $orderby = "";
    $sort    = trim($sort);
    if (!empty($sort)) {
        $rawsorts = explode(',', $sort);
        $sorts = array();
        foreach ($rawsorts as $rawsort) {
            $rawsort = trim($rawsort);
            if (strpos($rawsort, 'c.') === 0) {
                $rawsort = substr($rawsort, 2);
            }
            $sorts[] = trim($rawsort);
        }
        $sort = 'c.'.implode(',c.', $sorts);
        $orderby = "ORDER BY $sort";
    }

    $wheres = array("c.id <> :siteid");
    $params = array('siteid'=>SITEID);

    if (isset($USER->loginascontext) and $USER->loginascontext->contextlevel == CONTEXT_COURSE) {
                $wheres[] = "courseid = :loginas";
        $params['loginas'] = $USER->loginascontext->instanceid;
    }

    $coursefields = 'c.' .join(',c.', $fields);
    $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
    $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
    $params['contextlevel'] = CONTEXT_COURSE;
    $wheres = implode(" AND ", $wheres);

        $sql = "SELECT $coursefields $ccselect
              FROM {course} c
              JOIN (SELECT DISTINCT e.courseid
                      FROM {enrol} e
                      JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)
                     WHERE ue.status = :active AND e.status = :enabled AND ue.timestart < :now1 AND (ue.timeend = 0 OR ue.timeend > :now2)
                   ) en ON (en.courseid = c.id)
           $ccjoin
             WHERE $wheres
          $orderby";
    $params['userid']  = $USER->id;
    $params['active']  = ENROL_USER_ACTIVE;
    $params['enabled'] = ENROL_INSTANCE_ENABLED;
    $params['now1']    = round(time(), -2);     $params['now2']    = $params['now1'];

    $courses = $DB->get_records_sql($sql, $params, 0, $limit);

        foreach ($courses as $id=>$course) {
        context_helper::preload_from_record($course);
        if (!$course->visible) {
            if (!$context = context_course::instance($id, IGNORE_MISSING)) {
                unset($courses[$id]);
                continue;
            }
            if (!has_capability('moodle/course:viewhiddencourses', $context)) {
                unset($courses[$id]);
                continue;
            }
        }
        $courses[$id] = $course;
    }

    
    return $courses;
}


function enrol_get_course_info_icons($course, array $instances = NULL) {
    $icons = array();
    if (is_null($instances)) {
        $instances = enrol_get_instances($course->id, true);
    }
    $plugins = enrol_get_plugins(true);
    foreach ($plugins as $name => $plugin) {
        $pis = array();
        foreach ($instances as $instance) {
            if ($instance->status != ENROL_INSTANCE_ENABLED or $instance->courseid != $course->id) {
                debugging('Invalid instances parameter submitted in enrol_get_info_icons()');
                continue;
            }
            if ($instance->enrol == $name) {
                $pis[$instance->id] = $instance;
            }
        }
        if ($pis) {
            $icons = array_merge($icons, $plugin->get_info_icons($pis));
        }
    }
    return $icons;
}


function enrol_get_course_description_texts($course) {
    $lines = array();
    $instances = enrol_get_instances($course->id, true);
    $plugins = enrol_get_plugins(true);
    foreach ($instances as $instance) {
        if (!isset($plugins[$instance->enrol])) {
                        continue;
        }
        $plugin = $plugins[$instance->enrol];
        $text = $plugin->get_description_text($instance);
        if ($text !== NULL) {
            $lines[] = $text;
        }
    }
    return $lines;
}


function enrol_get_users_courses($userid, $onlyactive = false, $fields = NULL, $sort = 'visible DESC,sortorder ASC') {
    global $DB;

    $courses = enrol_get_all_users_courses($userid, $onlyactive, $fields, $sort);

        if ($onlyactive) {
        foreach ($courses as $id=>$course) {
            context_helper::preload_from_record($course);
            if (!$course->visible) {
                if (!$context = context_course::instance($id)) {
                    unset($courses[$id]);
                    continue;
                }
                if (!has_capability('moodle/course:viewhiddencourses', $context, $userid)) {
                    unset($courses[$id]);
                    continue;
                }
            }
        }
    }

    return $courses;

}


function enrol_user_sees_own_courses($user = null) {
    global $USER;

    if ($user === null) {
        $user = $USER;
    }
    $userid = is_object($user) ? $user->id : $user;

        if (isguestuser($userid) or empty($userid)) {
        return false;
    }

                if ($USER->id == $userid) {
        if (!empty($USER->enrol['enrolled'])) {
            foreach ($USER->enrol['enrolled'] as $until) {
                if ($until > time()) {
                    return true;
                }
            }
        }
    }

        $courses = enrol_get_all_users_courses($userid, true);
    foreach($courses as $course) {
        if ($course->visible) {
            return true;
        }
        context_helper::preload_from_record($course);
        $context = context_course::instance($course->id);
        if (has_capability('moodle/course:viewhiddencourses', $context, $user)) {
            return true;
        }
    }

    return false;
}


function enrol_get_all_users_courses($userid, $onlyactive = false, $fields = NULL, $sort = 'visible DESC,sortorder ASC') {
    global $DB;

        if (isguestuser($userid) or empty($userid)) {
        return(array());
    }

    $basefields = array('id', 'category', 'sortorder',
            'shortname', 'fullname', 'idnumber',
            'startdate', 'visible',
            'defaultgroupingid',
            'groupmode', 'groupmodeforce');

    if (empty($fields)) {
        $fields = $basefields;
    } else if (is_string($fields)) {
                $fields = explode(',', $fields);
        $fields = array_map('trim', $fields);
        $fields = array_unique(array_merge($basefields, $fields));
    } else if (is_array($fields)) {
        $fields = array_unique(array_merge($basefields, $fields));
    } else {
        throw new coding_exception('Invalid $fileds parameter in enrol_get_my_courses()');
    }
    if (in_array('*', $fields)) {
        $fields = array('*');
    }

    $orderby = "";
    $sort    = trim($sort);
    if (!empty($sort)) {
        $rawsorts = explode(',', $sort);
        $sorts = array();
        foreach ($rawsorts as $rawsort) {
            $rawsort = trim($rawsort);
            if (strpos($rawsort, 'c.') === 0) {
                $rawsort = substr($rawsort, 2);
            }
            $sorts[] = trim($rawsort);
        }
        $sort = 'c.'.implode(',c.', $sorts);
        $orderby = "ORDER BY $sort";
    }

    $params = array('siteid'=>SITEID);

    if ($onlyactive) {
        $subwhere = "WHERE ue.status = :active AND e.status = :enabled AND ue.timestart < :now1 AND (ue.timeend = 0 OR ue.timeend > :now2)";
        $params['now1']    = round(time(), -2);         $params['now2']    = $params['now1'];
        $params['active']  = ENROL_USER_ACTIVE;
        $params['enabled'] = ENROL_INSTANCE_ENABLED;
    } else {
        $subwhere = "";
    }

    $coursefields = 'c.' .join(',c.', $fields);
    $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
    $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
    $params['contextlevel'] = CONTEXT_COURSE;

        $sql = "SELECT $coursefields $ccselect
              FROM {course} c
              JOIN (SELECT DISTINCT e.courseid
                      FROM {enrol} e
                      JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)
                 $subwhere
                   ) en ON (en.courseid = c.id)
           $ccjoin
             WHERE c.id <> :siteid
          $orderby";
    $params['userid']  = $userid;

    $courses = $DB->get_records_sql($sql, $params);

    return $courses;
}




function enrol_user_delete($user) {
    global $DB;

    $plugins = enrol_get_plugins(true);
    foreach ($plugins as $plugin) {
        $plugin->user_delete($user);
    }

        $DB->delete_records('user_enrolments', array('userid'=>$user->id));
}


function enrol_course_delete($course) {
    global $DB;

    $instances = enrol_get_instances($course->id, false);
    $plugins = enrol_get_plugins(true);
    foreach ($instances as $instance) {
        if (isset($plugins[$instance->enrol])) {
            $plugins[$instance->enrol]->delete_instance($instance);
        }
                $DB->delete_records('user_enrolments', array('enrolid'=>$instance->id));
        $DB->delete_records('role_assignments', array('itemid'=>$instance->id, 'component'=>'enrol_'.$instance->enrol));
        $DB->delete_records('user_enrolments', array('enrolid'=>$instance->id));
        $DB->delete_records('enrol', array('id'=>$instance->id));
    }
}


function enrol_try_internal_enrol($courseid, $userid, $roleid = null, $timestart = 0, $timeend = 0) {
    global $DB;

    
    if (!enrol_is_enabled('manual')) {
        return false;
    }

    if (!$enrol = enrol_get_plugin('manual')) {
        return false;
    }
    if (!$instances = $DB->get_records('enrol', array('enrol'=>'manual', 'courseid'=>$courseid, 'status'=>ENROL_INSTANCE_ENABLED), 'sortorder,id ASC')) {
        return false;
    }
    $instance = reset($instances);

    $enrol->enrol_user($instance, $userid, $roleid, $timestart, $timeend);

    return true;
}


function enrol_selfenrol_available($courseid) {
    $result = false;

    $plugins = enrol_get_plugins(true);
    $enrolinstances = enrol_get_instances($courseid, true);
    foreach($enrolinstances as $instance) {
        if (!isset($plugins[$instance->enrol])) {
            continue;
        }
        if ($instance->enrol === 'guest') {
                        continue;
        }
        if ($plugins[$instance->enrol]->show_enrolme_link($instance)) {
            $result = true;
            break;
        }
    }

    return $result;
}


function enrol_get_enrolment_end($courseid, $userid) {
    global $DB;

    $sql = "SELECT ue.*
              FROM {user_enrolments} ue
              JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = :courseid)
              JOIN {user} u ON u.id = ue.userid
             WHERE ue.userid = :userid AND ue.status = :active AND e.status = :enabled AND u.deleted = 0";
    $params = array('enabled'=>ENROL_INSTANCE_ENABLED, 'active'=>ENROL_USER_ACTIVE, 'userid'=>$userid, 'courseid'=>$courseid);

    if (!$enrolments = $DB->get_records_sql($sql, $params)) {
        return false;
    }

    $changes = array();

    foreach ($enrolments as $ue) {
        $start = (int)$ue->timestart;
        $end = (int)$ue->timeend;
        if ($end != 0 and $end < $start) {
            debugging('Invalid enrolment start or end in user_enrolment id:'.$ue->id);
            continue;
        }
        if (isset($changes[$start])) {
            $changes[$start] = $changes[$start] + 1;
        } else {
            $changes[$start] = 1;
        }
        if ($end === 0) {
                    } else if (isset($changes[$end])) {
            $changes[$end] = $changes[$end] - 1;
        } else {
            $changes[$end] = -1;
        }
    }

            ksort($changes);

    $now = time();
    $current = 0;
    $present = null;

    foreach ($changes as $time => $change) {
        if ($time > $now) {
            if ($present === null) {
                                $present = $current;
                if ($present < 1) {
                                        return false;
                }
            }
            if ($present !== null) {
                                if ($current + $change < 1) {
                    return $time;
                }
            }
        }
        $current += $change;
    }

    if ($current > 0) {
        return 0;
    } else {
        return false;
    }
}


function enrol_accessing_via_instance(stdClass $instance) {
    global $DB, $USER;

    if (empty($instance->id)) {
        return false;
    }

    if (is_siteadmin()) {
                return false;
    }

    return $DB->record_exists('user_enrolments', array('userid'=>$USER->id, 'enrolid'=>$instance->id));
}



abstract class enrol_plugin {
    protected $config = null;

    
    public function get_name() {
                $words = explode('_', get_class($this));
        return $words[1];
    }

    
    public function get_instance_name($instance) {
        if (empty($instance->name)) {
            $enrol = $this->get_name();
            return get_string('pluginname', 'enrol_'.$enrol);
        } else {
            $context = context_course::instance($instance->courseid);
            return format_string($instance->name, true, array('context'=>$context));
        }
    }

    
    public function get_info_icons(array $instances) {
        return array();
    }

    
    public function get_description_text($instance) {
        return null;
    }

    
    protected function load_config() {
        if (!isset($this->config)) {
            $name = $this->get_name();
            $this->config = get_config("enrol_$name");
        }
    }

    
    public function get_config($name, $default = NULL) {
        $this->load_config();
        return isset($this->config->$name) ? $this->config->$name : $default;
    }

    
    public function set_config($name, $value) {
        $pluginname = $this->get_name();
        $this->load_config();
        if ($value === NULL) {
            unset($this->config->$name);
        } else {
            $this->config->$name = $value;
        }
        set_config($name, $value, "enrol_$pluginname");
    }

    
    public function roles_protected() {
        return true;
    }

    
    public function allow_enrol(stdClass $instance) {
        return false;
    }

    
    public function allow_unenrol(stdClass $instance) {
        return false;
    }

    
    public function allow_unenrol_user(stdClass $instance, stdClass $ue) {
        return $this->allow_unenrol($instance);
    }

    
    public function allow_manage(stdClass $instance) {
        return false;
    }

    
    public function show_enrolme_link(stdClass $instance) {
        return false;
    }

    
    public function try_autoenrol(stdClass $instance) {
        global $USER;

        return false;
    }

    
    public function try_guestaccess(stdClass $instance) {
        global $USER;

        return false;
    }

    
    public function enrol_user(stdClass $instance, $userid, $roleid = null, $timestart = 0, $timeend = 0, $status = null, $recovergrades = null) {
        global $DB, $USER, $CFG; 
        if ($instance->courseid == SITEID) {
            throw new coding_exception('invalid attempt to enrol into frontpage course!');
        }

        $name = $this->get_name();
        $courseid = $instance->courseid;

        if ($instance->enrol !== $name) {
            throw new coding_exception('invalid enrol instance!');
        }
        $context = context_course::instance($instance->courseid, MUST_EXIST);
        if (!isset($recovergrades)) {
            $recovergrades = $CFG->recovergradesdefault;
        }

        $inserted = false;
        $updated  = false;
        if ($ue = $DB->get_record('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$userid))) {
                        if ($ue->timestart != $timestart or $ue->timeend != $timeend or (!is_null($status) and $ue->status != $status)) {
                $this->update_user_enrol($instance, $userid, $status, $timestart, $timeend);
            }
        } else {
            $ue = new stdClass();
            $ue->enrolid      = $instance->id;
            $ue->status       = is_null($status) ? ENROL_USER_ACTIVE : $status;
            $ue->userid       = $userid;
            $ue->timestart    = $timestart;
            $ue->timeend      = $timeend;
            $ue->modifierid   = $USER->id;
            $ue->timecreated  = time();
            $ue->timemodified = $ue->timecreated;
            $ue->id = $DB->insert_record('user_enrolments', $ue);

            $inserted = true;
        }

        if ($inserted) {
                        $event = \core\event\user_enrolment_created::create(
                    array(
                        'objectid' => $ue->id,
                        'courseid' => $courseid,
                        'context' => $context,
                        'relateduserid' => $ue->userid,
                        'other' => array('enrol' => $name)
                        )
                    );
            $event->trigger();
                        require_once($CFG->libdir . '/coursecatlib.php');
            coursecat::user_enrolment_changed($courseid, $ue->userid,
                    $ue->status, $ue->timestart, $ue->timeend);
        }

        if ($roleid) {
                        if ($this->roles_protected()) {
                role_assign($roleid, $userid, $context->id, 'enrol_'.$name, $instance->id);
            } else {
                role_assign($roleid, $userid, $context->id);
            }
        }

                if ($recovergrades) {
            require_once("$CFG->libdir/gradelib.php");
            grade_recover_history_grades($userid, $courseid);
        }

                if ($userid == $USER->id) {
            if (isset($USER->enrol['enrolled'][$courseid])) {
                unset($USER->enrol['enrolled'][$courseid]);
            }
            if (isset($USER->enrol['tempguest'][$courseid])) {
                unset($USER->enrol['tempguest'][$courseid]);
                remove_temp_course_roles($context);
            }
        }
    }

    
    public function update_user_enrol(stdClass $instance, $userid, $status = NULL, $timestart = NULL, $timeend = NULL) {
        global $DB, $USER, $CFG;

        $name = $this->get_name();

        if ($instance->enrol !== $name) {
            throw new coding_exception('invalid enrol instance!');
        }

        if (!$ue = $DB->get_record('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$userid))) {
                        return;
        }

        $modified = false;
        if (isset($status) and $ue->status != $status) {
            $ue->status = $status;
            $modified = true;
        }
        if (isset($timestart) and $ue->timestart != $timestart) {
            $ue->timestart = $timestart;
            $modified = true;
        }
        if (isset($timeend) and $ue->timeend != $timeend) {
            $ue->timeend = $timeend;
            $modified = true;
        }

        if (!$modified) {
                        return;
        }

        $ue->modifierid = $USER->id;
        $DB->update_record('user_enrolments', $ue);
        context_course::instance($instance->courseid)->mark_dirty(); 
                cache_helper::invalidate_by_definition('core', 'suspended_userids', array(), array($instance->courseid));

                $event = \core\event\user_enrolment_updated::create(
                array(
                    'objectid' => $ue->id,
                    'courseid' => $instance->courseid,
                    'context' => context_course::instance($instance->courseid),
                    'relateduserid' => $ue->userid,
                    'other' => array('enrol' => $name)
                    )
                );
        $event->trigger();

        require_once($CFG->libdir . '/coursecatlib.php');
        coursecat::user_enrolment_changed($instance->courseid, $ue->userid,
                $ue->status, $ue->timestart, $ue->timeend);
    }

    
    public function unenrol_user(stdClass $instance, $userid) {
        global $CFG, $USER, $DB;
        require_once("$CFG->dirroot/group/lib.php");

        $name = $this->get_name();
        $courseid = $instance->courseid;

        if ($instance->enrol !== $name) {
            throw new coding_exception('invalid enrol instance!');
        }
        $context = context_course::instance($instance->courseid, MUST_EXIST);

        if (!$ue = $DB->get_record('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$userid))) {
                        return;
        }

                if ($gms = $DB->get_records('groups_members', array('userid'=>$userid, 'component'=>'enrol_'.$name, 'itemid'=>$instance->id))) {
            foreach ($gms as $gm) {
                groups_remove_member($gm->groupid, $gm->userid);
            }
        }

        role_unassign_all(array('userid'=>$userid, 'contextid'=>$context->id, 'component'=>'enrol_'.$name, 'itemid'=>$instance->id));
        $DB->delete_records('user_enrolments', array('id'=>$ue->id));

                $ue->courseid  = $courseid;
        $ue->enrol     = $name;

        $sql = "SELECT 'x'
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON (e.id = ue.enrolid)
                 WHERE ue.userid = :userid AND e.courseid = :courseid";
        if ($DB->record_exists_sql($sql, array('userid'=>$userid, 'courseid'=>$courseid))) {
            $ue->lastenrol = false;

        } else {
                        require_once("$CFG->libdir/gradelib.php");

                        role_unassign_all(array('userid'=>$userid, 'contextid'=>$context->id), true, false);

                        groups_delete_group_members($courseid, $userid);

            grade_user_unenrol($courseid, $userid);

            $DB->delete_records('user_lastaccess', array('userid'=>$userid, 'courseid'=>$courseid));

            $ue->lastenrol = true;         }
                $event = \core\event\user_enrolment_deleted::create(
                array(
                    'courseid' => $courseid,
                    'context' => $context,
                    'relateduserid' => $ue->userid,
                    'objectid' => $ue->id,
                    'other' => array(
                        'userenrolment' => (array)$ue,
                        'enrol' => $name
                        )
                    )
                );
        $event->trigger();
                $context->mark_dirty();

                require_once($CFG->libdir . '/coursecatlib.php');
        coursecat::user_enrolment_changed($courseid, $ue->userid, ENROL_USER_SUSPENDED);

                if ($userid == $USER->id) {
            if (isset($USER->enrol['enrolled'][$courseid])) {
                unset($USER->enrol['enrolled'][$courseid]);
            }
            if (isset($USER->enrol['tempguest'][$courseid])) {
                unset($USER->enrol['tempguest'][$courseid]);
                remove_temp_course_roles($context);
            }
        }
    }

    
    public function sync_user_enrolments($user) {
            }

    
    public function use_standard_editing_ui() {
        return false;
    }

    
    public function can_add_instance($courseid) {
        $link = $this->get_newinstance_link($courseid);
        return !empty($link);
    }

    
    public function can_edit_instance($instance) {
        $context = context_course::instance($instance->courseid);

        return has_capability('enrol/' . $instance->enrol . ':config', $context);
    }

    
    public function get_newinstance_link($courseid) {
                return NULL;
    }

    
    public function instance_deleteable($instance) {
        throw new coding_exception('Function enrol_plugin::instance_deleteable() is deprecated, use
                enrol_plugin::can_delete_instance() instead');
    }

    
    public function can_delete_instance($instance) {
        return false;
    }

    
    public function can_hide_show_instance($instance) {
        debugging("The enrolment plugin '".$this->get_name()."' should override the function can_hide_show_instance().", DEBUG_DEVELOPER);
        return true;
    }

    
    public function get_manual_enrol_link($instance) {
        return NULL;
    }

    
    public function get_unenrolself_link($instance) {
        global $USER, $CFG, $DB;

        $name = $this->get_name();
        if ($instance->enrol !== $name) {
            throw new coding_exception('invalid enrol instance!');
        }

        if ($instance->courseid == SITEID) {
            return NULL;
        }

        if (!enrol_is_enabled($name)) {
            return NULL;
        }

        if ($instance->status != ENROL_INSTANCE_ENABLED) {
            return NULL;
        }

        if (!file_exists("$CFG->dirroot/enrol/$name/unenrolself.php")) {
            return NULL;
        }

        $context = context_course::instance($instance->courseid, MUST_EXIST);

        if (!has_capability("enrol/$name:unenrolself", $context)) {
            return NULL;
        }

        if (!$DB->record_exists('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$USER->id, 'status'=>ENROL_USER_ACTIVE))) {
            return NULL;
        }

        return new moodle_url("/enrol/$name/unenrolself.php", array('enrolid'=>$instance->id));
    }

    
    public function course_edit_form($instance, MoodleQuickForm $mform, $data, $context) {
            }

    
    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {
            }

    
    public function edit_instance_validation($data, $files, $instance, $context) {
                debugging('enrol_plugin::edit_instance_validation() is missing. This plugin has no validation!', DEBUG_DEVELOPER);
        return array();
    }

    
    public function course_edit_validation($instance, array $data, $context) {
        return array();
    }

    
    public function course_updated($inserted, $course, $data) {
        if ($inserted) {
            if ($this->get_config('defaultenrol')) {
                $this->add_default_instance($course);
            }
        }
    }

    
    public function add_instance($course, array $fields = NULL) {
        global $DB;

        if ($course->id == SITEID) {
            throw new coding_exception('Invalid request to add enrol instance to frontpage.');
        }

        $instance = new stdClass();
        $instance->enrol          = $this->get_name();
        $instance->status         = ENROL_INSTANCE_ENABLED;
        $instance->courseid       = $course->id;
        $instance->enrolstartdate = 0;
        $instance->enrolenddate   = 0;
        $instance->timemodified   = time();
        $instance->timecreated    = $instance->timemodified;
        $instance->sortorder      = $DB->get_field('enrol', 'COALESCE(MAX(sortorder), -1) + 1', array('courseid'=>$course->id));

        $fields = (array)$fields;
        unset($fields['enrol']);
        unset($fields['courseid']);
        unset($fields['sortorder']);
        foreach($fields as $field=>$value) {
            $instance->$field = $value;
        }

        $instance->id = $DB->insert_record('enrol', $instance);

        \core\event\enrol_instance_created::create_from_record($instance)->trigger();

        return $instance->id;
    }

    
    public function update_instance($instance, $data) {
        global $DB;
        $properties = array('status', 'name', 'password', 'customint1', 'customint2', 'customint3',
                            'customint4', 'customint5', 'customint6', 'customint7', 'customint8',
                            'customchar1', 'customchar2', 'customchar3', 'customdec1', 'customdec2',
                            'customtext1', 'customtext2', 'customtext3', 'customtext4', 'roleid',
                            'enrolperiod', 'expirynotify', 'notifyall', 'expirythreshold',
                            'enrolstartdate', 'enrolenddate', 'cost', 'currency');

        foreach ($properties as $key) {
            if (isset($data->$key)) {
                $instance->$key = $data->$key;
            }
        }
        $instance->timemodified = time();

        $update = $DB->update_record('enrol', $instance);
        if ($update) {
            \core\event\enrol_instance_updated::create_from_record($instance)->trigger();
        }
        return $update;
    }

    
    public function add_default_instance($course) {
        return null;
    }

    
    public function update_status($instance, $newstatus) {
        global $DB;

        $instance->status = $newstatus;
        $DB->update_record('enrol', $instance);

        $context = context_course::instance($instance->courseid);
        \core\event\enrol_instance_updated::create_from_record($instance)->trigger();

                $context->mark_dirty();
    }

    
    public function delete_instance($instance) {
        global $DB;

        $name = $this->get_name();
        if ($instance->enrol !== $name) {
            throw new coding_exception('invalid enrol instance!');
        }

                $participants = $DB->get_recordset('user_enrolments', array('enrolid'=>$instance->id));
        foreach ($participants as $participant) {
            $this->unenrol_user($instance, $participant->userid);
        }
        $participants->close();

                $DB->delete_records('groups_members', array('itemid'=>$instance->id, 'component'=>'enrol_'.$name));
        $DB->delete_records('role_assignments', array('itemid'=>$instance->id, 'component'=>'enrol_'.$name));
        $DB->delete_records('user_enrolments', array('enrolid'=>$instance->id));

                $DB->delete_records('enrol', array('id'=>$instance->id));

        $context = context_course::instance($instance->courseid);
        \core\event\enrol_instance_deleted::create_from_record($instance)->trigger();

                $context->mark_dirty();
    }

    
    public function enrol_page_hook(stdClass $instance) {
        return null;
    }

    
    public function can_self_enrol(stdClass $instance, $checkuserenrolment = true) {
        return false;
    }

    
    public function get_enrol_info(stdClass $instance) {
        return null;
    }

    
    public function add_course_navigation($instancesnode, stdClass $instance) {
        if ($this->use_standard_editing_ui()) {
            $context = context_course::instance($instance->courseid);
            $cap = 'enrol/' . $instance->enrol . ':config';
            if (has_capability($cap, $context)) {
                $linkparams = array('courseid' => $instance->courseid, 'id' => $instance->id, 'type' => $instance->enrol);
                $managelink = new moodle_url('/enrol/editinstance.php', $linkparams);
                $instancesnode->add($this->get_instance_name($instance), $managelink, navigation_node::TYPE_SETTING);
            }
        }
    }

    
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;

        $icons = array();
        if ($this->use_standard_editing_ui()) {
            $linkparams = array('courseid' => $instance->courseid, 'id' => $instance->id, 'type' => $instance->enrol);
            $editlink = new moodle_url("/enrol/editinstance.php", $linkparams);
            $icons[] = $OUTPUT->action_icon($editlink, new pix_icon('t/edit', get_string('edit'), 'core',
                array('class' => 'iconsmall')));
        }
        return $icons;
    }

    
    public function is_cron_required() {
        global $CFG;

        $name = $this->get_name();
        $versionfile = "$CFG->dirroot/enrol/$name/version.php";
        $plugin = new stdClass();
        include($versionfile);
        if (empty($plugin->cron)) {
            return false;
        }
        $lastexecuted = $this->get_config('lastcron', 0);
        if ($lastexecuted + $plugin->cron < time()) {
            return true;
        } else {
            return false;
        }
    }

    
    public function cron() {
    }

    
    public function user_delete($user) {
        global $DB;

        $sql = "SELECT e.*
                  FROM {enrol} e
                  JOIN {user_enrolments} ue ON (ue.enrolid = e.id)
                 WHERE e.enrol = :name AND ue.userid = :userid";
        $params = array('name'=>$this->get_name(), 'userid'=>$user->id);

        $rs = $DB->get_recordset_sql($sql, $params);
        foreach($rs as $instance) {
            $this->unenrol_user($instance, $user->id);
        }
        $rs->close();
    }

    
    public function get_manual_enrol_button(course_enrolment_manager $manager) {
        return false;
    }

    
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
        return array();
    }

    
    public function has_bulk_operations(course_enrolment_manager $manager) {
       return false;
    }

    
    public function get_bulk_operations(course_enrolment_manager $manager) {
        return array();
    }

    
    public function process_expirations(progress_trace $trace, $courseid = null) {
        global $DB;

        $name = $this->get_name();
        if (!enrol_is_enabled($name)) {
            $trace->finished();
            return false;
        }

        $processed = false;
        $params = array();
        $coursesql = "";
        if ($courseid) {
            $coursesql = "AND e.courseid = :courseid";
        }

                $action = $this->get_config('expiredaction', ENROL_EXT_REMOVED_KEEP);

        if ($action == ENROL_EXT_REMOVED_UNENROL) {
            $instances = array();
            $sql = "SELECT ue.*, e.courseid, c.id AS contextid
                      FROM {user_enrolments} ue
                      JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = :enrol)
                      JOIN {context} c ON (c.instanceid = e.courseid AND c.contextlevel = :courselevel)
                     WHERE ue.timeend > 0 AND ue.timeend < :now $coursesql";
            $params = array('now'=>time(), 'courselevel'=>CONTEXT_COURSE, 'enrol'=>$name, 'courseid'=>$courseid);

            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $ue) {
                if (!$processed) {
                    $trace->output("Starting processing of enrol_$name expirations...");
                    $processed = true;
                }
                if (empty($instances[$ue->enrolid])) {
                    $instances[$ue->enrolid] = $DB->get_record('enrol', array('id'=>$ue->enrolid));
                }
                $instance = $instances[$ue->enrolid];
                if (!$this->roles_protected()) {
                                        if ($instance->roleid) {
                        role_unassign($instance->roleid, $ue->userid, $ue->contextid);
                    }
                }
                                $this->unenrol_user($instance, $ue->userid);
                $trace->output("Unenrolling expired user $ue->userid from course $instance->courseid", 1);
            }
            $rs->close();
            unset($instances);

        } else if ($action == ENROL_EXT_REMOVED_SUSPENDNOROLES or $action == ENROL_EXT_REMOVED_SUSPEND) {
            $instances = array();
            $sql = "SELECT ue.*, e.courseid, c.id AS contextid
                      FROM {user_enrolments} ue
                      JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = :enrol)
                      JOIN {context} c ON (c.instanceid = e.courseid AND c.contextlevel = :courselevel)
                     WHERE ue.timeend > 0 AND ue.timeend < :now
                           AND ue.status = :useractive $coursesql";
            $params = array('now'=>time(), 'courselevel'=>CONTEXT_COURSE, 'useractive'=>ENROL_USER_ACTIVE, 'enrol'=>$name, 'courseid'=>$courseid);
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $ue) {
                if (!$processed) {
                    $trace->output("Starting processing of enrol_$name expirations...");
                    $processed = true;
                }
                if (empty($instances[$ue->enrolid])) {
                    $instances[$ue->enrolid] = $DB->get_record('enrol', array('id'=>$ue->enrolid));
                }
                $instance = $instances[$ue->enrolid];

                if ($action == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                    if (!$this->roles_protected()) {
                                                $count = $DB->count_records('role_assignments', array('userid'=>$ue->userid, 'contextid'=>$ue->contextid));
                        if ($count == 1) {
                            role_unassign_all(array('userid'=>$ue->userid, 'contextid'=>$ue->contextid, 'component'=>'', 'itemid'=>0));

                        } else if ($count > 1 and $instance->roleid) {
                            role_unassign($instance->roleid, $ue->userid, $ue->contextid, '', 0);
                        }
                    }
                                        role_unassign_all(array('userid'=>$ue->userid, 'contextid'=>$ue->contextid, 'component'=>'enrol_'.$name, 'itemid'=>$instance->id), true);
                                        if (0 == $DB->count_records('role_assignments', array('userid'=>$ue->userid, 'contextid'=>$ue->contextid))) {
                        role_unassign_all(array('userid'=>$ue->userid, 'contextid'=>$ue->contextid, 'component'=>'', 'itemid'=>0), true);
                    }
                }

                $this->update_user_enrol($instance, $ue->userid, ENROL_USER_SUSPENDED);
                $trace->output("Suspending expired user $ue->userid in course $instance->courseid", 1);
            }
            $rs->close();
            unset($instances);

        } else {
                    }

        if ($processed) {
            $trace->output("...finished processing of enrol_$name expirations");
        } else {
            $trace->output("No expired enrol_$name enrolments detected");
        }
        $trace->finished();

        return $processed;
    }

    
    public function send_expiry_notifications($trace) {
        global $DB, $CFG;

        $name = $this->get_name();
        if (!enrol_is_enabled($name)) {
            $trace->finished();
            return;
        }

                
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);


        $expirynotifylast = $this->get_config('expirynotifylast', 0);
        $expirynotifyhour = $this->get_config('expirynotifyhour');
        if (is_null($expirynotifyhour)) {
            debugging("send_expiry_notifications() in $name enrolment plugin needs expirynotifyhour setting");
            $trace->finished();
            return;
        }

        if (!($trace instanceof progress_trace)) {
            $trace = $trace ? new text_progress_trace() : new null_progress_trace();
            debugging('enrol_plugin::send_expiry_notifications() now expects progress_trace instance as parameter!', DEBUG_DEVELOPER);
        }

        $timenow = time();
        $notifytime = usergetmidnight($timenow, $CFG->timezone) + ($expirynotifyhour * 3600);

        if ($expirynotifylast > $notifytime) {
            $trace->output($name.' enrolment expiry notifications were already sent today at '.userdate($expirynotifylast, '', $CFG->timezone).'.');
            $trace->finished();
            return;

        } else if ($timenow < $notifytime) {
            $trace->output($name.' enrolment expiry notifications will be sent at '.userdate($notifytime, '', $CFG->timezone).'.');
            $trace->finished();
            return;
        }

        $trace->output('Processing '.$name.' enrolment expiration notifications...');

                $sql = "SELECT ue.*, e.expirynotify, e.notifyall, e.expirythreshold, e.courseid, c.fullname
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = :name AND e.expirynotify > 0 AND e.status = :enabled)
                  JOIN {course} c ON (c.id = e.courseid)
                  JOIN {user} u ON (u.id = ue.userid AND u.deleted = 0 AND u.suspended = 0)
                 WHERE ue.status = :active AND ue.timeend > 0 AND ue.timeend > :now1 AND ue.timeend < (e.expirythreshold + :now2)
              ORDER BY ue.enrolid ASC, u.lastname ASC, u.firstname ASC, u.id ASC";
        $params = array('enabled'=>ENROL_INSTANCE_ENABLED, 'active'=>ENROL_USER_ACTIVE, 'now1'=>$timenow, 'now2'=>$timenow, 'name'=>$name);

        $rs = $DB->get_recordset_sql($sql, $params);

        $lastenrollid = 0;
        $users = array();

        foreach($rs as $ue) {
            if ($lastenrollid and $lastenrollid != $ue->enrolid) {
                $this->notify_expiry_enroller($lastenrollid, $users, $trace);
                $users = array();
            }
            $lastenrollid = $ue->enrolid;

            $enroller = $this->get_enroller($ue->enrolid);
            $context = context_course::instance($ue->courseid);

            $user = $DB->get_record('user', array('id'=>$ue->userid));

            $users[] = array('fullname'=>fullname($user, has_capability('moodle/site:viewfullnames', $context, $enroller)), 'timeend'=>$ue->timeend);

            if (!$ue->notifyall) {
                continue;
            }

            if ($ue->timeend - $ue->expirythreshold + 86400 < $timenow) {
                                $trace->output("user $ue->userid was already notified that enrolment in course $ue->courseid expires on ".userdate($ue->timeend, '', $CFG->timezone), 1);
                continue;
            }

            $this->notify_expiry_enrolled($user, $ue, $trace);
        }
        $rs->close();

        if ($lastenrollid and $users) {
            $this->notify_expiry_enroller($lastenrollid, $users, $trace);
        }

        $trace->output('...notification processing finished.');
        $trace->finished();

        $this->set_config('expirynotifylast', $timenow);
    }

    
    protected function get_enroller($instanceid) {
        return get_admin();
    }

    
    protected function notify_expiry_enrolled($user, $ue, progress_trace $trace) {
        global $CFG;

        $name = $this->get_name();

        $oldforcelang = force_current_language($user->lang);

        $enroller = $this->get_enroller($ue->enrolid);
        $context = context_course::instance($ue->courseid);

        $a = new stdClass();
        $a->course   = format_string($ue->fullname, true, array('context'=>$context));
        $a->user     = fullname($user, true);
        $a->timeend  = userdate($ue->timeend, '', $user->timezone);
        $a->enroller = fullname($enroller, has_capability('moodle/site:viewfullnames', $context, $user));

        $subject = get_string('expirymessageenrolledsubject', 'enrol_'.$name, $a);
        $body = get_string('expirymessageenrolledbody', 'enrol_'.$name, $a);

        $message = new stdClass();
        $message->notification      = 1;
        $message->component         = 'enrol_'.$name;
        $message->name              = 'expiry_notification';
        $message->userfrom          = $enroller;
        $message->userto            = $user;
        $message->subject           = $subject;
        $message->fullmessage       = $body;
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml   = markdown_to_html($body);
        $message->smallmessage      = $subject;
        $message->contexturlname    = $a->course;
        $message->contexturl        = (string)new moodle_url('/course/view.php', array('id'=>$ue->courseid));

        if (message_send($message)) {
            $trace->output("notifying user $ue->userid that enrolment in course $ue->courseid expires on ".userdate($ue->timeend, '', $CFG->timezone), 1);
        } else {
            $trace->output("error notifying user $ue->userid that enrolment in course $ue->courseid expires on ".userdate($ue->timeend, '', $CFG->timezone), 1);
        }

        force_current_language($oldforcelang);
    }

    
    protected function notify_expiry_enroller($eid, $users, progress_trace $trace) {
        global $DB;

        $name = $this->get_name();

        $instance = $DB->get_record('enrol', array('id'=>$eid, 'enrol'=>$name));
        $context = context_course::instance($instance->courseid);
        $course = $DB->get_record('course', array('id'=>$instance->courseid));

        $enroller = $this->get_enroller($instance->id);
        $admin = get_admin();

        $oldforcelang = force_current_language($enroller->lang);

        foreach($users as $key=>$info) {
            $users[$key] = '* '.$info['fullname'].' - '.userdate($info['timeend'], '', $enroller->timezone);
        }

        $a = new stdClass();
        $a->course    = format_string($course->fullname, true, array('context'=>$context));
        $a->threshold = get_string('numdays', '', $instance->expirythreshold / (60*60*24));
        $a->users     = implode("\n", $users);
        $a->extendurl = (string)new moodle_url('/enrol/users.php', array('id'=>$instance->courseid));

        $subject = get_string('expirymessageenrollersubject', 'enrol_'.$name, $a);
        $body = get_string('expirymessageenrollerbody', 'enrol_'.$name, $a);

        $message = new stdClass();
        $message->notification      = 1;
        $message->component         = 'enrol_'.$name;
        $message->name              = 'expiry_notification';
        $message->userfrom          = $admin;
        $message->userto            = $enroller;
        $message->subject           = $subject;
        $message->fullmessage       = $body;
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml   = markdown_to_html($body);
        $message->smallmessage      = $subject;
        $message->contexturlname    = $a->course;
        $message->contexturl        = $a->extendurl;

        if (message_send($message)) {
            $trace->output("notifying user $enroller->id about all expiring $name enrolments in course $instance->courseid", 1);
        } else {
            $trace->output("error notifying user $enroller->id about all expiring $name enrolments in course $instance->courseid", 1);
        }

        force_current_language($oldforcelang);
    }

    
    public function backup_annotate_custom_fields(backup_enrolments_execution_step $step, stdClass $enrol) {
            }

    
    public function restore_sync_course($course) {
            }

    
    public function restore_instance(restore_enrolments_structure_step $step, stdClass $data, $course, $oldid) {
                $step->set_mapping('enrol', $oldid, 0);
    }

    
    public function restore_user_enrolment(restore_enrolments_structure_step $step, $data, $instance, $userid, $oldinstancestatus) {
            }

    
    public function restore_role_assignment($instance, $roleid, $userid, $contextid) {
            }

    
    public function restore_group_member($instance, $groupid, $userid) {
                    }

    
    public function get_instance_defaults() {
        return array();
    }

    
    public function validate_param_types($data, $rules) {
        $errors = array();
        $invalidstr = get_string('invaliddata', 'error');
        foreach ($rules as $fieldname => $rule) {
            if (is_array($rule)) {
                if (!in_array($data[$fieldname], $rule)) {
                    $errors[$fieldname] = $invalidstr;
                }
            } else {
                if ($data[$fieldname] != clean_param($data[$fieldname], $rule)) {
                    $errors[$fieldname] = $invalidstr;
                }
            }
        }
        return $errors;
    }
}
