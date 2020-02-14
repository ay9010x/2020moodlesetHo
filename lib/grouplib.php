<?php




defined('MOODLE_INTERNAL') || die();


define('NOGROUPS', 0);


define('SEPARATEGROUPS', 1);


define('VISIBLEGROUPS', 2);



function groups_group_exists($groupid) {
    global $DB;
    return $DB->record_exists('groups', array('id'=>$groupid));
}


function groups_get_group_name($groupid) {
    global $DB;
    return $DB->get_field('groups', 'name', array('id'=>$groupid));
}


function groups_get_grouping_name($groupingid) {
    global $DB;
    return $DB->get_field('groupings', 'name', array('id'=>$groupingid));
}


function groups_get_group_by_name($courseid, $name) {
    $data = groups_get_course_data($courseid);
    foreach ($data->groups as $group) {
        if ($group->name == $name) {
            return $group->id;
        }
    }
    return false;
}


function groups_get_group_by_idnumber($courseid, $idnumber) {
    if (empty($idnumber)) {
        return false;
    }
    $data = groups_get_course_data($courseid);
    foreach ($data->groups as $group) {
        if ($group->idnumber == $idnumber) {
            return $group;
        }
    }
    return false;
}


function groups_get_grouping_by_name($courseid, $name) {
    $data = groups_get_course_data($courseid);
    foreach ($data->groupings as $grouping) {
        if ($grouping->name == $name) {
            return $grouping->id;
        }
    }
    return false;
}


function groups_get_grouping_by_idnumber($courseid, $idnumber) {
    if (empty($idnumber)) {
        return false;
    }
    $data = groups_get_course_data($courseid);
    foreach ($data->groupings as $grouping) {
        if ($grouping->idnumber == $idnumber) {
            return $grouping;
        }
    }
    return false;
}


function groups_get_group($groupid, $fields='*', $strictness=IGNORE_MISSING) {
    global $DB;
    return $DB->get_record('groups', array('id'=>$groupid), $fields, $strictness);
}


function groups_get_grouping($groupingid, $fields='*', $strictness=IGNORE_MISSING) {
    global $DB;
    return $DB->get_record('groupings', array('id'=>$groupingid), $fields, $strictness);
}


