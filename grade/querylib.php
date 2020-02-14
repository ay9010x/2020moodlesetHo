<?php




function grade_get_course_grades($courseid, $userid_or_ids=null) {

    $grade_item = grade_item::fetch_course_item($courseid);

    if ($grade_item->needsupdate) {
        grade_regrade_final_grades($courseid);
    }

    $item = new stdClass();
    $item->scaleid    = $grade_item->scaleid;
    $item->name       = $grade_item->get_name();
    $item->grademin   = $grade_item->grademin;
    $item->grademax   = $grade_item->grademax;
    $item->gradepass  = $grade_item->gradepass;
    $item->locked     = $grade_item->is_locked();
    $item->hidden     = $grade_item->is_hidden();
    $item->grades     = array();

    switch ($grade_item->gradetype) {
        case GRADE_TYPE_NONE:
            continue;

        case GRADE_TYPE_VALUE:
            $item->scaleid = 0;
            break;

        case GRADE_TYPE_TEXT:
            $item->scaleid   = 0;
            $item->grademin   = 0;
            $item->grademax   = 0;
            $item->gradepass  = 0;
            break;
    }

    if (empty($userid_or_ids)) {
        $userids = array();

    } else if (is_array($userid_or_ids)) {
        $userids = $userid_or_ids;

    } else {
        $userids = array($userid_or_ids);
    }

    if ($userids) {
        $grade_grades = grade_grade::fetch_users_grades($grade_item, $userids, true);
        foreach ($userids as $userid) {
            $grade_grades[$userid]->grade_item =& $grade_item;

            $grade = new stdClass();
            $grade->grade          = $grade_grades[$userid]->finalgrade;
            $grade->locked         = $grade_grades[$userid]->is_locked();
            $grade->hidden         = $grade_grades[$userid]->is_hidden();
            $grade->overridden     = $grade_grades[$userid]->overridden;
            $grade->feedback       = $grade_grades[$userid]->feedback;
            $grade->feedbackformat = $grade_grades[$userid]->feedbackformat;
            $grade->usermodified   = $grade_grades[$userid]->usermodified;
            $grade->dategraded     = $grade_grades[$userid]->get_dategraded();
            $grade->datesubmitted  = $grade_grades[$userid]->get_datesubmitted();

                        if ($grade_item->needsupdate) {
                $grade->grade          = false;
                $grade->str_grade      = get_string('error');
                $grade->str_long_grade = $grade->str_grade;

            } else if (is_null($grade->grade)) {
                $grade->str_grade      = '-';
                $grade->str_long_grade = $grade->str_grade;

            } else {
                $grade->str_grade = grade_format_gradevalue($grade->grade, $grade_item);
                if ($grade_item->gradetype == GRADE_TYPE_SCALE or $grade_item->get_displaytype() != GRADE_DISPLAY_TYPE_REAL) {
                    $grade->str_long_grade = $grade->str_grade;
                } else {
                    $a = new stdClass();
                    $a->grade = $grade->str_grade;
                    $a->max   = grade_format_gradevalue($grade_item->grademax, $grade_item);
                    $grade->str_long_grade = get_string('gradelong', 'grades', $a);
                }
            }

                        if (is_null($grade->feedback)) {
                $grade->str_feedback = '';
            } else {
                $grade->str_feedback = format_text($grade->feedback, $grade->feedbackformat);
            }

            $item->grades[$userid] = $grade;
        }
    }

    return $item;
}


