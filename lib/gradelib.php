<?php



defined('MOODLE_INTERNAL') || die();


require_once($CFG->libdir . '/grade/constants.php');

require_once($CFG->libdir . '/grade/grade_category.php');
require_once($CFG->libdir . '/grade/grade_item.php');
require_once($CFG->libdir . '/grade/grade_grade.php');
require_once($CFG->libdir . '/grade/grade_scale.php');
require_once($CFG->libdir . '/grade/grade_outcome.php');



function grade_update($source, $courseid, $itemtype, $itemmodule, $iteminstance, $itemnumber, $grades=NULL, $itemdetails=NULL) {
    global $USER, $CFG, $DB;

        $allowed = array('itemname', 'idnumber', 'gradetype', 'grademax', 'grademin', 'scaleid', 'multfactor', 'plusfactor', 'deleted', 'hidden');
        $floats  = array('grademin', 'grademax', 'multfactor', 'plusfactor');

        $params = compact('courseid', 'itemtype', 'itemmodule', 'iteminstance', 'itemnumber');

    if (is_null($courseid) or is_null($itemtype)) {
        debugging('Missing courseid or itemtype');
        return GRADE_UPDATE_FAILED;
    }

    if (!$grade_items = grade_item::fetch_all($params)) {
                $grade_item = false;

    } else if (count($grade_items) == 1){
        $grade_item = reset($grade_items);
        unset($grade_items); 
    } else {
        debugging('Found more than one grade item');
        return GRADE_UPDATE_MULTIPLE;
    }

    if (!empty($itemdetails['deleted'])) {
        if ($grade_item) {
            if ($grade_item->delete($source)) {
                return GRADE_UPDATE_OK;
            } else {
                return GRADE_UPDATE_FAILED;
            }
        }
        return GRADE_UPDATE_OK;
    }


    if (!$grade_item) {
        if ($itemdetails) {
            $itemdetails = (array)$itemdetails;

                        if (array_key_exists('scaleid', $itemdetails)) {
                if ($itemdetails['scaleid']) {
                    unset($itemdetails['grademin']);
                    unset($itemdetails['grademax']);
                }
            }

            foreach ($itemdetails as $k=>$v) {
                if (!in_array($k, $allowed)) {
                                        continue;
                }
                if ($k == 'gradetype' and $v == GRADE_TYPE_NONE) {
                                        return GRADE_UPDATE_OK;
                }
                $params[$k] = $v;
            }
        }
        $grade_item = new grade_item($params);
        $grade_item->insert();

    } else {
        if ($grade_item->is_locked()) {
                        return GRADE_UPDATE_ITEM_LOCKED;
        }

        if ($itemdetails) {
            $itemdetails = (array)$itemdetails;
            $update = false;
            foreach ($itemdetails as $k=>$v) {
                if (!in_array($k, $allowed)) {
                                        continue;
                }
                if (in_array($k, $floats)) {
                    if (grade_floats_different($grade_item->{$k}, $v)) {
                        $grade_item->{$k} = $v;
                        $update = true;
                    }

                } else {
                    if ($grade_item->{$k} != $v) {
                        $grade_item->{$k} = $v;
                        $update = true;
                    }
                }
            }
            if ($update) {
                $grade_item->update();
            }
        }
    }

    if (!empty($itemdetails['reset'])) {
        $grade_item->delete_all_grades('reset');
        return GRADE_UPDATE_OK;
    }

        if ($grade_item->gradetype == GRADE_TYPE_NONE) {
        return GRADE_UPDATE_OK;
    }

        if (empty($grades)) {
        return GRADE_UPDATE_OK;
    }

    if (is_object($grades)) {
        $grades = array($grades->userid=>$grades);
    } else {
        if (array_key_exists('userid', $grades)) {
            $grades = array($grades['userid']=>$grades);
        }
    }

    foreach($grades as $k=>$g) {
        if (!is_array($g)) {
            $g = (array)$g;
            $grades[$k] = $g;
        }

        if (empty($g['userid']) or $k != $g['userid']) {
            debugging('Incorrect grade array index, must be user id! Grade ignored.');
            unset($grades[$k]);
        }
    }

    if (empty($grades)) {
        return GRADE_UPDATE_FAILED;
    }

    $count = count($grades);
    if ($count > 0 and $count < 200) {
        list($uids, $params) = $DB->get_in_or_equal(array_keys($grades), SQL_PARAMS_NAMED, $start='uid');
        $params['gid'] = $grade_item->id;
        $sql = "SELECT * FROM {grade_grades} WHERE itemid = :gid AND userid $uids";

    } else {
        $sql = "SELECT * FROM {grade_grades} WHERE itemid = :gid";
        $params = array('gid'=>$grade_item->id);
    }

    $rs = $DB->get_recordset_sql($sql, $params);

    $failed = false;

    while (count($grades) > 0) {
        $grade_grade = null;
        $grade       = null;

        foreach ($rs as $gd) {

            $userid = $gd->userid;
            if (!isset($grades[$userid])) {
                                continue;
            }
                        $grade       = $grades[$userid];
            $grade_grade = new grade_grade($gd, false);
            unset($grades[$userid]);
            break;
        }

        if (is_null($grade_grade)) {
            if (count($grades) == 0) {
                                break;
            }

            $grade       = reset($grades);
            $userid      = $grade['userid'];
            $grade_grade = new grade_grade(array('itemid'=>$grade_item->id, 'userid'=>$userid), false);
            $grade_grade->load_optional_fields();             unset($grades[$userid]);
        }

        $rawgrade       = false;
        $feedback       = false;
        $feedbackformat = FORMAT_MOODLE;
        $usermodified   = $USER->id;
        $datesubmitted  = null;
        $dategraded     = null;

        if (array_key_exists('rawgrade', $grade)) {
            $rawgrade = $grade['rawgrade'];
        }

        if (array_key_exists('feedback', $grade)) {
            $feedback = $grade['feedback'];
        }

        if (array_key_exists('feedbackformat', $grade)) {
            $feedbackformat = $grade['feedbackformat'];
        }

        if (array_key_exists('usermodified', $grade)) {
            $usermodified = $grade['usermodified'];
        }

        if (array_key_exists('datesubmitted', $grade)) {
            $datesubmitted = $grade['datesubmitted'];
        }

        if (array_key_exists('dategraded', $grade)) {
            $dategraded = $grade['dategraded'];
        }

                if (!$grade_item->update_raw_grade($userid, $rawgrade, $source, $feedback, $feedbackformat, $usermodified, $dategraded, $datesubmitted, $grade_grade)) {
            $failed = true;
        }
    }

    if ($rs) {
        $rs->close();
    }

    if (!$failed) {
        return GRADE_UPDATE_OK;
    } else {
        return GRADE_UPDATE_FAILED;
    }
}


