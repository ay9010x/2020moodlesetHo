<?php



defined('MOODLE_INTERNAL') || die();


define('MAX_COURSES_IN_CATEGORY', 10000);


define('MAX_COURSE_CATEGORIES', 10000);


if (!defined('LASTACCESS_UPDATE_SECS')) {
    define('LASTACCESS_UPDATE_SECS', 60);
}


function get_admin() {
    global $CFG, $DB;

    static $mainadmin = null;
    static $prevadmins = null;

    if (empty($CFG->siteadmins)) {
                        return false;
    }

    if (isset($mainadmin) and $prevadmins === $CFG->siteadmins) {
        return clone($mainadmin);
    }

    $mainadmin = null;

    foreach (explode(',', $CFG->siteadmins) as $id) {
        if ($user = $DB->get_record('user', array('id'=>$id, 'deleted'=>0))) {
            $mainadmin = $user;
            break;
        }
    }

    if ($mainadmin) {
        $prevadmins = $CFG->siteadmins;
        return clone($mainadmin);
    } else {
                return false;
    }
}


function get_admins() {
    global $DB, $CFG;

    if (empty($CFG->siteadmins)) {          return array();
    }

    $sql = "SELECT u.*
              FROM {user} u
             WHERE u.deleted = 0 AND u.id IN ($CFG->siteadmins)";

        $records = $DB->get_records_sql($sql);
    $admins = array();
    foreach (explode(',', $CFG->siteadmins) as $id) {
        $id = (int)$id;
        if (!isset($records[$id])) {
                        continue;
        }
        $admins[$records[$id]->id] = $records[$id];
    }

    return $admins;
}


function search_users($courseid, $groupid, $searchtext, $sort='', array $exceptions=null) {
    global $DB;

    $fullname  = $DB->sql_fullname('u.firstname', 'u.lastname');

    if (!empty($exceptions)) {
        list($exceptions, $params) = $DB->get_in_or_equal($exceptions, SQL_PARAMS_NAMED, 'ex', false);
        $except = "AND u.id $exceptions";
    } else {
        $except = "";
        $params = array();
    }

    if (!empty($sort)) {
        $order = "ORDER BY $sort";
    } else {
        $order = "";
    }

    $select = "u.deleted = 0 AND u.confirmed = 1 AND (".$DB->sql_like($fullname, ':search1', false)." OR ".$DB->sql_like('u.email', ':search2', false).")";
    $params['search1'] = "%$searchtext%";
    $params['search2'] = "%$searchtext%";

    if (!$courseid or $courseid == SITEID) {
        $sql = "SELECT u.id, u.firstname, u.lastname, u.email
                  FROM {user} u
                 WHERE $select
                       $except
                $order";
        return $DB->get_records_sql($sql, $params);

    } else {
        if ($groupid) {
            $sql = "SELECT u.id, u.firstname, u.lastname, u.email
                      FROM {user} u
                      JOIN {groups_members} gm ON gm.userid = u.id
                     WHERE $select AND gm.groupid = :groupid
                           $except
                     $order";
            $params['groupid'] = $groupid;
            return $DB->get_records_sql($sql, $params);

        } else {
            $context = context_course::instance($courseid);

                        list($relatedctxsql, $relatedctxparams) = $DB->get_in_or_equal($context->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'relatedctx');

            $sql = "SELECT u.id, u.firstname, u.lastname, u.email
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                     WHERE $select AND ra.contextid $relatedctxsql
                           $except
                    $order";
            $params = array_merge($params, $relatedctxparams);
            return $DB->get_records_sql($sql, $params);
        }
    }
}


function users_search_sql($search, $u = 'u', $searchanywhere = true, array $extrafields = array(),
        array $exclude = null, array $includeonly = null) {
    global $DB, $CFG;
    $params = array();
    $tests = array();

    if ($u) {
        $u .= '.';
    }

        if ($search) {
        $conditions = array(
            $DB->sql_fullname($u . 'firstname', $u . 'lastname'),
            $conditions[] = $u . 'lastname'
        );
        foreach ($extrafields as $field) {
            $conditions[] = $u . $field;
        }
        if ($searchanywhere) {
            $searchparam = '%' . $search . '%';
        } else {
            $searchparam = $search . '%';
        }
        $i = 0;
        foreach ($conditions as $key => $condition) {
            $conditions[$key] = $DB->sql_like($condition, ":con{$i}00", false, false);
            $params["con{$i}00"] = $searchparam;
            $i++;
        }
        $tests[] = '(' . implode(' OR ', $conditions) . ')';
    }

        $tests[] = $u . "id <> :guestid";
    $params['guestid'] = $CFG->siteguest;
    $tests[] = $u . 'deleted = 0';
    $tests[] = $u . 'confirmed = 1';

        if (!empty($exclude)) {
        list($usertest, $userparams) = $DB->get_in_or_equal($exclude, SQL_PARAMS_NAMED, 'ex', false);
        $tests[] = $u . 'id ' . $usertest;
        $params = array_merge($params, $userparams);
    }

        if (!empty($includeonly)) {
        list($usertest, $userparams) = $DB->get_in_or_equal($includeonly, SQL_PARAMS_NAMED, 'val');
        $tests[] = $u . 'id ' . $usertest;
        $params = array_merge($params, $userparams);
    }

            if (empty($tests)) {
        $tests[] = '1 = 1';
    }

        return array(implode(' AND ', $tests), $params);
}



