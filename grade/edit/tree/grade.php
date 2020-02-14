<?php



require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/lib.php';
require_once 'grade_form.php';

$courseid = required_param('courseid', PARAM_INT);
$id       = optional_param('id', 0, PARAM_INT);
$itemid   = optional_param('itemid', 0, PARAM_INT);
$userid   = optional_param('userid', 0, PARAM_INT);

$url = new moodle_url('/grade/edit/tree/grade.php', array('courseid'=>$courseid));
if ($id !== 0) {
    $url->param('id', $id);
}
if ($itemid !== 0) {
    $url->param('itemid', $itemid);
}
if ($userid !== 0) {
    $url->param('userid', $userid);
}
$PAGE->set_url($url);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}

$PAGE->set_pagelayout('incourse');
require_login($course);
$context = context_course::instance($course->id);
if (!has_capability('moodle/grade:manage', $context)) {
    require_capability('moodle/grade:edit', $context);
}

$gpr = new grade_plugin_return();
$returnurl = $gpr->get_return_url($CFG->wwwroot.'/grade/report/index.php?id='.$course->id);

if (!empty($id)) {
    if (!$grade = $DB->get_record('grade_grades', array('id' => $id))) {
        print_error('invalidgroupid');
    }

    if (!empty($itemid) and $itemid != $grade->itemid) {
        print_error('invaliditemid');
    }
    $itemid = $grade->itemid;

    if (!empty($userid) and $userid != $grade->userid) {
        print_error('invaliduser');
    }
    $userid = $grade->userid;

    unset($grade);

} else if (empty($userid) or empty($itemid)) {
    print_error('missinguseranditemid');
}

if (!$grade_item = grade_item::fetch(array('id'=>$itemid, 'courseid'=>$courseid))) {
    print_error('cannotfindgradeitem');
}

if (groups_get_course_groupmode($COURSE) == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
    if ($groups = groups_get_all_groups($COURSE->id, $userid)) {
        $ok = false;
        foreach ($groups as $group) {
            if (groups_is_member($group->id, $USER->id)) {
                $ok = true;
            }
        }
        if (!$ok) {
            print_error('cannotgradeuser');
        }
    } else {
        print_error('cannotgradeuser');
    }
}

$mform = new edit_grade_form(null, array('grade_item'=>$grade_item, 'gpr'=>$gpr));

if ($grade = $DB->get_record('grade_grades', array('itemid' => $grade_item->id, 'userid' => $userid))) {

        if (empty($grade->feedback)) {
        $grade->feedback  = '';
    } else {
        $options = new stdClass();
        $options->smiley  = false;
        $options->filter  = false;
        $options->noclean = false;
        $options->para    = false;
        $grade->feedback  = format_text($grade->feedback, $grade->feedbackformat, $options);
    }
    $grade->feedbackformat = FORMAT_HTML;

    $grade->locked      = $grade->locked     > 0 ? 1:0;
    $grade->overridden  = $grade->overridden > 0 ? 1:0;
    $grade->excluded    = $grade->excluded   > 0 ? 1:0;

    if ($grade->hidden > 1) {
        $grade->hiddenuntil = $grade->hidden;
        $grade->hidden = 1;
    } else {
        $grade->hiddenuntil = 0;
    }

    if ($grade_item->is_hidden()) {
        $grade->hidden = 1;
    }

    if ($grade_item->is_locked()) {
        $grade->locked = 1;
    }

        if ($grade_item->gradetype == GRADE_TYPE_SCALE) {
        if (empty($grade->finalgrade)) {
            $grade->finalgrade = -1;
        } else {
            $grade->finalgrade = (int)$grade->finalgrade;
        }
    } else if ($grade_item->gradetype == GRADE_TYPE_VALUE) {
        $grade->finalgrade = format_float($grade->finalgrade, $grade_item->get_decimals());
    }

    $grade->oldgrade    = $grade->finalgrade;
    $grade->oldfeedback = $grade->feedback;

    $grade->feedback = array('text'=>$grade->feedback, 'format'=>$grade->feedbackformat);

    $mform->set_data($grade);
} else {
    $grade = new stdClass();
    $grade->feedback = array('text'=>'', 'format'=>FORMAT_HTML);
    $mform->set_data(array('itemid'=>$itemid, 'userid'=>$userid, 'locked'=>$grade_item->locked, 'locktime'=>$grade_item->locktime));
}