function grade_update_outcomes($source, $courseid, $itemtype, $itemmodule, $iteminstance, $userid, $data) {
    if ($items = grade_item::fetch_all(array('itemtype'=>$itemtype, 'itemmodule'=>$itemmodule, 'iteminstance'=>$iteminstance, 'courseid'=>$courseid))) {
        $result = true;
        foreach ($items as $item) {
            if (!array_key_exists($item->itemnumber, $data)) {
                continue;
            }
            $grade = $data[$item->itemnumber] < 1 ? null : $data[$item->itemnumber];
            $result = ($item->update_final_grade($userid, $grade, $source) && $result);
        }
        return $result;
    }
    return false; }


function grade_needs_regrade_final_grades($courseid) {
    $course_item = grade_item::fetch_course_item($courseid);
    return $course_item->needsupdate;
}


function grade_needs_regrade_progress_bar($courseid) {
    global $DB;
    $grade_items = grade_item::fetch_all(array('courseid' => $courseid));

    list($sql, $params) = $DB->get_in_or_equal(array_keys($grade_items), SQL_PARAMS_NAMED, 'gi');
    $gradecount = $DB->count_records_select('grade_grades', 'itemid ' . $sql, $params);

            return $gradecount > 100;
}


function grade_regrade_final_grades_if_required($course, callable $callback = null) {
    global $PAGE, $OUTPUT;

    if (!grade_needs_regrade_final_grades($course->id)) {
        return false;
    }

    if (grade_needs_regrade_progress_bar($course->id)) {
        $PAGE->set_heading($course->fullname);
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('recalculatinggrades', 'grades'));
        $progress = new \core\progress\display(true);
        $status = grade_regrade_final_grades($course->id, null, null, $progress);

                if (is_array($status)) {
            foreach ($status as $error) {
                $errortext = new \core\output\notification($error, \core\output\notification::NOTIFY_ERROR);
                echo $OUTPUT->render($errortext);
            }
            $courseitem = grade_item::fetch_course_item($course->id);
            $courseitem->regrading_finished();
        }

        if ($callback) {
                        $url = call_user_func($callback);
        }

        if (empty($url)) {
            $url = $PAGE->url;
        }

        echo $OUTPUT->continue_button($url);
        echo $OUTPUT->footer();
        die();
    } else {
        $result = grade_regrade_final_grades($course->id);
        if ($callback) {
            call_user_func($callback);
        }
        return $result;
    }
}