function grade_get_course_grade($userid, $courseid_or_ids=null) {

    if (!is_array($courseid_or_ids)) {
        if (empty($courseid_or_ids)) {
            if (!$courses = enrol_get_users_courses($userid)) {
                return false;
            }
            $courseids = array_keys($courses);
            return grade_get_course_grade($userid, $courseids);
        }
        if (!is_numeric($courseid_or_ids)) {
            return false;
        }
        if (!$grades = grade_get_course_grade($userid, array($courseid_or_ids))) {
            return false;
        } else {
                        $grade = reset($grades);
            return $grade;
        }
    }

    foreach ($courseid_or_ids as $courseid) {
        $grade_item = grade_item::fetch_course_item($courseid);
        $course_items[$grade_item->courseid] = $grade_item;
    }

    $grades = array();
    foreach ($course_items as $grade_item) {
        if ($grade_item->needsupdate) {
            grade_regrade_final_grades($courseid);
        }

        $item = new stdClass();
        $item->scaleid    = $grade_item->scaleid;
        $item->name       = $grade_item->get_name();
        $item->grademin   = $grade_item->grademin;
        $item->grademax   = $grade_item->grademax;
        $item->gradepass  = $grade_item->gradepass;
        $item->locked     = $grade_item->is_locked();
        $item->hidden     = $grade_item->is_hidden();

        switch ($grade_item->gradetype) {
            case GRADE_TYPE_NONE:
                continue;

            case GRADE_TYPE_VALUE:
                $item->scaleid = 0;
                break;

            case GRADE_TYPE_TEXT:
                $item->scaleid   = 0;
                $item->grademin   = 0;
                $item->grademax   = 0;
                $item->gradepass  = 0;
                break;
        }
        $grade_grade = new grade_grade(array('userid'=>$userid, 'itemid'=>$grade_item->id));
        $grade_grade->grade_item =& $grade_item;

        $grade = new stdClass();
        $grade->grade          = $grade_grade->finalgrade;
        $grade->locked         = $grade_grade->is_locked();
        $grade->hidden         = $grade_grade->is_hidden();
        $grade->overridden     = $grade_grade->overridden;
        $grade->feedback       = $grade_grade->feedback;
        $grade->feedbackformat = $grade_grade->feedbackformat;
        $grade->usermodified   = $grade_grade->usermodified;
        $grade->dategraded     = $grade_grade->get_dategraded();
        $grade->item           = $item;

                if ($grade_item->needsupdate) {
            $grade->grade          = false;
            $grade->str_grade      = get_string('error');
            $grade->str_long_grade = $grade->str_grade;

        } else if (is_null($grade->grade)) {
            $grade->str_grade      = '-';
            $grade->str_long_grade = $grade->str_grade;

        } else {
            $grade->str_grade = grade_format_gradevalue($grade->grade, $grade_item);
            if ($grade_item->gradetype == GRADE_TYPE_SCALE or $grade_item->get_displaytype() != GRADE_DISPLAY_TYPE_REAL) {
                $grade->str_long_grade = $grade->str_grade;
            } else {
                $a = new stdClass();
                $a->grade = $grade->str_grade;
                $a->max   = grade_format_gradevalue($grade_item->grademax, $grade_item);
                $grade->str_long_grade = get_string('gradelong', 'grades', $a);
            }
        }

                if (is_null($grade->feedback)) {
            $grade->str_feedback = '';
        } else {
            $grade->str_feedback = format_text($grade->feedback, $grade->feedbackformat);
        }

        $grades[$grade_item->courseid] = $grade;
    }

    return $grades;
}


function grade_get_grade_items_for_activity($cm, $only_main_item=false) {
    global $CFG, $DB;

    if (!isset($cm->modname)) {
        $params = array($cm->id);
        $cm = $DB->get_record_sql("SELECT cm.*, md.name as modname
                                    FROM {course_modules} cm,
                                         {modules} md
                                   WHERE cm.id = ? AND md.id = cm.module", $params);
    }


    if (empty($cm) or empty($cm->instance) or empty($cm->course)) {
        debugging("Incorrect cm parameter in grade_get_grade_items_for_activity()!");
        return false;
    }

    if ($only_main_item) {
        return grade_item::fetch_all(array('itemtype'=>'mod', 'itemmodule'=>$cm->modname, 'iteminstance'=>$cm->instance, 'courseid'=>$cm->course, 'itemnumber'=>0));
    } else {
        return grade_item::fetch_all(array('itemtype'=>'mod', 'itemmodule'=>$cm->modname, 'iteminstance'=>$cm->instance, 'courseid'=>$cm->course));
    }
}


function grade_is_user_graded_in_activity($cm, $userid) {

    $grade_items = grade_get_grade_items_for_activity($cm, true);
    if (empty($grade_items)) {
        return false;
    }

    $grade_item = reset($grade_items);

    if ($grade_item->gradetype == GRADE_TYPE_NONE) {
        return false;
    }

    if ($grade_item->needsupdate) {
                grade_regrade_final_grades($grade_item->courseid);
    }

    if (!$grade = $grade_item->get_final($userid)) {
        return false;
    }

    if (is_null($grade->finalgrade)) {
        return false;
    }

    return true;
}


function grade_get_gradable_activities($courseid, $modulename='') {
    global $CFG, $DB;

    if (empty($modulename)) {
        if (!$modules = $DB->get_records('modules', array('visible' => '1'))) {
            return false;
        }
        $result = array();
        foreach ($modules as $module) {
            if ($cms = grade_get_gradable_activities($courseid, $module->name)) {
                $result =  $result + $cms;
            }
        }
        if (empty($result)) {
            return false;
        } else {
            return $result;
        }
    }

    $params = array($courseid, $modulename, GRADE_TYPE_NONE, $modulename);
    $sql = "SELECT cm.*, m.name, md.name as modname
              FROM {grade_items} gi, {course_modules} cm, {modules} md, {{$modulename}} m
             WHERE gi.courseid = ? AND
                   gi.itemtype = 'mod' AND
                   gi.itemmodule = ? AND
                   gi.itemnumber = 0 AND
                   gi.gradetype != ? AND
                   gi.iteminstance = cm.instance AND
                   cm.instance = m.id AND
                   md.name = ? AND
                   md.id = cm.module";

    return $DB->get_records_sql($sql, $params);
}