function groups_get_all_groups($courseid, $userid=0, $groupingid=0, $fields='g.*') {
    global $DB;

            $knownfields = true;
    if ($fields !== 'g.*') {
                        if (strpos($fields, 'g.*') !== 0 && strpos($fields, 'g.id') !== 0) {
            $knownfields = false;
        } else {
            $fieldbits = explode(',', $fields);
            foreach ($fieldbits as $bit) {
                $bit = trim($bit);
                if (strpos($bit, 'g.') !== 0 or stripos($bit, ' AS ') !== false) {
                    $knownfields = false;
                    break;
                }
            }
        }
    }

    if (empty($userid) && $knownfields) {
                $data = groups_get_course_data($courseid);
        if (empty($groupingid)) {
                        $groups = $data->groups;
        } else {
            $groups = array();
            foreach ($data->mappings as $mapping) {
                if ($mapping->groupingid != $groupingid) {
                    continue;
                }
                if (isset($data->groups[$mapping->groupid])) {
                    $groups[$mapping->groupid] = $data->groups[$mapping->groupid];
                }
            }
        }
                return $groups;
    }


    if (empty($userid)) {
        $userfrom  = "";
        $userwhere = "";
        $params = array();

    } else {
        list($usql, $params) = $DB->get_in_or_equal($userid);
        $userfrom  = ", {groups_members} gm";
        $userwhere = "AND g.id = gm.groupid AND gm.userid $usql";
    }

    if (!empty($groupingid)) {
        $groupingfrom  = ", {groupings_groups} gg";
        $groupingwhere = "AND g.id = gg.groupid AND gg.groupingid = ?";
        $params[] = $groupingid;
    } else {
        $groupingfrom  = "";
        $groupingwhere = "";
    }

    array_unshift($params, $courseid);

    return $DB->get_records_sql("SELECT $fields
                                   FROM {groups} g $userfrom $groupingfrom
                                  WHERE g.courseid = ? $userwhere $groupingwhere
                               ORDER BY name ASC", $params);
}



function groups_get_my_groups() {
    global $DB, $USER;
    return $DB->get_records_sql("SELECT *
                                   FROM {groups_members} gm
                                   JOIN {groups} g
                                    ON g.id = gm.groupid
                                  WHERE gm.userid = ?
                                   ORDER BY name ASC", array($USER->id));
}


function groups_get_user_groups($courseid, $userid=0) {
    global $USER, $DB;

    if (empty($userid)) {
        $userid = $USER->id;
    }

    $sql = "SELECT g.id, gg.groupingid
              FROM {groups} g
                   JOIN {groups_members} gm   ON gm.groupid = g.id
              LEFT JOIN {groupings_groups} gg ON gg.groupid = g.id
             WHERE gm.userid = ? AND g.courseid = ?";
    $params = array($userid, $courseid);

    $rs = $DB->get_recordset_sql($sql, $params);

    if (!$rs->valid()) {
        $rs->close();         return array('0' => array());
    }

    $result    = array();
    $allgroups = array();

    foreach ($rs as $group) {
        $allgroups[$group->id] = $group->id;
        if (is_null($group->groupingid)) {
            continue;
        }
        if (!array_key_exists($group->groupingid, $result)) {
            $result[$group->groupingid] = array();
        }
        $result[$group->groupingid][$group->id] = $group->id;
    }
    $rs->close();

    $result['0'] = array_keys($allgroups); 
    return $result;
}


function groups_get_all_groupings($courseid) {
    $data = groups_get_course_data($courseid);
    return $data->groupings;
}


function groups_is_member($groupid, $userid=null) {
    global $USER, $DB;

    if (!$userid) {
        $userid = $USER->id;
    }

    return $DB->record_exists('groups_members', array('groupid'=>$groupid, 'userid'=>$userid));
}


function groups_has_membership($cm, $userid=null) {
    global $CFG, $USER, $DB;

    static $cache = array();

    if (empty($userid)) {
        $userid = $USER->id;
    }

    $cachekey = $userid.'|'.$cm->course.'|'.$cm->groupingid;
    if (isset($cache[$cachekey])) {
        return($cache[$cachekey]);
    }

    if ($cm->groupingid) {
                $sql = "SELECT 'x'
                  FROM {groups_members} gm, {groupings_groups} gg
                 WHERE gm.userid = ? AND gm.groupid = gg.groupid AND gg.groupingid = ?";
        $params = array($userid, $cm->groupingid);

    } else {
                $sql = "SELECT 'x'
                  FROM {groups_members} gm, {groups} g
                 WHERE gm.userid = ? AND gm.groupid = g.id AND g.courseid = ?";
        $params = array($userid, $cm->course);
    }

    $cache[$cachekey] = $DB->record_exists_sql($sql, $params);

    return $cache[$cachekey];
}


function groups_get_members($groupid, $fields='u.*', $sort='lastname ASC') {
    global $DB;

    return $DB->get_records_sql("SELECT $fields
                                   FROM {user} u, {groups_members} gm
                                  WHERE u.id = gm.userid AND gm.groupid = ?
                               ORDER BY $sort", array($groupid));
}



function groups_get_grouping_members($groupingid, $fields='u.*', $sort='lastname ASC') {
    global $DB;

    return $DB->get_records_sql("SELECT $fields
                                   FROM {user} u
                                     INNER JOIN {groups_members} gm ON u.id = gm.userid
                                     INNER JOIN {groupings_groups} gg ON gm.groupid = gg.groupid
                                  WHERE  gg.groupingid = ?
                               ORDER BY $sort", array($groupingid));
}


function groups_get_course_groupmode($course) {
    return $course->groupmode;
}


function groups_get_activity_groupmode($cm, $course=null) {
    if ($cm instanceof cm_info) {
        return $cm->effectivegroupmode;
    }
    if (isset($course->id) and $course->id == $cm->course) {
            } else {
                $course = get_course($cm->course, false);
    }

    return empty($course->groupmodeforce) ? $cm->groupmode : $course->groupmode;
}


function groups_print_course_menu($course, $urlroot, $return=false) {
    global $USER, $OUTPUT;

    if (!$groupmode = $course->groupmode) {
        if ($return) {
            return '';
        } else {
            return;
        }
    }

    $context = context_course::instance($course->id);
    $aag = has_capability('moodle/site:accessallgroups', $context);

    $usergroups = array();
    if ($groupmode == VISIBLEGROUPS or $aag) {
        $allowedgroups = groups_get_all_groups($course->id, 0, $course->defaultgroupingid);
                $usergroups = groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid);
    } else {
        $allowedgroups = groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid);
    }

    $activegroup = groups_get_course_group($course, true, $allowedgroups);

    $groupsmenu = array();
    if (!$allowedgroups or $groupmode == VISIBLEGROUPS or $aag) {
        $groupsmenu[0] = get_string('allparticipants');
    }

    $groupsmenu += groups_sort_menu_options($allowedgroups, $usergroups);

    if ($groupmode == VISIBLEGROUPS) {
        $grouplabel = get_string('groupsvisible');
    } else {
        $grouplabel = get_string('groupsseparate');
    }

    if ($aag and $course->defaultgroupingid) {
        if ($grouping = groups_get_grouping($course->defaultgroupingid)) {
            $grouplabel = $grouplabel . ' (' . format_string($grouping->name) . ')';
        }
    }

    if (count($groupsmenu) == 1) {
        $groupname = reset($groupsmenu);
        $output = $grouplabel.': '.$groupname;
    } else {
        $select = new single_select(new moodle_url($urlroot), 'group', $groupsmenu, $activegroup, null, 'selectgroup');
        $select->label = $grouplabel;
        $output = $OUTPUT->render($select);
    }

    $output = '<div class="groupselector">'.$output.'</div>';

    if ($return) {
        return $output;
    } else {
        echo $output;
    }
}


function groups_list_to_menu($groups) {
    $groupsmenu = array();
    foreach ($groups as $group) {
        $groupsmenu[$group->id] = format_string($group->name);
    }
    return $groupsmenu;
}


function groups_sort_menu_options($allowedgroups, $usergroups) {
    $useroptions = array();
    if ($usergroups) {
        $useroptions = groups_list_to_menu($usergroups);

                foreach ($usergroups as $group) {
            unset($allowedgroups[$group->id]);
        }
    }

    $allowedoptions = array();
    if ($allowedgroups) {
        $allowedoptions = groups_list_to_menu($allowedgroups);
    }

    if ($useroptions && $allowedoptions) {
        return array(
            1 => array(get_string('mygroups', 'group') => $useroptions),
            2 => array(get_string('othergroups', 'group') => $allowedoptions)
        );
    } else if ($useroptions) {
        return $useroptions;
    } else {
        return $allowedoptions;
    }
}


function groups_allgroups_course_menu($course, $urlroot, $update = false, $activegroup = 0) {
    global $SESSION, $OUTPUT, $USER;

    $groupmode = groups_get_course_groupmode($course);
    $context = context_course::instance($course->id);
    $groupsmenu = array();

    if (has_capability('moodle/site:accessallgroups', $context)) {
        $groupsmenu[0] = get_string('allparticipants');
        $allowedgroups = groups_get_all_groups($course->id, 0, $course->defaultgroupingid);
    } else {
        $allowedgroups = groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid);
    }

    $groupsmenu += groups_list_to_menu($allowedgroups);

    if ($update) {
                if (!isset($SESSION->activegroup)) {
            $SESSION->activegroup = array();
        }
        if (!isset($SESSION->activegroup[$course->id])) {
            $SESSION->activegroup[$course->id] = array(SEPARATEGROUPS => array(), VISIBLEGROUPS => array(), 'aag' => array());
        }
        if (empty($groupsmenu[$activegroup])) {
            $activegroup = key($groupsmenu);         }
        $SESSION->activegroup[$course->id][$groupmode][$course->defaultgroupingid] = $activegroup;
    }

    $grouplabel = get_string('groups');
    if (count($groupsmenu) == 0) {
        return '';
    } else if (count($groupsmenu) == 1) {
        $groupname = reset($groupsmenu);
        $output = $grouplabel.': '.$groupname;
    } else {
        $select = new single_select(new moodle_url($urlroot), 'group', $groupsmenu, $activegroup, null, 'selectgroup');
        $select->label = $grouplabel;
        $output = $OUTPUT->render($select);
    }

    return $output;

}


function groups_print_activity_menu($cm, $urlroot, $return=false, $hideallparticipants=false) {
    global $USER, $OUTPUT;

    if ($urlroot instanceof moodle_url) {
        
    } else {
        if (strpos($urlroot, 'http') !== 0) {                         debugging('groups_print_activity_menu requires absolute URL for ' .
                      '$urlroot, not <tt>' . s($urlroot) . '</tt>. Example: ' .
                      'groups_print_activity_menu($cm, $CFG->wwwroot . \'/mod/mymodule/view.php?id=13\');',
                      DEBUG_DEVELOPER);
        }
        $urlroot = new moodle_url($urlroot);
    }

    if (!$groupmode = groups_get_activity_groupmode($cm)) {
        if ($return) {
            return '';
        } else {
            return;
        }
    }

    $context = context_module::instance($cm->id);
    $aag = has_capability('moodle/site:accessallgroups', $context);

    $usergroups = array();
    if ($groupmode == VISIBLEGROUPS or $aag) {
        $allowedgroups = groups_get_all_groups($cm->course, 0, $cm->groupingid);                 $usergroups = groups_get_all_groups($cm->course, $USER->id, $cm->groupingid);
    } else {
        $allowedgroups = groups_get_all_groups($cm->course, $USER->id, $cm->groupingid);     }

    $activegroup = groups_get_activity_group($cm, true, $allowedgroups);

    $groupsmenu = array();
    if ((!$allowedgroups or $groupmode == VISIBLEGROUPS or $aag) and !$hideallparticipants) {
        $groupsmenu[0] = get_string('allparticipants');
    }

    $groupsmenu += groups_sort_menu_options($allowedgroups, $usergroups);

    if ($groupmode == VISIBLEGROUPS) {
        $grouplabel = get_string('groupsvisible');
    } else {
        $grouplabel = get_string('groupsseparate');
    }

    if ($aag and $cm->groupingid) {
        if ($grouping = groups_get_grouping($cm->groupingid)) {
            $grouplabel = $grouplabel . ' (' . format_string($grouping->name) . ')';
        }
    }

    if (count($groupsmenu) == 1) {
        $groupname = reset($groupsmenu);
        $output = $grouplabel.': '.$groupname;
    } else {
        $select = new single_select($urlroot, 'group', $groupsmenu, $activegroup, null, 'selectgroup');
        $select->label = $grouplabel;
        $output = $OUTPUT->render($select);
    }

    $output = '<div class="groupselector">'.$output.'</div>';

    if ($return) {
        return $output;
    } else {
        echo $output;
    }
}


function groups_get_course_group($course, $update=false, $allowedgroups=null) {
    global $USER, $SESSION;

    if (!$groupmode = $course->groupmode) {
                return false;
    }

    $context = context_course::instance($course->id);
    if (has_capability('moodle/site:accessallgroups', $context)) {
        $groupmode = 'aag';
    }

    if (!is_array($allowedgroups)) {
        if ($groupmode == VISIBLEGROUPS or $groupmode === 'aag') {
            $allowedgroups = groups_get_all_groups($course->id, 0, $course->defaultgroupingid);
        } else {
            $allowedgroups = groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid);
        }
    }

    _group_verify_activegroup($course->id, $groupmode, $course->defaultgroupingid, $allowedgroups);

        $changegroup = optional_param('group', -1, PARAM_INT);
    if ($update and $changegroup != -1) {

        if ($changegroup == 0) {
                        if ($groupmode == VISIBLEGROUPS or $groupmode === 'aag') {
                $SESSION->activegroup[$course->id][$groupmode][$course->defaultgroupingid] = 0;
            }

        } else {
            if ($allowedgroups and array_key_exists($changegroup, $allowedgroups)) {
                $SESSION->activegroup[$course->id][$groupmode][$course->defaultgroupingid] = $changegroup;
            }
        }
    }

    return $SESSION->activegroup[$course->id][$groupmode][$course->defaultgroupingid];
}