function grade_get_grades($courseid, $itemtype, $itemmodule, $iteminstance, $userid_or_ids=null) {
    global $CFG;

    $return = new stdClass();
    $return->items    = array();
    $return->outcomes = array();

    $course_item = grade_item::fetch_course_item($courseid);
    $needsupdate = array();
    if ($course_item->needsupdate) {
        $result = grade_regrade_final_grades($courseid);
        if ($result !== true) {
            $needsupdate = array_keys($result);
        }
    }

    if ($grade_items = grade_item::fetch_all(array('itemtype'=>$itemtype, 'itemmodule'=>$itemmodule, 'iteminstance'=>$iteminstance, 'courseid'=>$courseid))) {
        foreach ($grade_items as $grade_item) {
            $decimalpoints = null;

            if (empty($grade_item->outcomeid)) {
                                $item = new stdClass();
                $item->id = $grade_item->id;
                $item->itemnumber = $grade_item->itemnumber;
                $item->itemtype  = $grade_item->itemtype;
                $item->itemmodule = $grade_item->itemmodule;
                $item->iteminstance = $grade_item->iteminstance;
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
                        $grade->datesubmitted  = $grade_grades[$userid]->get_datesubmitted();
                        $grade->dategraded     = $grade_grades[$userid]->get_dategraded();

                                                if ($grade_item->gradetype == GRADE_TYPE_TEXT or $grade_item->gradetype == GRADE_TYPE_NONE) {
                            $grade->grade          = null;
                            $grade->str_grade      = '-';
                            $grade->str_long_grade = $grade->str_grade;

                        } else if (in_array($grade_item->id, $needsupdate)) {
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
                $return->items[$grade_item->itemnumber] = $item;

            } else {
                if (!$grade_outcome = grade_outcome::fetch(array('id'=>$grade_item->outcomeid))) {
                    debugging('Incorect outcomeid found');
                    continue;
                }

                                $outcome = new stdClass();
                $outcome->id = $grade_item->id;
                $outcome->itemnumber = $grade_item->itemnumber;
                $outcome->itemtype   = $grade_item->itemtype;
                $outcome->itemmodule = $grade_item->itemmodule;
                $outcome->iteminstance = $grade_item->iteminstance;
                $outcome->scaleid    = $grade_outcome->scaleid;
                $outcome->name       = $grade_outcome->get_name();
                $outcome->locked     = $grade_item->is_locked();
                $outcome->hidden     = $grade_item->is_hidden();

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
                        $grade->feedback       = $grade_grades[$userid]->feedback;
                        $grade->feedbackformat = $grade_grades[$userid]->feedbackformat;
                        $grade->usermodified   = $grade_grades[$userid]->usermodified;

                                                if (in_array($grade_item->id, $needsupdate)) {
                            $grade->grade     = false;
                            $grade->str_grade = get_string('error');

                        } else if (is_null($grade->grade)) {
                            $grade->grade = 0;
                            $grade->str_grade = get_string('nooutcome', 'grades');

                        } else {
                            $grade->grade = (int)$grade->grade;
                            $scale = $grade_item->load_scale();
                            $grade->str_grade = format_string($scale->scale_items[(int)$grade->grade-1]);
                        }

                                                if (is_null($grade->feedback)) {
                            $grade->str_feedback = '';
                        } else {
                            $grade->str_feedback = format_text($grade->feedback, $grade->feedbackformat);
                        }

                        $outcome->grades[$userid] = $grade;
                    }
                }

                if (isset($return->outcomes[$grade_item->itemnumber])) {
                                        $newnumber = $grade_item->itemnumber + 1;
                    while(grade_item::fetch(array('itemtype'=>$itemtype, 'itemmodule'=>$itemmodule, 'iteminstance'=>$iteminstance, 'courseid'=>$courseid, 'itemnumber'=>$newnumber))) {
                        $newnumber++;
                    }
                    $outcome->itemnumber    = $newnumber;
                    $grade_item->itemnumber = $newnumber;
                    $grade_item->update('system');
                }

                $return->outcomes[$grade_item->itemnumber] = $outcome;

            }
        }
    }

        ksort($return->items, SORT_NUMERIC);
    ksort($return->outcomes, SORT_NUMERIC);

    return $return;
}






function grade_get_setting($courseid, $name, $default=null, $resetcache=false) {
    global $DB;

    static $cache = array();

    if ($resetcache or !array_key_exists($courseid, $cache)) {
        $cache[$courseid] = array();

    } else if (is_null($name)) {
        return null;

    } else if (array_key_exists($name, $cache[$courseid])) {
        return $cache[$courseid][$name];
    }

    if (!$data = $DB->get_record('grade_settings', array('courseid'=>$courseid, 'name'=>$name))) {
        $result = null;
    } else {
        $result = $data->value;
    }

    if (is_null($result)) {
        $result = $default;
    }

    $cache[$courseid][$name] = $result;
    return $result;
}