if ($mform->is_cancelled()) {
    redirect($returnurl);

} else if ($data = $mform->get_data(false)) {

    if (isset($data->feedback) && is_array($data->feedback)) {
        $data->feedbackformat = $data->feedback['format'];
        $data->feedback = $data->feedback['text'];
    }

    $old_grade_grade = new grade_grade(array('userid'=>$data->userid, 'itemid'=>$grade_item->id), true); 
        if (!isset($data->finalgrade) or $data->finalgrade == $data->oldgrade) {
        $data->finalgrade = $old_grade_grade->finalgrade;

    } else if ($grade_item->gradetype == GRADE_TYPE_SCALE) {
        if ($data->finalgrade < 1) {
            $data->finalgrade = NULL;
        }

    } else if ($grade_item->gradetype == GRADE_TYPE_VALUE) {
        $data->finalgrade = unformat_float($data->finalgrade);

    } else {
                $data->finalgrade = $old_grade_grade->finalgrade;
    }

        if (!property_exists($data, 'feedback') or $data->feedback == $data->oldfeedback) {
        $data->feedback       = $old_grade_grade->feedback;
        $data->feedbackformat = $old_grade_grade->feedbackformat;
    }

            $grade_item->update_final_grade($data->userid, $data->finalgrade, 'editgrade', $data->feedback, $data->feedbackformat);

    $grade_grade = new grade_grade(array('userid'=>$data->userid, 'itemid'=>$grade_item->id), true);
    $grade_grade->grade_item =& $grade_item; 
    if (has_capability('moodle/grade:manage', $context) or has_capability('moodle/grade:edit', $context)) {
                if (!isset($data->overridden)) {
            $data->overridden = 0;         }
        $grade_grade->set_overridden($data->overridden);
    }

    if (has_capability('moodle/grade:manage', $context) or has_capability('moodle/grade:hide', $context)) {
        $hidden      = empty($data->hidden) ? 0: $data->hidden;
        $hiddenuntil = empty($data->hiddenuntil) ? 0: $data->hiddenuntil;

        if ($grade_item->is_hidden()) {
            if ($old_grade_grade->hidden == 1 and $hiddenuntil == 0) {
                            } else {
                $grade_grade->set_hidden($hiddenuntil);
            }
        } else {
            if ($hiddenuntil) {
                $grade_grade->set_hidden($hiddenuntil);
            } else {
                $grade_grade->set_hidden($hidden);             }
        }
    }

    if (isset($data->locked) and !$grade_item->is_locked()) {
        if (($old_grade_grade->locked or $old_grade_grade->locktime)
          and (!has_capability('moodle/grade:manage', $context) and !has_capability('moodle/grade:unlock', $context))) {
            
        } else if ((!$old_grade_grade->locked and !$old_grade_grade->locktime)
          and (!has_capability('moodle/grade:manage', $context) and !has_capability('moodle/grade:lock', $context))) {
            
        } else {
            $grade_grade->set_locktime($data->locktime);             $grade_grade->set_locked($data->locked, false, true);
                        $grade_grade = new grade_grade(array('userid'=>$data->userid, 'itemid'=>$grade_item->id), true);
            $grade_grade->grade_item =& $grade_item;         }
    }

    if (isset($data->excluded) and has_capability('moodle/grade:manage', $context)) {
        $grade_grade->set_excluded($data->excluded);
    }

        if ($old_grade_grade->excluded != $grade_grade->excluded) {
        $parent = $grade_item->get_parent_category();
        $parent->force_regrading();

    } else if ($old_grade_grade->overridden != $grade_grade->overridden and empty($grade_grade->overridden)) {         $grade_item->force_regrading();

    } else if ($old_grade_grade->locktime != $grade_grade->locktime) {
        $grade_item->force_regrading();
    }

    redirect($returnurl);
}

$strgrades       = get_string('grades');
$strgraderreport = get_string('graderreport', 'grades');
$strgradeedit    = get_string('editgrade', 'grades');
$struser         = get_string('user');

grade_build_nav(__FILE__, $strgradeedit, array('courseid' => $courseid));


$PAGE->set_title($strgrades . ': ' . $strgraderreport . ': ' . $strgradeedit);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($strgradeedit);

echo $OUTPUT->box_start();

$mform->display();

echo $OUTPUT->box_end();

echo $OUTPUT->footer();
die;
