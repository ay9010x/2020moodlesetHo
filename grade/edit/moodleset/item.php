<?php




require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/lib.php';
require_once 'item_form.php';

$courseid = required_param('courseid', PARAM_INT);
$id       = optional_param('id', 0, PARAM_INT);

$url = new moodle_url('/grade/edit/moodleset/item.php', array('courseid'=>$courseid));
if ($id !== 0) {
    $url->param('id', $id);
}
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
navigation_node::override_active_url(new moodle_url('/grade/edit/moodleset/index.php',
    array('id'=>$courseid)));

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}

require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/grade:manage', $context);

$gpr = new grade_plugin_return();
$returnurl = $gpr->get_return_url('index.php?id='.$course->id);

$heading = get_string('itemsedit', 'grades');

if ($grade_item = grade_item::fetch(array('id'=>$id, 'courseid'=>$courseid))) {
        if (!empty($grade_item->outcomeid) && !empty($CFG->enableoutcomes)) {
        $url = $CFG->wwwroot.'/grade/edit/moodleset/outcomeitem.php?id='.$id.'&amp;courseid='.$courseid;
        redirect($gpr->add_url_params($url));
    }
    if ($grade_item->is_course_item() or $grade_item->is_category_item()) {
        $grade_category = $grade_item->get_item_category();
        $url = $CFG->wwwroot.'/grade/edit/moodleset/category.php?id='.$grade_category->id.'&amp;courseid='.$courseid;
        redirect($gpr->add_url_params($url));
    }

    $item = $grade_item->get_record_data();
    $parent_category = $grade_item->get_parent_category();
    $item->parentcategory = $parent_category->id;

} else {
    $heading = get_string('newitem', 'grades');
    $grade_item = new grade_item(array('courseid'=>$courseid, 'itemtype'=>'manual'), false);
    $item = $grade_item->get_record_data();
    $parent_category = grade_category::fetch_course_category($courseid);
    $item->parentcategory = $parent_category->id;
}
$decimalpoints = $grade_item->get_decimals();

if ($item->hidden > 1) {
    $item->hiddenuntil = $item->hidden;
    $item->hidden = 0;
} else {
    $item->hiddenuntil = 0;
}

$item->locked = !empty($item->locked);

$item->grademax        = format_float($item->grademax, $decimalpoints);
$item->grademin        = format_float($item->grademin, $decimalpoints);
$item->gradepass       = format_float($item->gradepass, $decimalpoints);
$item->multfactor      = format_float($item->multfactor, 4);
$item->plusfactor      = format_float($item->plusfactor, 4);

if ($parent_category->aggregation == GRADE_AGGREGATE_SUM or $parent_category->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN2) {
    $item->aggregationcoef = $item->aggregationcoef == 0 ? 0 : 1;
} else {
    $item->aggregationcoef = format_float($item->aggregationcoef, 4);
}
if ($parent_category->aggregation == GRADE_AGGREGATE_SUM) {
    $item->aggregationcoef2 = format_float($item->aggregationcoef2 * 100.0);
}
$item->cancontrolvisibility = $grade_item->can_control_visibility();

$mform = new edit_item_form(null, array('current'=>$item, 'gpr'=>$gpr));

if ($mform->is_cancelled()) {
    redirect($returnurl);

} else if ($data = $mform->get_data(false)) {

        if (empty($grade_item->id) && isset($data->parentcategory) && $parent_category->id != $data->parentcategory) {
        $parent_category = grade_category::fetch(array('id' => $data->parentcategory));
    }

        $defaults = grade_category::get_default_aggregation_coefficient_values($parent_category->aggregation);
    if (!isset($data->aggregationcoef) || $data->aggregationcoef == '') {
        $data->aggregationcoef = $defaults['aggregationcoef'];
    }
    if (!isset($data->weightoverride)) {
        $data->weightoverride = $defaults['weightoverride'];
    }

    if (!isset($data->gradepass) || $data->gradepass == '') {
        $data->gradepass = 0;
    }

    if (!isset($data->grademin) || $data->grademin == '') {
        $data->grademin = 0;
    }

    $hidden      = empty($data->hidden) ? 0: $data->hidden;
    $hiddenuntil = empty($data->hiddenuntil) ? 0: $data->hiddenuntil;
    unset($data->hidden);
    unset($data->hiddenuntil);

    $locked   = empty($data->locked) ? 0: $data->locked;
    $locktime = empty($data->locktime) ? 0: $data->locktime;
    unset($data->locked);
    unset($data->locktime);

    $convert = array('grademax', 'grademin', 'gradepass', 'multfactor', 'plusfactor', 'aggregationcoef', 'aggregationcoef2');
    foreach ($convert as $param) {
        if (property_exists($data, $param)) {
            $data->$param = unformat_float($data->$param);
        }
    }
    if (isset($data->aggregationcoef2) && $parent_category->aggregation == GRADE_AGGREGATE_SUM) {
        $data->aggregationcoef2 = $data->aggregationcoef2 / 100.0;
    } else {
        $data->aggregationcoef2 = $defaults['aggregationcoef2'];
    }

    $grade_item = new grade_item(array('id'=>$id, 'courseid'=>$courseid));
    $oldmin = $grade_item->grademin;
    $oldmax = $grade_item->grademax;
    grade_item::set_properties($grade_item, $data);
    $grade_item->outcomeid = null;

        if (!property_exists($data, 'decimals') or $data->decimals < 0) {
        $grade_item->decimals = null;
    }

    if (empty($grade_item->id)) {
        $grade_item->itemtype = 'manual';         $grade_item->insert();

                if (isset($data->parentcategory)) {
            $grade_item->set_parent($data->parentcategory, false);
        }

    } else {
        $grade_item->update();

        if (!empty($data->rescalegrades) && $data->rescalegrades == 'yes') {
            $newmin = $grade_item->grademin;
            $newmax = $grade_item->grademax;
            $grade_item->rescale_grades_keep_percentage($oldmin, $oldmax, $newmin, $newmax, 'gradebook');
        }
    }

        if ($hiddenuntil) {
        $grade_item->set_hidden($hiddenuntil, false);
    } else {
        $grade_item->set_hidden($hidden, false);
    }

    $grade_item->set_locktime($locktime);     $grade_item->set_locked($locked, false, true);

    redirect($returnurl);
}

$PAGE->navbar->add($heading);
print_grade_page_head($courseid, 'settings', null, $heading, false, false, false);

$mform->display();

echo $OUTPUT->footer();