function grade_get_settings($courseid) {
    global $DB;

     $settings = new stdClass();
     $settings->id = $courseid;

    if ($records = $DB->get_records('grade_settings', array('courseid'=>$courseid))) {
        foreach ($records as $record) {
            $settings->{$record->name} = $record->value;
        }
    }

    return $settings;
}


function grade_set_setting($courseid, $name, $value) {
    global $DB;

    if (is_null($value)) {
        $DB->delete_records('grade_settings', array('courseid'=>$courseid, 'name'=>$name));

    } else if (!$existing = $DB->get_record('grade_settings', array('courseid'=>$courseid, 'name'=>$name))) {
        $data = new stdClass();
        $data->courseid = $courseid;
        $data->name     = $name;
        $data->value    = $value;
        $DB->insert_record('grade_settings', $data);

    } else {
        $data = new stdClass();
        $data->id       = $existing->id;
        $data->value    = $value;
        $DB->update_record('grade_settings', $data);
    }

    grade_get_setting($courseid, null, null, true); }


function grade_format_gradevalue($value, &$grade_item, $localized=true, $displaytype=null, $decimals=null) {
    if ($grade_item->gradetype == GRADE_TYPE_NONE or $grade_item->gradetype == GRADE_TYPE_TEXT) {
        return '';
    }

        if (is_null($value)) {
        return '-';
    }

    if ($grade_item->gradetype != GRADE_TYPE_VALUE and $grade_item->gradetype != GRADE_TYPE_SCALE) {
                return '';
    }

    if (is_null($displaytype)) {
        $displaytype = $grade_item->get_displaytype();
    }

    if (is_null($decimals)) {
        $decimals = $grade_item->get_decimals();
    }

    switch ($displaytype) {
        case GRADE_DISPLAY_TYPE_REAL:
            return grade_format_gradevalue_real($value, $grade_item, $decimals, $localized);

        case GRADE_DISPLAY_TYPE_PERCENTAGE:
            return grade_format_gradevalue_percentage($value, $grade_item, $decimals, $localized);

        case GRADE_DISPLAY_TYPE_LETTER:
            return grade_format_gradevalue_letter($value, $grade_item);

        case GRADE_DISPLAY_TYPE_REAL_PERCENTAGE:
            return grade_format_gradevalue_real($value, $grade_item, $decimals, $localized) . ' (' .
                    grade_format_gradevalue_percentage($value, $grade_item, $decimals, $localized) . ')';

        case GRADE_DISPLAY_TYPE_REAL_LETTER:
            return grade_format_gradevalue_real($value, $grade_item, $decimals, $localized) . ' (' .
                    grade_format_gradevalue_letter($value, $grade_item) . ')';

        case GRADE_DISPLAY_TYPE_PERCENTAGE_REAL:
            return grade_format_gradevalue_percentage($value, $grade_item, $decimals, $localized) . ' (' .
                    grade_format_gradevalue_real($value, $grade_item, $decimals, $localized) . ')';

        case GRADE_DISPLAY_TYPE_LETTER_REAL:
            return grade_format_gradevalue_letter($value, $grade_item) . ' (' .
                    grade_format_gradevalue_real($value, $grade_item, $decimals, $localized) . ')';

        case GRADE_DISPLAY_TYPE_LETTER_PERCENTAGE:
            return grade_format_gradevalue_letter($value, $grade_item) . ' (' .
                    grade_format_gradevalue_percentage($value, $grade_item, $decimals, $localized) . ')';

        case GRADE_DISPLAY_TYPE_PERCENTAGE_LETTER:
            return grade_format_gradevalue_percentage($value, $grade_item, $decimals, $localized) . ' (' .
                    grade_format_gradevalue_letter($value, $grade_item) . ')';
        default:
            return '';
    }
}


function grade_format_gradevalue_real($value, $grade_item, $decimals, $localized) {
    if ($grade_item->gradetype == GRADE_TYPE_SCALE) {
        if (!$scale = $grade_item->load_scale()) {
            return get_string('error');
        }

        $value = $grade_item->bounded_grade($value);
        return format_string($scale->scale_items[$value-1]);

    } else {
        return format_float($value, $decimals, $localized);
    }
}


function grade_format_gradevalue_percentage($value, $grade_item, $decimals, $localized) {
    $min = $grade_item->grademin;
    $max = $grade_item->grademax;
    if ($min == $max) {
        return '';
    }
    $value = $grade_item->bounded_grade($value);
    $percentage = (($value-$min)*100)/($max-$min);
    return format_float($percentage, $decimals, $localized).' %';
}