function users_order_by_sql($usertablealias = '', $search = null, context $context = null) {
    global $DB, $PAGE;

    if ($usertablealias) {
        $tableprefix = $usertablealias . '.';
    } else {
        $tableprefix = '';
    }

    $sort = "{$tableprefix}lastname, {$tableprefix}firstname, {$tableprefix}id";
    $params = array();

    if (!$search) {
        return array($sort, $params);
    }

    if (!$context) {
        $context = $PAGE->context;
    }

    $exactconditions = array();
    $paramkey = 'usersortexact1';

    $exactconditions[] = $DB->sql_fullname($tableprefix . 'firstname', $tableprefix  . 'lastname') .
            ' = :' . $paramkey;
    $params[$paramkey] = $search;
    $paramkey++;

    $fieldstocheck = array_merge(array('firstname', 'lastname'), get_extra_user_fields($context));
    foreach ($fieldstocheck as $key => $field) {
        $exactconditions[] = 'LOWER(' . $tableprefix . $field . ') = LOWER(:' . $paramkey . ')';
        $params[$paramkey] = $search;
        $paramkey++;
    }

    $sort = 'CASE WHEN ' . implode(' OR ', $exactconditions) .
            ' THEN 0 ELSE 1 END, ' . $sort;

    return array($sort, $params);
}


function get_users($get=true, $search='', $confirmed=false, array $exceptions=null, $sort='firstname ASC',
                   $firstinitial='', $lastinitial='', $page='', $recordsperpage='', $fields='*', $extraselect='', array $extraparams=null) {
    global $DB, $CFG;

    if ($get && !$recordsperpage) {
        debugging('Call to get_users with $get = true no $recordsperpage limit. ' .
                'On large installations, this will probably cause an out of memory error. ' .
                'Please think again and change your code so that it does not try to ' .
                'load so much data into memory.', DEBUG_DEVELOPER);
    }

    $fullname  = $DB->sql_fullname();

    $select = " id <> :guestid AND deleted = 0";
    $params = array('guestid'=>$CFG->siteguest);

    if (!empty($search)){
        $search = trim($search);
        $select .= " AND (".$DB->sql_like($fullname, ':search1', false)." OR ".$DB->sql_like('email', ':search2', false)." OR username = :search3)";
        $params['search1'] = "%$search%";
        $params['search2'] = "%$search%";
        $params['search3'] = "$search";
    }

    if ($confirmed) {
        $select .= " AND confirmed = 1";
    }

    if ($exceptions) {
        list($exceptions, $eparams) = $DB->get_in_or_equal($exceptions, SQL_PARAMS_NAMED, 'ex', false);
        $params = $params + $eparams;
        $select .= " AND id $exceptions";
    }

    if ($firstinitial) {
        $select .= " AND ".$DB->sql_like('firstname', ':fni', false, false);
        $params['fni'] = "$firstinitial%";
    }
    if ($lastinitial) {
        $select .= " AND ".$DB->sql_like('lastname', ':lni', false, false);
        $params['lni'] = "$lastinitial%";
    }

    if ($extraselect) {
        $select .= " AND $extraselect";
        $params = $params + (array)$extraparams;
    }

    if ($get) {
        return $DB->get_records_select('user', $select, $params, $sort, $fields, $page, $recordsperpage);
    } else {
        return $DB->count_records_select('user', $select, $params);
    }
}



