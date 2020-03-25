<?php



require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->libdir.'/gradelib.php';

$courseid = required_param('id', PARAM_INT);

$PAGE->set_url('/grade/edit/outcome/course.php', array('id'=>$courseid));

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/course:update', $context);

$gpr = new grade_plugin_return(array('type'=>'edit', 'plugin'=>'outcomes', 'courseid'=>$courseid));

$standardoutcomes    = grade_outcome::fetch_all_global();
$co_custom           = grade_outcome::fetch_all_local($courseid);
$co_standard_used    = array();
$co_standard_notused = array();

if ($courseused = $DB->get_records('grade_outcomes_courses', array('courseid' => $courseid), '', 'outcomeid')) {
    $courseused = array_keys($courseused);
} else {
    $courseused = array();
}

foreach ($courseused as $oid) {
    if (!array_key_exists($oid, $standardoutcomes) and !array_key_exists($oid, $co_custom)) {
        $DB->delete_records('grade_outcomes_courses', array('outcomeid' => $oid, 'courseid' => $courseid));
    }
}

foreach($co_custom as $oid=>$outcome) {
    if (!in_array($oid, $courseused)) {
        $courseused[$oid] = $oid;
        $goc = new stdClass();
        $goc->courseid = $courseid;
        $goc->outcomeid = $oid;
        $DB->insert_record('grade_outcomes_courses', $goc);
    }
}

$params = array($courseid);
$sql = "SELECT DISTINCT outcomeid
          FROM {grade_items}
         WHERE courseid=? and outcomeid IS NOT NULL";
if ($realused = $DB->get_records_sql($sql, $params)) {
    $realused = array_keys($realused);
    foreach ($realused as $oid) {
        if (array_key_exists($oid, $standardoutcomes)) {

            $co_standard_used[$oid] = $standardoutcomes[$oid];
            unset($standardoutcomes[$oid]);

            if (!in_array($oid, $courseused)) {
                $courseused[$oid] = $oid;
                $goc = new stdClass();
                $goc->courseid = $courseid;
                $goc->outcomeid = $oid;
                $DB->insert_record('grade_outcomes_courses', $goc);
            }
        }
    }
}

foreach ($standardoutcomes as $oid=>$outcome) {
    if (in_array($oid, $courseused)) {
        $co_standard_notused[$oid] = $standardoutcomes[$oid];
        unset($standardoutcomes[$oid]);
    }
}


if ($data = data_submitted() and confirm_sesskey()) {
    require_capability('moodle/grade:manageoutcomes', $context);
    if (!empty($data->add) && !empty($data->addoutcomes)) {
            foreach ($data->addoutcomes as $add) {
            $add = clean_param($add, PARAM_INT);
            if (!array_key_exists($add, $standardoutcomes)) {
                continue;
            }
            $goc = new stdClass();
            $goc->courseid = $courseid;
            $goc->outcomeid = $add;
            $DB->insert_record('grade_outcomes_courses', $goc);
        }

    } else if (!empty($data->remove) && !empty($data->removeoutcomes)) {
            foreach ($data->removeoutcomes as $remove) {
            $remove = clean_param($remove, PARAM_INT);
            if (!array_key_exists($remove, $co_standard_notused)) {
                continue;
            }
            $DB->delete_records('grade_outcomes_courses', array('courseid' => $courseid, 'outcomeid' => $remove));
        }
    }
    redirect('course.php?id='.$courseid); }

print_grade_page_head($COURSE->id, 'outcome', 'course');

require('course_form.html');

echo $OUTPUT->footer();