function grade_format_gradevalue_letter($value, $grade_item) {
    global $CFG;
    $context = context_course::instance($grade_item->courseid, IGNORE_MISSING);
    if (!$letters = grade_get_letters($context)) {
        return '';     }

    if (is_null($value)) {
        return '-';
    }

    $value = grade_grade::standardise_score($value, $grade_item->grademin, $grade_item->grademax, 0, 100);
    $value = bounded_number(0, $value, 100); 
    $gradebookcalculationsfreeze = 'gradebook_calculations_freeze_' . $grade_item->courseid;

    foreach ($letters as $boundary => $letter) {
        if (property_exists($CFG, $gradebookcalculationsfreeze) && (int)$CFG->{$gradebookcalculationsfreeze} <= 20160518) {
                    } else {
                        $boundary = grade_grade::standardise_score($boundary, 0, 100, 0, 100);
        }
        if ($value >= $boundary) {
            return format_string($letter);
        }
    }
    return '-'; }



function grade_get_categories_menu($courseid, $includenew=false) {
    $result = array();
    if (!$categories = grade_category::fetch_all(array('courseid'=>$courseid))) {
                if (!grade_category::fetch_course_category($courseid)) {
            debugging('Can not create course grade category!');
            return $result;
        }
        $categories = grade_category::fetch_all(array('courseid'=>$courseid));
    }
    foreach ($categories as $key=>$category) {
        if ($category->is_course_category()) {
            $result[$category->id] = get_string('uncategorised', 'grades');
            unset($categories[$key]);
        }
    }
    if ($includenew) {
        $result[-1] = get_string('newcategory', 'grades');
    }
    $cats = array();
    foreach ($categories as $category) {
        $cats[$category->id] = $category->get_name();
    }
    core_collator::asort($cats);

    return ($result+$cats);
}


function grade_get_letters($context=null) {
    global $DB;

    if (empty($context)) {
                return array('93'=>'A', '90'=>'A-', '87'=>'B+', '83'=>'B', '80'=>'B-', '77'=>'C+', '73'=>'C', '70'=>'C-', '67'=>'D+', '60'=>'D', '0'=>'F');
    }

    static $cache = array();

    if (array_key_exists($context->id, $cache)) {
        return $cache[$context->id];
    }

    if (count($cache) > 100) {
        $cache = array();     }

    $letters = array();

    $contexts = $context->get_parent_context_ids();
    array_unshift($contexts, $context->id);

    foreach ($contexts as $ctxid) {
        if ($records = $DB->get_records('grade_letters', array('contextid'=>$ctxid), 'lowerboundary DESC')) {
            foreach ($records as $record) {
                $letters[$record->lowerboundary] = $record->letter;
            }
        }

        if (!empty($letters)) {
            $cache[$context->id] = $letters;
            return $letters;
        }
    }

    $letters = grade_get_letters(null);
    $cache[$context->id] = $letters;
    return $letters;
}



function grade_verify_idnumber($idnumber, $courseid, $grade_item=null, $cm=null) {
    global $DB;

    if ($idnumber == '') {
                return true;
    }

        if ($cm and $cm->idnumber == $idnumber) {
        if ($grade_item and $grade_item->itemnumber != 0) {
                                    return false;
        }
        return true;
    } else if ($grade_item and $grade_item->idnumber == $idnumber) {
        return true;
    }

    if ($DB->record_exists('course_modules', array('course'=>$courseid, 'idnumber'=>$idnumber))) {
        return false;
    }

    if ($DB->record_exists('grade_items', array('courseid'=>$courseid, 'idnumber'=>$idnumber))) {
        return false;
    }

    return true;
}


function grade_force_full_regrading($courseid) {
    global $DB;
    $DB->set_field('grade_items', 'needsupdate', 1, array('courseid'=>$courseid));
}


function grade_force_site_regrading() {
    global $CFG, $DB;
    $DB->set_field('grade_items', 'needsupdate', 1);
}