function get_users_listing($sort='lastaccess', $dir='ASC', $page=0, $recordsperpage=0,
                           $search='', $firstinitial='', $lastinitial='', $extraselect='',
                           array $extraparams=null, $extracontext = null) {
    global $DB, $CFG;

    $fullname  = $DB->sql_fullname();

    $select = "deleted <> 1 AND id <> :guestid";
    $params = array('guestid' => $CFG->siteguest);

    if (!empty($search)) {
        $search = trim($search);
        $select .= " AND (". $DB->sql_like($fullname, ':search1', false, false).
                   " OR ". $DB->sql_like('email', ':search2', false, false).
                   " OR username = :search3)";
        $params['search1'] = "%$search%";
        $params['search2'] = "%$search%";
        $params['search3'] = "$search";
    }

    if ($firstinitial) {
        $select .= " AND ". $DB->sql_like('firstname', ':fni', false, false);
        $params['fni'] = "$firstinitial%";
    }
    if ($lastinitial) {
        $select .= " AND ". $DB->sql_like('lastname', ':lni', false, false);
        $params['lni'] = "$lastinitial%";
    }

    if ($extraselect) {
        $select .= " AND $extraselect";
        $params = $params + (array)$extraparams;
    }

    if ($sort) {
        $sort = " ORDER BY $sort $dir";
    }

            $extrafields = '';
    if ($extracontext) {
        $extrafields = get_extra_user_fields_sql($extracontext, '', '',
                array('id', 'username', 'email', 'firstname', 'lastname', 'city', 'country',
                'lastaccess', 'confirmed', 'mnethostid'));
    }
    $namefields = get_all_user_name_fields(true);
    $extrafields = "$extrafields, $namefields";

        return $DB->get_records_sql("SELECT id, username, email, city, country, lastaccess, confirmed, mnethostid, suspended $extrafields
                                   FROM {user}
                                  WHERE $select
                                  $sort", $params, $page, $recordsperpage);

}



function get_users_confirmed() {
    global $DB, $CFG;
    return $DB->get_records_sql("SELECT *
                                   FROM {user}
                                  WHERE confirmed = 1 AND deleted = 0 AND id <> ?", array($CFG->siteguest));
}





function get_site() {
    global $SITE, $DB;

    if (!empty($SITE->id)) {           return $SITE;
    }

    if ($course = $DB->get_record('course', array('category'=>0))) {
        return $course;
    } else {
                        throw new moodle_exception('nosite', 'error');
    }
}


function get_course($courseid, $clone = true) {
    global $DB, $COURSE, $SITE;
    if (!empty($COURSE->id) && $COURSE->id == $courseid) {
        return $clone ? clone($COURSE) : $COURSE;
    } else if (!empty($SITE->id) && $SITE->id == $courseid) {
        return $clone ? clone($SITE) : $SITE;
    } else {
        return $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    }
}


function get_courses($categoryid="all", $sort="c.sortorder ASC", $fields="c.*") {

    global $USER, $CFG, $DB;

    $params = array();

    if ($categoryid !== "all" && is_numeric($categoryid)) {
        $categoryselect = "WHERE c.category = :catid";
        $params['catid'] = $categoryid;
    } else {
        $categoryselect = "";
    }

    if (empty($sort)) {
        $sortstatement = "";
    } else {
        $sortstatement = "ORDER BY $sort";
    }

    $visiblecourses = array();

    $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
    $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
    $params['contextlevel'] = CONTEXT_COURSE;

    $sql = "SELECT $fields $ccselect
              FROM {course} c
           $ccjoin
              $categoryselect
              $sortstatement";

        if ($courses = $DB->get_records_sql($sql, $params)) {

                foreach ($courses as $course) {
            context_helper::preload_from_record($course);
            if (isset($course->visible) && $course->visible <= 0) {
                                if (has_capability('moodle/course:viewhiddencourses', context_course::instance($course->id))) {
                    $visiblecourses [$course->id] = $course;
                }
            } else {
                $visiblecourses [$course->id] = $course;
            }
        }
    }
    return $visiblecourses;
}



function get_courses_page($categoryid="all", $sort="c.sortorder ASC", $fields="c.*",
                          &$totalcount, $limitfrom="", $limitnum="") {
    global $USER, $CFG, $DB;

    $params = array();

    $categoryselect = "";
    if ($categoryid !== "all" && is_numeric($categoryid)) {
        $categoryselect = "WHERE c.category = :catid";
        $params['catid'] = $categoryid;
    } else {
        $categoryselect = "";
    }

    $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
    $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
    $params['contextlevel'] = CONTEXT_COURSE;

    $totalcount = 0;
    if (!$limitfrom) {
        $limitfrom = 0;
    }
    $visiblecourses = array();

    $sql = "SELECT $fields $ccselect
              FROM {course} c
              $ccjoin
           $categoryselect
          ORDER BY $sort";

        $rs = $DB->get_recordset_sql($sql, $params);
        foreach($rs as $course) {
        context_helper::preload_from_record($course);
        if ($course->visible <= 0) {
                        if (has_capability('moodle/course:viewhiddencourses', context_course::instance($course->id))) {
                $totalcount++;
                if ($totalcount > $limitfrom && (!$limitnum or count($visiblecourses) < $limitnum)) {
                    $visiblecourses [$course->id] = $course;
                }
            }
        } else {
            $totalcount++;
            if ($totalcount > $limitfrom && (!$limitnum or count($visiblecourses) < $limitnum)) {
                $visiblecourses [$course->id] = $course;
            }
        }
    }
    $rs->close();
    return $visiblecourses;
}


function get_courses_search($searchterms, $sort, $page, $recordsperpage, &$totalcount,
                            $requiredcapabilities = array()) {
    global $CFG, $DB;

    if ($DB->sql_regex_supported()) {
        $REGEXP    = $DB->sql_regex(true);
        $NOTREGEXP = $DB->sql_regex(false);
    }

    $searchcond = array();
    $params     = array();
    $i = 0;

        if ($DB->get_dbfamily() == 'oracle') {
        $concat = "(c.summary|| ' ' || c.fullname || ' ' || c.idnumber || ' ' || c.shortname)";
    } else {
        $concat = $DB->sql_concat("COALESCE(c.summary, '')", "' '", 'c.fullname', "' '", 'c.idnumber', "' '", 'c.shortname');
    }

    foreach ($searchterms as $searchterm) {
        $i++;

        $NOT = false;                    
                if (!$DB->sql_regex_supported()) {
            if (substr($searchterm, 0, 1) == '-') {
                $NOT = true;
            }
            $searchterm = trim($searchterm, '+-');
        }

        
        if (substr($searchterm,0,1) == '+') {
            $searchterm = trim($searchterm, '+-');
            $searchterm = preg_quote($searchterm, '|');
            $searchcond[] = "$concat $REGEXP :ss$i";
            $params['ss'.$i] = "(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)";

        } else if (substr($searchterm,0,1) == "-") {
            $searchterm = trim($searchterm, '+-');
            $searchterm = preg_quote($searchterm, '|');
            $searchcond[] = "$concat $NOTREGEXP :ss$i";
            $params['ss'.$i] = "(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)";

        } else {
            $searchcond[] = $DB->sql_like($concat,":ss$i", false, true, $NOT);
            $params['ss'.$i] = "%$searchterm%";
        }
    }

    if (empty($searchcond)) {
        $searchcond = array('1 = 1');
    }

    $searchcond = implode(" AND ", $searchcond);

    $courses = array();
    $c = 0; 
        $limitfrom = $page * $recordsperpage;
    $limitto   = $limitfrom + $recordsperpage;

    $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
    $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
    $params['contextlevel'] = CONTEXT_COURSE;

    $sql = "SELECT c.* $ccselect
              FROM {course} c
           $ccjoin
             WHERE $searchcond AND c.id <> ".SITEID."
          ORDER BY $sort";

    $rs = $DB->get_recordset_sql($sql, $params);
    foreach($rs as $course) {
                context_helper::preload_from_record($course);
        $coursecontext = context_course::instance($course->id);
        if (!$course->visible && !has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
            continue;
        }
        if (!empty($requiredcapabilities)) {
            if (!has_all_capabilities($requiredcapabilities, $coursecontext)) {
                continue;
            }
        }
                                if ($c >= $limitfrom && $c < $limitto) {
            $courses[$course->id] = $course;
        }
        $c++;
    }
    $rs->close();

            $totalcount = $c;
    return $courses;
}


function fix_course_sortorder() {
    global $DB, $SITE;

    
            $cacheevents = array();

    if ($unsorted = $DB->get_records('course_categories', array('sortorder'=>0))) {
                $DB->set_field('course_categories', 'sortorder', MAX_COURSES_IN_CATEGORY*MAX_COURSE_CATEGORIES, array('sortorder'=>0));
        $cacheevents['changesincoursecat'] = true;
    }

    $allcats = $DB->get_records('course_categories', null, 'sortorder, id', 'id, sortorder, parent, depth, path');
    $topcats    = array();
    $brokencats = array();
    foreach ($allcats as $cat) {
        $sortorder = (int)$cat->sortorder;
        if (!$cat->parent) {
            while(isset($topcats[$sortorder])) {
                $sortorder++;
            }
            $topcats[$sortorder] = $cat;
            continue;
        }
        if (!isset($allcats[$cat->parent])) {
            $brokencats[] = $cat;
            continue;
        }
        if (!isset($allcats[$cat->parent]->children)) {
            $allcats[$cat->parent]->children = array();
        }
        while(isset($allcats[$cat->parent]->children[$sortorder])) {
            $sortorder++;
        }
        $allcats[$cat->parent]->children[$sortorder] = $cat;
    }
    unset($allcats);

        if ($brokencats) {
        $defaultcat = reset($topcats);
        foreach ($brokencats as $cat) {
            $topcats[] = $cat;
        }
    }

        $sortorder = 0;
    $fixcontexts = array();
    if (_fix_course_cats($topcats, $sortorder, 0, 0, '', $fixcontexts)) {
        $cacheevents['changesincoursecat'] = true;
    }

        $frontcourses = $DB->get_records('course', array('category'=>0), 'id');
    if (count($frontcourses) > 1) {
        if (isset($frontcourses[SITEID])) {
            $frontcourse = $frontcourses[SITEID];
            unset($frontcourses[SITEID]);
        } else {
            $frontcourse = array_shift($frontcourses);
        }
        $defaultcat = reset($topcats);
        foreach ($frontcourses as $course) {
            $DB->set_field('course', 'category', $defaultcat->id, array('id'=>$course->id));
            $context = context_course::instance($course->id);
            $fixcontexts[$context->id] = $context;
            $cacheevents['changesincourse'] = true;
        }
        unset($frontcourses);
    } else {
        $frontcourse = reset($frontcourses);
    }

        if ($fixcontexts) {
        foreach ($fixcontexts as $fixcontext) {
            $fixcontext->reset_paths(false);
        }
        context_helper::build_all_paths(false);
        unset($fixcontexts);
        $cacheevents['changesincourse'] = true;
        $cacheevents['changesincoursecat'] = true;
    }

        unset($topcats);
    unset($brokencats);
    unset($fixcontexts);

        if ($frontcourse->sortorder != 1) {
        $DB->set_field('course', 'sortorder', 1, array('id'=>$frontcourse->id));
        $cacheevents['changesincourse'] = true;
    }

        $sql = "SELECT cc.id, cc.coursecount, COUNT(c.id) AS newcount
              FROM {course_categories} cc
              LEFT JOIN {course} c ON c.category = cc.id
          GROUP BY cc.id, cc.coursecount
            HAVING cc.coursecount <> COUNT(c.id)";

    if ($updatecounts = $DB->get_records_sql($sql)) {
                $categories = array();
        foreach ($updatecounts as $cat) {
            $cat->coursecount = $cat->newcount;
            if ($cat->coursecount >= MAX_COURSES_IN_CATEGORY) {
                $categories[] = $cat->id;
            }
            unset($cat->newcount);
            $DB->update_record_raw('course_categories', $cat, true);
        }
        if (!empty($categories)) {
            $str = implode(', ', $categories);
            debugging("The number of courses (category id: $str) has reached MAX_COURSES_IN_CATEGORY (" . MAX_COURSES_IN_CATEGORY . "), it will cause a sorting performance issue, please increase the value of MAX_COURSES_IN_CATEGORY in lib/datalib.php file. See tracker issue: MDL-25669", DEBUG_DEVELOPER);
        }
        $cacheevents['changesincoursecat'] = true;
    }

        $sql = "SELECT DISTINCT cc.id, cc.sortorder
              FROM {course_categories} cc
              JOIN {course} c ON c.category = cc.id
             WHERE c.sortorder < cc.sortorder OR c.sortorder > cc.sortorder + ".MAX_COURSES_IN_CATEGORY;

    if ($fixcategories = $DB->get_records_sql($sql)) {
                foreach ($fixcategories as $cat) {
            $sql = "UPDATE {course}
                       SET sortorder = ".$DB->sql_modulo('sortorder', MAX_COURSES_IN_CATEGORY)." + ?
                     WHERE category = ?";
            $DB->execute($sql, array($cat->sortorder, $cat->id));
        }
        $cacheevents['changesincoursecat'] = true;
    }
    unset($fixcategories);

        $sql = "SELECT DISTINCT c1.category AS id , cc.sortorder
              FROM {course} c1
              JOIN {course} c2 ON c1.sortorder = c2.sortorder
              JOIN {course_categories} cc ON (c1.category = cc.id)
             WHERE c1.id <> c2.id";
    $fixcategories = $DB->get_records_sql($sql);

    $sql = "SELECT cc.id, cc.sortorder, cc.coursecount, MAX(c.sortorder) AS maxsort, MIN(c.sortorder) AS minsort
              FROM {course_categories} cc
              JOIN {course} c ON c.category = cc.id
          GROUP BY cc.id, cc.sortorder, cc.coursecount
            HAVING (MAX(c.sortorder) <>  cc.sortorder + cc.coursecount) OR (MIN(c.sortorder) <>  cc.sortorder + 1)";
    $gapcategories = $DB->get_records_sql($sql);

    foreach ($gapcategories as $cat) {
        if (isset($fixcategories[$cat->id])) {
            
        } else if ($cat->minsort == $cat->sortorder and $cat->maxsort == $cat->sortorder + $cat->coursecount - 1) {
                        $sql = "UPDATE {course}
                       SET sortorder = sortorder + 1
                     WHERE category = ?";
            $DB->execute($sql, array($cat->id));

        } else {
                        $fixcategories[$cat->id] = $cat;
        }
        $cacheevents['changesincourse'] = true;
    }
    unset($gapcategories);

        foreach ($fixcategories as $cat) {
        $i = 1;
        $courses = $DB->get_records('course', array('category'=>$cat->id), 'sortorder ASC, id DESC', 'id, sortorder');
        foreach ($courses as $course) {
            if ($course->sortorder != $cat->sortorder + $i) {
                $course->sortorder = $cat->sortorder + $i;
                $DB->update_record_raw('course', $course, true);
                $cacheevents['changesincourse'] = true;
            }
            $i++;
        }
    }

        foreach (array_keys($cacheevents) as $event) {
        cache_helper::purge_by_event($event);
    }
}


function _fix_course_cats($children, &$sortorder, $parent, $depth, $path, &$fixcontexts) {
    global $DB;

    $depth++;
    $changesmade = false;

    foreach ($children as $cat) {
        $sortorder = $sortorder + MAX_COURSES_IN_CATEGORY;
        $update = false;
        if ($parent != $cat->parent or $depth != $cat->depth or $path.'/'.$cat->id != $cat->path) {
            $cat->parent = $parent;
            $cat->depth  = $depth;
            $cat->path   = $path.'/'.$cat->id;
            $update = true;

                        $context = context_coursecat::instance($cat->id);
            $fixcontexts[$context->id] = $context;
        }
        if ($cat->sortorder != $sortorder) {
            $cat->sortorder = $sortorder;
            $update = true;
        }
        if ($update) {
            $DB->update_record('course_categories', $cat, true);
            $changesmade = true;
        }
        if (isset($cat->children)) {
            if (_fix_course_cats($cat->children, $sortorder, $cat->id, $cat->depth, $cat->path, $fixcontexts)) {
                $changesmade = true;
            }
        }
    }
    return $changesmade;
}


function get_my_remotecourses($userid=0) {
    global $DB, $USER;

    if (empty($userid)) {
        $userid = $USER->id;
    }

        $sql = "SELECT c.id, c.remoteid, c.shortname, c.fullname,
                   c.hostid, c.summary, c.summaryformat, c.categoryname AS cat_name,
                   h.name AS hostname
              FROM {mnetservice_enrol_courses} c
              JOIN (SELECT DISTINCT hostid, remotecourseid
                      FROM {mnetservice_enrol_enrolments}
                     WHERE userid = ?
                   ) e ON (e.hostid = c.hostid AND e.remotecourseid = c.remoteid)
              JOIN {mnet_host} h ON h.id = c.hostid";

    return $DB->get_records_sql($sql, array($userid));
}


function get_my_remotehosts() {
    global $CFG, $USER;

    if ($USER->mnethostid == $CFG->mnet_localhost_id) {
        return false;     }
    if (!empty($USER->mnet_foreign_host_array) && is_array($USER->mnet_foreign_host_array)) {
        return $USER->mnet_foreign_host_array;
    }
    return false;
}



function get_scales_menu($courseid=0) {
    global $DB;

    $sql = "SELECT id, name
              FROM {scale}
             WHERE courseid = 0 or courseid = ?
          ORDER BY courseid ASC, name ASC";
    $params = array($courseid);

    return $scales = $DB->get_records_sql_menu($sql, $params);
}


function increment_revision_number($table, $field, $select, array $params = null) {
    global $DB;

    $now = time();
    $sql = "UPDATE {{$table}}
                   SET $field = (CASE
                       WHEN $field IS NULL THEN $now
                       WHEN $field < $now THEN $now
                       WHEN $field > $now + 3600 THEN $now
                       ELSE $field + 1 END)";
    if ($select) {
        $sql = $sql . " WHERE $select";
    }
    $DB->execute($sql, $params);
}




function get_course_mods($courseid) {
    global $DB;

    if (empty($courseid)) {
        return false;     }

    return $DB->get_records_sql("SELECT cm.*, m.name as modname
                                   FROM {modules} m, {course_modules} cm
                                  WHERE cm.course = ? AND cm.module = m.id AND m.visible = 1",
                                array($courseid)); }



function get_coursemodule_from_id($modulename, $cmid, $courseid=0, $sectionnum=false, $strictness=IGNORE_MISSING) {
    global $DB;

    $params = array('cmid'=>$cmid);

    if (!$modulename) {
        if (!$modulename = $DB->get_field_sql("SELECT md.name
                                                 FROM {modules} md
                                                 JOIN {course_modules} cm ON cm.module = md.id
                                                WHERE cm.id = :cmid", $params, $strictness)) {
            return false;
        }
    } else {
        if (!core_component::is_valid_plugin_name('mod', $modulename)) {
            throw new coding_exception('Invalid modulename parameter');
        }
    }

    $params['modulename'] = $modulename;

    $courseselect = "";
    $sectionfield = "";
    $sectionjoin  = "";

    if ($courseid) {
        $courseselect = "AND cm.course = :courseid";
        $params['courseid'] = $courseid;
    }

    if ($sectionnum) {
        $sectionfield = ", cw.section AS sectionnum";
        $sectionjoin  = "LEFT JOIN {course_sections} cw ON cw.id = cm.section";
    }

    $sql = "SELECT cm.*, m.name, md.name AS modname $sectionfield
              FROM {course_modules} cm
                   JOIN {modules} md ON md.id = cm.module
                   JOIN {".$modulename."} m ON m.id = cm.instance
                   $sectionjoin
             WHERE cm.id = :cmid AND md.name = :modulename
                   $courseselect";

    return $DB->get_record_sql($sql, $params, $strictness);
}


function get_coursemodule_from_instance($modulename, $instance, $courseid=0, $sectionnum=false, $strictness=IGNORE_MISSING) {
    global $DB;

    if (!core_component::is_valid_plugin_name('mod', $modulename)) {
        throw new coding_exception('Invalid modulename parameter');
    }

    $params = array('instance'=>$instance, 'modulename'=>$modulename);

    $courseselect = "";
    $sectionfield = "";
    $sectionjoin  = "";

    if ($courseid) {
        $courseselect = "AND cm.course = :courseid";
        $params['courseid'] = $courseid;
    }

    if ($sectionnum) {
        $sectionfield = ", cw.section AS sectionnum";
        $sectionjoin  = "LEFT JOIN {course_sections} cw ON cw.id = cm.section";
    }

    $sql = "SELECT cm.*, m.name, md.name AS modname $sectionfield
              FROM {course_modules} cm
                   JOIN {modules} md ON md.id = cm.module
                   JOIN {".$modulename."} m ON m.id = cm.instance
                   $sectionjoin
             WHERE m.id = :instance AND md.name = :modulename
                   $courseselect";

    return $DB->get_record_sql($sql, $params, $strictness);
}


function get_coursemodules_in_course($modulename, $courseid, $extrafields='') {
    global $DB;

    if (!core_component::is_valid_plugin_name('mod', $modulename)) {
        throw new coding_exception('Invalid modulename parameter');
    }

    if (!empty($extrafields)) {
        $extrafields = ", $extrafields";
    }
    $params = array();
    $params['courseid'] = $courseid;
    $params['modulename'] = $modulename;


    return $DB->get_records_sql("SELECT cm.*, m.name, md.name as modname $extrafields
                                   FROM {course_modules} cm, {modules} md, {".$modulename."} m
                                  WHERE cm.course = :courseid AND
                                        cm.instance = m.id AND
                                        md.name = :modulename AND
                                        md.id = cm.module", $params);
}


function get_all_instances_in_courses($modulename, $courses, $userid=NULL, $includeinvisible=false) {
    global $CFG, $DB;

    if (!core_component::is_valid_plugin_name('mod', $modulename)) {
        throw new coding_exception('Invalid modulename parameter');
    }

    $outputarray = array();

    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return $outputarray;
    }

    list($coursessql, $params) = $DB->get_in_or_equal(array_keys($courses), SQL_PARAMS_NAMED, 'c0');
    $params['modulename'] = $modulename;

    if (!$rawmods = $DB->get_records_sql("SELECT cm.id AS coursemodule, m.*, cw.section, cm.visible AS visible,
                                                 cm.groupmode, cm.groupingid
                                            FROM {course_modules} cm, {course_sections} cw, {modules} md,
                                                 {".$modulename."} m
                                           WHERE cm.course $coursessql AND
                                                 cm.instance = m.id AND
                                                 cm.section = cw.id AND
                                                 md.name = :modulename AND
                                                 md.id = cm.module", $params)) {
        return $outputarray;
    }

    foreach ($courses as $course) {
        $modinfo = get_fast_modinfo($course, $userid);

        if (empty($modinfo->instances[$modulename])) {
            continue;
        }

        foreach ($modinfo->instances[$modulename] as $cm) {
            if (!$includeinvisible and !$cm->uservisible) {
                continue;
            }
            if (!isset($rawmods[$cm->id])) {
                continue;
            }
            $instance = $rawmods[$cm->id];
            if (!empty($cm->extra)) {
                $instance->extra = $cm->extra;
            }
            $outputarray[] = $instance;
        }
    }

    return $outputarray;
}


function get_all_instances_in_course($modulename, $course, $userid=NULL, $includeinvisible=false) {
    return get_all_instances_in_courses($modulename, array($course->id => $course), $userid, $includeinvisible);
}



function instance_is_visible($moduletype, $module) {
    global $DB;

    if (!empty($module->id)) {
        $params = array('courseid'=>$module->course, 'moduletype'=>$moduletype, 'moduleid'=>$module->id);
        if ($records = $DB->get_records_sql("SELECT cm.instance, cm.visible, cm.groupingid, cm.id, cm.course
                                               FROM {course_modules} cm, {modules} m
                                              WHERE cm.course = :courseid AND
                                                    cm.module = m.id AND
                                                    m.name = :moduletype AND
                                                    cm.instance = :moduleid", $params)) {

            foreach ($records as $record) {                 return $record->visible;
            }
        }
    }
    return true;  }




function get_log_manager($forcereload = false) {
    
    static $singleton = null;

    if ($forcereload and isset($singleton)) {
        $singleton->dispose();
        $singleton = null;
    }

    if (isset($singleton)) {
        return $singleton;
    }

    $classname = '\tool_log\log\manager';
    if (defined('LOG_MANAGER_CLASS')) {
        $classname = LOG_MANAGER_CLASS;
    }

    if (!class_exists($classname)) {
        if (!empty($classname)) {
            debugging("Cannot find log manager class '$classname'.", DEBUG_DEVELOPER);
        }
        $classname = '\core\log\dummy_manager';
    }

    $singleton = new $classname();
    return $singleton;
}


function add_to_config_log($name, $oldvalue, $value, $plugin) {
    global $USER, $DB;

    $log = new stdClass();
    $log->userid       = during_initial_install() ? 0 :$USER->id;     $log->timemodified = time();
    $log->name         = $name;
    $log->oldvalue  = $oldvalue;
    $log->value     = $value;
    $log->plugin    = $plugin;
    $DB->insert_record('config_log', $log);
}


function user_accesstime_log($courseid=0) {
    global $USER, $CFG, $DB;

    if (!isloggedin() or \core\session\manager::is_loggedinas()) {
                return;
    }

    if (isguestuser()) {
                return;
    }

    if (empty($courseid)) {
        $courseid = SITEID;
    }

    $timenow = time();

    if ($timenow - $USER->lastaccess > LASTACCESS_UPDATE_SECS) {
            $USER->lastaccess = $timenow;

        $last = new stdClass();
        $last->id         = $USER->id;
        $last->lastip     = getremoteaddr();
        $last->lastaccess = $timenow;

        $DB->update_record_raw('user', $last);
    }

    if ($courseid == SITEID) {
            return;
    }

    if (empty($USER->currentcourseaccess[$courseid]) or ($timenow - $USER->currentcourseaccess[$courseid] > LASTACCESS_UPDATE_SECS)) {

        $lastaccess = $DB->get_field('user_lastaccess', 'timeaccess', array('userid'=>$USER->id, 'courseid'=>$courseid));

        if ($lastaccess === false) {
                        $USER->currentcourseaccess[$courseid] = $timenow;

            $last = new stdClass();
            $last->userid     = $USER->id;
            $last->courseid   = $courseid;
            $last->timeaccess = $timenow;
            try {
                $DB->insert_record_raw('user_lastaccess', $last, false);
            } catch (dml_write_exception $e) {
                                                $lastaccess = $DB->get_field('user_lastaccess', 'timeaccess', array('userid' => $USER->id,
                                                                                    'courseid' => $courseid));
                if ($lastaccess === false) {
                    throw $e;
                }
                                            }

        } else if ($timenow - $lastaccess <  LASTACCESS_UPDATE_SECS) {
            
        } else {
                        $USER->currentcourseaccess[$courseid] = $timenow;

            $DB->set_field('user_lastaccess', 'timeaccess', $timenow, array('userid'=>$USER->id, 'courseid'=>$courseid));
        }
    }
}


function get_logs($select, array $params=null, $order='l.time DESC', $limitfrom='', $limitnum='', &$totalcount) {
    global $DB;

    if ($order) {
        $order = "ORDER BY $order";
    }

    $selectsql = "";
    $countsql  = "";

    if ($select) {
        $select = "WHERE $select";
    }

    $sql = "SELECT COUNT(*)
              FROM {log} l
           $select";

    $totalcount = $DB->count_records_sql($sql, $params);
    $allnames = get_all_user_name_fields(true, 'u');
    $sql = "SELECT l.*, $allnames, u.picture
              FROM {log} l
              LEFT JOIN {user} u ON l.userid = u.id
           $select
            $order";

    return $DB->get_records_sql($sql, $params, $limitfrom, $limitnum) ;
}



function get_logs_usercourse($userid, $courseid, $coursestart) {
    global $DB;

    $params = array();

    $courseselect = '';
    if ($courseid) {
        $courseselect = "AND course = :courseid";
        $params['courseid'] = $courseid;
    }
    $params['userid'] = $userid;
                $coursestart = (int)$coursestart;

    return $DB->get_records_sql("SELECT FLOOR((time - $coursestart)/". DAYSECS .") AS day, COUNT(*) AS num
                                   FROM {log}
                                  WHERE userid = :userid
                                        AND time > $coursestart $courseselect
                               GROUP BY FLOOR((time - $coursestart)/". DAYSECS .")", $params);
}


function get_logs_userday($userid, $courseid, $daystart) {
    global $DB;

    $params = array('userid'=>$userid);

    $courseselect = '';
    if ($courseid) {
        $courseselect = "AND course = :courseid";
        $params['courseid'] = $courseid;
    }
    $daystart = (int)$daystart; 
    return $DB->get_records_sql("SELECT FLOOR((time - $daystart)/". HOURSECS .") AS hour, COUNT(*) AS num
                                   FROM {log}
                                  WHERE userid = :userid
                                        AND time > $daystart $courseselect
                               GROUP BY FLOOR((time - $daystart)/". HOURSECS .") ", $params);
}



function print_object($object) {

        raise_memory_limit(MEMORY_EXTRA);

    if (CLI_SCRIPT) {
        fwrite(STDERR, print_r($object, true));
        fwrite(STDERR, PHP_EOL);
    } else {
        echo html_writer::tag('pre', s(print_r($object, true)), array('class' => 'notifytiny'));
    }
}


function xmldb_debug($message, $object) {

    debugging($message, DEBUG_DEVELOPER);
}


function user_can_create_courses() {
    global $DB;
    $catsrs = $DB->get_recordset('course_categories');
    foreach ($catsrs as $cat) {
        if (has_capability('moodle/course:create', context_coursecat::instance($cat->id))) {
            $catsrs->close();
            return true;
        }
    }
    $catsrs->close();
    return false;
}


function update_field_with_unique_index($table, $field, array $newvalues,
        array $otherconditions, $unusedvalue = -1) {
    global $DB;
    $safechanges = decompose_update_into_safe_changes($newvalues, $unusedvalue);

    $transaction = $DB->start_delegated_transaction();
    foreach ($safechanges as $change) {
        list($from, $to) = $change;
        $otherconditions[$field] = $from;
        $DB->set_field($table, $field, $to, $otherconditions);
    }
    $transaction->allow_commit();
}


function decompose_update_into_safe_changes(array $newvalues, $unusedvalue) {
    $nontrivialmap = array();
    foreach ($newvalues as $from => $to) {
        if ($from == $unusedvalue || $to == $unusedvalue) {
            throw new \coding_exception('Supposedly unused value ' . $unusedvalue . ' is actually used!');
        }
        if ($from != $to) {
            $nontrivialmap[$from] = $to;
        }
    }

    if (empty($nontrivialmap)) {
        return array();
    }

                $safechanges = array();
    $nontrivialmapchanged = true;
    while ($nontrivialmapchanged) {
        $nontrivialmapchanged = false;

        foreach ($nontrivialmap as $from => $to) {
            if (array_key_exists($to, $nontrivialmap)) {
                continue;             }
                        $safechanges[] = array($from, $to);
            unset($nontrivialmap[$from]);
            $nontrivialmapchanged = true;
        }
    }

        if (empty($nontrivialmap)) {
        return $safechanges;
    }

            while (!empty($nontrivialmap)) {
                reset($nontrivialmap);
        $current = $cyclestart = key($nontrivialmap);
        $cycle = array();
        do {
            $cycle[] = $current;
            $next = $nontrivialmap[$current];
            unset($nontrivialmap[$current]);
            $current = $next;
        } while ($current != $cyclestart);

                $safechanges[] = array($cyclestart, $unusedvalue);
        $cycle[0] = $unusedvalue;
        $to = $cyclestart;
        while ($from = array_pop($cycle)) {
            $safechanges[] = array($from, $to);
            $to = $from;
        }
    }

    return $safechanges;
}