function groups_get_activity_group($cm, $update=false, $allowedgroups=null) {
    global $USER, $SESSION;

    if (!$groupmode = groups_get_activity_groupmode($cm)) {
                return false;
    }

    $context = context_module::instance($cm->id);
    if (has_capability('moodle/site:accessallgroups', $context)) {
        $groupmode = 'aag';
    }

    if (!is_array($allowedgroups)) {
        if ($groupmode == VISIBLEGROUPS or $groupmode === 'aag') {
            $allowedgroups = groups_get_all_groups($cm->course, 0, $cm->groupingid);
        } else {
            $allowedgroups = groups_get_all_groups($cm->course, $USER->id, $cm->groupingid);
        }
    }

    _group_verify_activegroup($cm->course, $groupmode, $cm->groupingid, $allowedgroups);

        $changegroup = optional_param('group', -1, PARAM_INT);
    if ($update and $changegroup != -1) {

        if ($changegroup == 0) {
                        if ($groupmode == VISIBLEGROUPS or $groupmode === 'aag') {
                $SESSION->activegroup[$cm->course][$groupmode][$cm->groupingid] = 0;
            }

        } else {
            if ($allowedgroups and array_key_exists($changegroup, $allowedgroups)) {
                $SESSION->activegroup[$cm->course][$groupmode][$cm->groupingid] = $changegroup;
            }
        }
    }

    return $SESSION->activegroup[$cm->course][$groupmode][$cm->groupingid];
}