function grade_recover_history_grades($userid, $courseid) {
    global $CFG, $DB;

    if ($CFG->disablegradehistory) {
        debugging('Attempting to recover grades when grade history is disabled.');
        return false;
    }

        $recoveredgrades = false;

                $course_context = context_course::instance($courseid);
    if (!is_enrolled($course_context, $userid)) {
        debugging('Attempting to recover the grades of a user who is deleted or not enrolled. Skipping recover.');
        return false;
    }

                $sql = "SELECT gg.id
              FROM {grade_grades} gg
              JOIN {grade_items} gi ON gi.id = gg.itemid
             WHERE gi.courseid = :courseid AND gg.userid = :userid";
    $params = array('userid' => $userid, 'courseid' => $courseid);
    if ($DB->record_exists_sql($sql, $params)) {
        debugging('Attempting to recover the grades of a user who already has grades. Skipping recover.');
        return false;
    } else {
                        $sql = "SELECT h.id, gi.itemtype, gi.itemmodule, gi.iteminstance as iteminstance, gi.itemnumber, h.source, h.itemid, h.userid, h.rawgrade, h.rawgrademax,
                       h.rawgrademin, h.rawscaleid, h.usermodified, h.finalgrade, h.hidden, h.locked, h.locktime, h.exported, h.overridden, h.excluded, h.feedback,
                       h.feedbackformat, h.information, h.informationformat, h.timemodified, itemcreated.tm AS timecreated
                  FROM {grade_grades_history} h
                  JOIN (SELECT itemid, MAX(id) AS id
                          FROM {grade_grades_history}
                         WHERE userid = :userid1
                      GROUP BY itemid) maxquery ON h.id = maxquery.id AND h.itemid = maxquery.itemid
                  JOIN {grade_items} gi ON gi.id = h.itemid
                  JOIN (SELECT itemid, MAX(timemodified) AS tm
                          FROM {grade_grades_history}
                         WHERE userid = :userid2 AND action = :insertaction
                      GROUP BY itemid) itemcreated ON itemcreated.itemid = h.itemid
                 WHERE gi.courseid = :courseid";
        $params = array('userid1' => $userid, 'userid2' => $userid , 'insertaction' => GRADE_HISTORY_INSERT, 'courseid' => $courseid);
        $oldgrades = $DB->get_records_sql($sql, $params);

                foreach ($oldgrades as $oldgrade) {
            unset($oldgrade->id);

            $grade = new grade_grade($oldgrade, false);            $grade->insert($oldgrade->source);

                        if (!is_null($oldgrade->finalgrade) || !is_null($oldgrade->feedback)) {
                $recoveredgrades = true;
            }
        }
    }

            grade_grab_course_grades($courseid, null, $userid);

    return $recoveredgrades;
}


function grade_regrade_final_grades($courseid, $userid=null, $updated_item=null, $progress=null) {
        \core_php_time_limit::raise();
    raise_memory_limit(MEMORY_EXTRA);

    $course_item = grade_item::fetch_course_item($courseid);

    if ($progress == null) {
        $progress = new \core\progress\none();
    }

    if ($userid) {
                if (empty($updated_item)) {
            print_error("cannotbenull", 'debug', '', "updated_item");
        }
        if ($course_item->needsupdate) {
            $updated_item->force_regrading();
            return array($course_item->id =>'Can not do fast regrading after updating of raw grades');
        }

    } else {
        if (!$course_item->needsupdate) {
                        return true;
        }
    }

                    $cats = grade_category::fetch_all(array('courseid' => $courseid));
    $flatcattree = array();
    foreach ($cats as $cat) {
        if (!isset($flatcattree[$cat->depth])) {
            $flatcattree[$cat->depth] = array();
        }
        $flatcattree[$cat->depth][] = $cat;
    }
    krsort($flatcattree);
    foreach ($flatcattree as $depth => $cats) {
        foreach ($cats as $cat) {
            $cat->pre_regrade_final_grades();
        }
    }

    $progresstotal = 0;
    $progresscurrent = 0;

    $grade_items = grade_item::fetch_all(array('courseid'=>$courseid));
    $depends_on = array();

    foreach ($grade_items as $gid=>$gitem) {
        if ((!empty($updated_item) and $updated_item->id == $gid) ||
                $gitem->is_course_item() || $gitem->is_category_item() || $gitem->is_calculated()) {
            $grade_items[$gid]->needsupdate = 1;
        }

                if ($grade_items[$gid]->needsupdate) {
            $depends_on[$gid] = $grade_items[$gid]->depends_on();
            $progresstotal++;
        }
    }

    $progress->start_progress('regrade_course', $progresstotal);

    $errors = array();
    $finalids = array();
    $updatedids = array();
    $gids     = array_keys($grade_items);
    $failed = 0;

    while (count($finalids) < count($gids)) {         $count = 0;
        foreach ($gids as $gid) {
            if (in_array($gid, $finalids)) {
                continue;             }

            if (!$grade_items[$gid]->needsupdate) {
                $finalids[] = $gid;                 continue;
            }
            $thisprogress = $progresstotal;
            foreach ($grade_items as $item) {
                if ($item->needsupdate) {
                    $thisprogress--;
                }
            }
                        $thisprogress = max(min($thisprogress, $progresstotal), $progresscurrent);
            $progress->progress($thisprogress);
            $progresscurrent = $thisprogress;

            foreach ($depends_on[$gid] as $did) {
                if (!in_array($did, $finalids)) {
                                        continue 2;
                }
            }

            
                                    
                        
                        if (!empty($updated_item) && $gid != $updated_item->id && !in_array($updated_item->id, $depends_on[$gid])) {

                                                                
                $updateddependencies = false;
                foreach ($depends_on[$gid] as $dependency) {
                    if (in_array($dependency, $updatedids)) {
                        $updateddependencies = true;
                        break;
                    }
                }
                if ($updateddependencies === false) {
                                        
                    $finalids[] = $gid;
                    continue;
                }
            }

                        $result = $grade_items[$gid]->regrade_final_grades($userid);

            if ($result === true) {

                                if (empty($userid)) {
                    $grade_items[$gid]->regrading_finished();
                                        $grade_items[$gid]->check_locktime();
                } else {
                    $grade_items[$gid]->needsupdate = 0;
                }
                $count++;
                $finalids[] = $gid;
                $updatedids[] = $gid;

            } else {
                $grade_items[$gid]->force_regrading();
                $errors[$gid] = $result;
            }
        }

        if ($count == 0) {
            $failed++;
        } else {
            $failed = 0;
        }

        if ($failed > 1) {
            foreach($gids as $gid) {
                if (in_array($gid, $finalids)) {
                    continue;                 }
                $grade_items[$gid]->force_regrading();
                $errors[$grade_items[$gid]->id] = get_string('errorcalculationbroken', 'grades');
            }
            break;         }
    }
    $progress->end_progress();

    if (count($errors) == 0) {
        if (empty($userid)) {
                        grade_grade::check_locktime_all($gids);
        }
        return true;
    } else {
        return $errors;
    }
}


