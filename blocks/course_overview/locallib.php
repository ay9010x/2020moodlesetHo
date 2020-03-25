<?php



define('BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_NONE', '0');
define('BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_ONLY_PARENT_NAME', '1');
define('BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_FULL_PATH', '2');


function block_course_overview_get_overviews($courses) {
    $htmlarray = array();
    if ($modules = get_plugin_list_with_function('mod', 'print_overview')) {
                        if (defined('MAX_MODINFO_CACHE_SIZE') && MAX_MODINFO_CACHE_SIZE > 0 && count($courses) > MAX_MODINFO_CACHE_SIZE) {
            $batches = array_chunk($courses, MAX_MODINFO_CACHE_SIZE, true);
        } else {
            $batches = array($courses);
        }
        foreach ($batches as $courses) {
            foreach ($modules as $fname) {
                $fname($courses, $htmlarray);
            }
        }
    }
    return $htmlarray;
}


function block_course_overview_update_mynumber($number) {
    set_user_preference('course_overview_number_of_courses', $number);
}


function block_course_overview_update_myorder($sortorder) {
    $value = implode(',', $sortorder);
    if (core_text::strlen($value) > 1333) {
                $value = preg_replace('/,[\d]*$/', '', core_text::substr($value, 0, 1334));
    }
    set_user_preference('course_overview_course_sortorder', $value);
}


function block_course_overview_get_myorder() {
    if ($value = get_user_preferences('course_overview_course_sortorder')) {
        return explode(',', $value);
    }
        $order = array();
    if ($value = get_user_preferences('course_overview_course_order')) {
        $order = unserialize($value);
        block_course_overview_update_myorder($order);
        unset_user_preference('course_overview_course_order');
    }
    return $order;
}


function block_course_overview_get_child_shortnames($courseid) {
    global $DB;
    $ctxselect = context_helper::get_preload_record_columns_sql('ctx');
    $sql = "SELECT c.id, c.shortname, $ctxselect
            FROM {enrol} e
            JOIN {course} c ON (c.id = e.customint1)
            JOIN {context} ctx ON (ctx.instanceid = e.customint1)
            WHERE e.courseid = :courseid AND e.enrol = :method AND ctx.contextlevel = :contextlevel ORDER BY e.sortorder";
    $params = array('method' => 'meta', 'courseid' => $courseid, 'contextlevel' => CONTEXT_COURSE);

    if ($results = $DB->get_records_sql($sql, $params)) {
        $shortnames = array();
                foreach ($results as $res) {
            context_helper::preload_from_record($res);
            $context = context_course::instance($res->id);
            $shortnames[] = format_string($res->shortname, true, $context);
        }
        $total = count($shortnames);
        $suffix = '';
        if ($total > 10) {
            $shortnames = array_slice($shortnames, 0, 10);
            $diff = $total - count($shortnames);
            if ($diff > 1) {
                $suffix = get_string('shortnamesufixprural', 'block_course_overview', $diff);
            } else {
                $suffix = get_string('shortnamesufixsingular', 'block_course_overview', $diff);
            }
        }
        $shortnames = get_string('shortnameprefix', 'block_course_overview', implode('; ', $shortnames));
        $shortnames .= $suffix;
    }

    return isset($shortnames) ? $shortnames : false;
}


function block_course_overview_get_max_user_courses($showallcourses = false) {
        $config = get_config('block_course_overview');
    $limit = $config->defaultmaxcourses;

        if (empty($config->forcedefaultmaxcourses)) {
        if ($showallcourses) {
            $limit = 0;
        } else {
            $limit = get_user_preferences('course_overview_number_of_courses', $limit);
        }
    }
    return $limit;
}


function block_course_overview_get_sorted_courses($showallcourses = false) {
    global $USER;

    $limit = block_course_overview_get_max_user_courses($showallcourses);

    $courses = enrol_get_my_courses('enddate');        // by YCJ
    $site = get_site();

    if (array_key_exists($site->id,$courses)) {
        unset($courses[$site->id]);
    }

    foreach ($courses as $c) {
        if (isset($USER->lastcourseaccess[$c->id])) {
            $courses[$c->id]->lastaccess = $USER->lastcourseaccess[$c->id];
        } else {
            $courses[$c->id]->lastaccess = 0;
        }
    }
    
    // Get remote courses. Note that here lacks of the field 'enddate'.
        $remotecourses = array();
    if (is_enabled_auth('mnet')) {
        $remotecourses = get_my_remotecourses();
    }
        foreach ($remotecourses as $id => $val) {
        $remoteid = $val->remoteid * -1;
        $val->id = $remoteid;
        $courses[$remoteid] = $val;
    }

    $order = block_course_overview_get_myorder();

    // Get unexpired courses in sort order into list.
    $sortedcourses = array();
    $counter = 0;
    $time = time();          // by YCJ
    foreach ($order as $key => $cid) {
        if (($counter >= $limit) && ($limit != 0)) {
            break;
        }
                                      
        if (isset($courses[$cid])) {
           if (isset($courses[$cid]->enddate) && ($courses[$cid]->enddate < $time)) continue;          // by YCJ

           $sortedcourses[$cid] = $courses[$cid];
           $counter++;
        }
    }
    
    // Append unsorted courses if limit allows.
    foreach ($courses as $c) {
        if (($limit != 0) && ($counter >= $limit)) {
            break;
        }
        if (!in_array($c->id, $order)) {
        	  if (isset($c->enddate) && ($c->enddate < $time)) continue;          // by YCJ

            $sortedcourses[$c->id] = $c;
            $counter++;
        }
    }

        $sitecourses = array();
    foreach ($sortedcourses as $key => $course) {
        if ($course->id > 0) {
            $sitecourses[$key] = $course;
        }
    }
    return array($sortedcourses, $sitecourses, count($courses));
}