function groups_get_activity_allowed_groups($cm,$userid=0) {
        global $USER;
    if(!$userid) {
        $userid=$USER->id;
    }

        $groupmode=groups_get_activity_groupmode($cm);

            $context = context_module::instance($cm->id);
    if ($groupmode == VISIBLEGROUPS or has_capability('moodle/site:accessallgroups', $context, $userid)) {
        return groups_get_all_groups($cm->course, 0, $cm->groupingid);
    } else {
                return groups_get_all_groups($cm->course, $userid, $cm->groupingid);
    }
}


function groups_group_visible($groupid, $course, $cm = null, $userid = null) {
    global $USER;

    if (empty($userid)) {
        $userid = $USER->id;
    }

    $groupmode = empty($cm) ? groups_get_course_groupmode($course) : groups_get_activity_groupmode($cm, $course);
    if ($groupmode == NOGROUPS || $groupmode == VISIBLEGROUPS) {
                return true;
    }

    $context = empty($cm) ? context_course::instance($course->id) : context_module::instance($cm->id);
    if (has_capability('moodle/site:accessallgroups', $context, $userid)) {
                return true;
    } else if ($groupid != 0) {
                $groups = empty($cm) ? groups_get_all_groups($course->id, $userid) : groups_get_activity_allowed_groups($cm, $userid);
        if (array_key_exists($groupid, $groups)) {
                        return true;
        }
    }
    return false;
}