function grade_grab_course_grades($courseid, $modname=null, $userid=0) {
    global $CFG, $DB;

    if ($modname) {
        $sql = "SELECT a.*, cm.idnumber as cmidnumber, m.name as modname
                  FROM {".$modname."} a, {course_modules} cm, {modules} m
                 WHERE m.name=:modname AND m.visible=1 AND m.id=cm.module AND cm.instance=a.id AND cm.course=:courseid";
        $params = array('modname'=>$modname, 'courseid'=>$courseid);

        if ($modinstances = $DB->get_records_sql($sql, $params)) {
            foreach ($modinstances as $modinstance) {
                grade_update_mod_grades($modinstance, $userid);
            }
        }
        return;
    }

    if (!$mods = core_component::get_plugin_list('mod') ) {
        print_error('nomodules', 'debug');
    }

    foreach ($mods as $mod => $fullmod) {
        if ($mod == 'NEWMODULE') {               continue;
        }

                if (file_exists($fullmod.'/lib.php')) {
                        $sql = "SELECT a.*, cm.idnumber as cmidnumber, m.name as modname
                      FROM {".$mod."} a, {course_modules} cm, {modules} m
                     WHERE m.name=:mod AND m.visible=1 AND m.id=cm.module AND cm.instance=a.id AND cm.course=:courseid";
            $params = array('mod'=>$mod, 'courseid'=>$courseid);

            if ($modinstances = $DB->get_records_sql($sql, $params)) {
                foreach ($modinstances as $modinstance) {
                    grade_update_mod_grades($modinstance, $userid);
                }
            }
        }
    }
}


function grade_update_mod_grades($modinstance, $userid=0) {
    global $CFG, $DB;

    $fullmod = $CFG->dirroot.'/mod/'.$modinstance->modname;
    if (!file_exists($fullmod.'/lib.php')) {
        debugging('missing lib.php file in module ' . $modinstance->modname);
        return false;
    }
    include_once($fullmod.'/lib.php');

    $updateitemfunc   = $modinstance->modname.'_grade_item_update';
    $updategradesfunc = $modinstance->modname.'_update_grades';

    if (function_exists($updategradesfunc) and function_exists($updateitemfunc)) {
                $updateitemfunc($modinstance);
        $updategradesfunc($modinstance, $userid);

    } else {
            }

    return true;
}


function remove_grade_letters($context, $showfeedback) {
    global $DB, $OUTPUT;

    $strdeleted = get_string('deleted');

    $DB->delete_records('grade_letters', array('contextid'=>$context->id));
    if ($showfeedback) {
        echo $OUTPUT->notification($strdeleted.' - '.get_string('letters', 'grades'), 'notifysuccess');
    }
}