function _group_verify_activegroup($courseid, $groupmode, $groupingid, array $allowedgroups) {
    global $SESSION, $USER;

        if (!isset($SESSION->activegroup)) {
        $SESSION->activegroup = array();
    }
    if (!array_key_exists($courseid, $SESSION->activegroup)) {
        $SESSION->activegroup[$courseid] = array(SEPARATEGROUPS=>array(), VISIBLEGROUPS=>array(), 'aag'=>array());
    }

        if (array_key_exists($groupingid, $SESSION->activegroup[$courseid][$groupmode]) and !array_key_exists($SESSION->activegroup[$courseid][$groupmode][$groupingid], $allowedgroups)) {
                if ($SESSION->activegroup[$courseid][$groupmode][$groupingid] > 0 or $groupmode == SEPARATEGROUPS) {
                        unset($SESSION->activegroup[$courseid][$groupmode][$groupingid]);
        }
    }

        if (!array_key_exists($groupingid, $SESSION->activegroup[$courseid][$groupmode])) {
        if ($groupmode == 'aag') {
            $SESSION->activegroup[$courseid][$groupmode][$groupingid] = 0; 
        } else if ($allowedgroups) {
            if ($groupmode != SEPARATEGROUPS and $mygroups = groups_get_all_groups($courseid, $USER->id, $groupingid)) {
                $firstgroup = reset($mygroups);
            } else {
                $firstgroup = reset($allowedgroups);
            }
            $SESSION->activegroup[$courseid][$groupmode][$groupingid] = $firstgroup->id;

        } else {
                                    $SESSION->activegroup[$courseid][$groupmode][$groupingid] = 0;
        }
    }
}