function remove_course_grades($courseid, $showfeedback) {
    global $DB, $OUTPUT;

    $fs = get_file_storage();
    $strdeleted = get_string('deleted');

    $course_category = grade_category::fetch_course_category($courseid);
    $course_category->delete('coursedelete');
    $fs->delete_area_files(context_course::instance($courseid)->id, 'grade', 'feedback');
    if ($showfeedback) {
        echo $OUTPUT->notification($strdeleted.' - '.get_string('grades', 'grades').', '.get_string('items', 'grades').', '.get_string('categories', 'grades'), 'notifysuccess');
    }

    if ($outcomes = grade_outcome::fetch_all(array('courseid'=>$courseid))) {
        foreach ($outcomes as $outcome) {
            $outcome->delete('coursedelete');
        }
    }
    $DB->delete_records('grade_outcomes_courses', array('courseid'=>$courseid));
    if ($showfeedback) {
        echo $OUTPUT->notification($strdeleted.' - '.get_string('outcomes', 'grades'), 'notifysuccess');
    }

    if ($scales = grade_scale::fetch_all(array('courseid'=>$courseid))) {
        foreach ($scales as $scale) {
            $scale->delete('coursedelete');
        }
    }
    if ($showfeedback) {
        echo $OUTPUT->notification($strdeleted.' - '.get_string('scales'), 'notifysuccess');
    }

    $DB->delete_records('grade_settings', array('courseid'=>$courseid));
    if ($showfeedback) {
        echo $OUTPUT->notification($strdeleted.' - '.get_string('settings', 'grades'), 'notifysuccess');
    }
}


function grade_course_category_delete($categoryid, $newparentid, $showfeedback) {
    global $DB;

    $context = context_coursecat::instance($categoryid);
    $DB->delete_records('grade_letters', array('contextid'=>$context->id));
}


function grade_uninstalled_module($modname) {
    global $CFG, $DB;

    $sql = "SELECT *
              FROM {grade_items}
             WHERE itemtype='mod' AND itemmodule=?";

        $rs = $DB->get_recordset_sql($sql, array($modname));
    foreach ($rs as $item) {
        $grade_item = new grade_item($item, false);
        $grade_item->delete('moduninstall');
    }
    $rs->close();
}


function grade_user_delete($userid) {
    if ($grades = grade_grade::fetch_all(array('userid'=>$userid))) {
        foreach ($grades as $grade) {
            $grade->delete('userdelete');
        }
    }
}


function grade_user_unenrol($courseid, $userid) {
    if ($items = grade_item::fetch_all(array('courseid'=>$courseid))) {
        foreach ($items as $item) {
            if ($grades = grade_grade::fetch_all(array('userid'=>$userid, 'itemid'=>$item->id))) {
                foreach ($grades as $grade) {
                    $grade->delete('userdelete');
                }
            }
        }
    }
}


function grade_cron() {
    global $CFG, $DB;

    $now = time();

    $sql = "SELECT i.*
              FROM {grade_items} i
             WHERE i.locked = 0 AND i.locktime > 0 AND i.locktime < ? AND EXISTS (
                SELECT 'x' FROM {grade_items} c WHERE c.itemtype='course' AND c.needsupdate=0 AND c.courseid=i.courseid)";

        $rs = $DB->get_recordset_sql($sql, array($now));
    foreach ($rs as $item) {
        $grade_item = new grade_item($item, false);
        $grade_item->locked = $now;
        $grade_item->update('locktime');
    }
    $rs->close();

    $grade_inst = new grade_grade();
    $fields = 'g.'.implode(',g.', $grade_inst->required_fields);

    $sql = "SELECT $fields
              FROM {grade_grades} g, {grade_items} i
             WHERE g.locked = 0 AND g.locktime > 0 AND g.locktime < ? AND g.itemid=i.id AND EXISTS (
                SELECT 'x' FROM {grade_items} c WHERE c.itemtype='course' AND c.needsupdate=0 AND c.courseid=i.courseid)";

        $rs = $DB->get_recordset_sql($sql, array($now));
    foreach ($rs as $grade) {
        $grade_grade = new grade_grade($grade, false);
        $grade_grade->locked = $now;
        $grade_grade->update('locktime');
    }
    $rs->close();

            if (!empty($CFG->gradehistorylifetime)) {          $histlifetime = $now - ($CFG->gradehistorylifetime * 3600 * 24);
        $tables = array('grade_outcomes_history', 'grade_categories_history', 'grade_items_history', 'grade_grades_history', 'scale_history');
        foreach ($tables as $table) {
            if ($DB->delete_records_select($table, "timemodified < ?", array($histlifetime))) {
                mtrace("    Deleted old grade history records from '$table'");
            }
        }
    }
}


function grade_course_reset($courseid) {

        grade_force_full_regrading($courseid);

    $grade_items = grade_item::fetch_all(array('courseid'=>$courseid));
    foreach ($grade_items as $gid=>$grade_item) {
        $grade_item->delete_all_grades('reset');
    }

        grade_grab_course_grades($courseid);

        grade_regrade_final_grades($courseid);
    return true;
}


function grade_floatval($number) {
    if (is_null($number) or $number === '') {
        return null;
    }
            return round($number, 5);
}


function grade_floats_different($f1, $f2) {
        return (grade_floatval($f1) !== grade_floatval($f2));
}


function grade_floats_equal($f1, $f2) {
    return (grade_floatval($f1) === grade_floatval($f2));
}