function groups_cache_groupdata($courseid, cache $cache = null) {
    global $DB;

    if ($cache === null) {
                $cache = cache::make('core', 'groupdata');
    }

        $groups = $DB->get_records('groups', array('courseid' => $courseid), 'name ASC');
        $groupings = $DB->get_records('groupings', array('courseid' => $courseid), 'name ASC');

    if (!is_array($groups)) {
        $groups = array();
    }

    if (!is_array($groupings)) {
        $groupings = array();
    }

    if (!empty($groupings)) {
                list($insql, $params) = $DB->get_in_or_equal(array_keys($groupings));
        $mappings = $DB->get_records_sql("
                SELECT gg.id, gg.groupingid, gg.groupid
                  FROM {groupings_groups} gg
                  JOIN {groups} g ON g.id = gg.groupid
                 WHERE gg.groupingid $insql
              ORDER BY g.name ASC", $params);
    } else {
        $mappings = array();
    }

        $data = new stdClass;
    $data->groups = $groups;
    $data->groupings = $groupings;
    $data->mappings = $mappings;
        $cache->set($courseid, $data);
        return $data;
}


function groups_get_course_data($courseid, cache $cache = null) {
    if ($cache === null) {
                $cache = cache::make('core', 'groupdata');
    }
        $data = $cache->get($courseid);
    if ($data === false) {
        $data = groups_cache_groupdata($courseid, $cache);
    }
    return $data;
}


function groups_user_groups_visible($course, $userid, $cm = null) {
    global $USER;

    $groupmode = empty($cm) ? groups_get_course_groupmode($course) : groups_get_activity_groupmode($cm, $course);
    if ($groupmode == NOGROUPS || $groupmode == VISIBLEGROUPS) {
                return true;
    }

    $context = empty($cm) ? context_course::instance($course->id) : context_module::instance($cm->id);
    if (has_capability('moodle/site:accessallgroups', $context)) {
                return true;
    } else {
                if (empty($cm)) {
            $usergroups = groups_get_all_groups($course->id, $userid);
            $currentusergroups = groups_get_all_groups($course->id, $USER->id);
        } else {
            $usergroups = groups_get_activity_allowed_groups($cm, $userid);
            $currentusergroups = groups_get_activity_allowed_groups($cm, $USER->id);
        }

        $samegroups = array_intersect_key($currentusergroups, $usergroups);
        if (!empty($samegroups)) {
                        return true;
        }
    }
    return false;
}
