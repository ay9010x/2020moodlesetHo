<?php



require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/lib.php';
require_once 'edit_form.php';

$courseid = optional_param('courseid', 0, PARAM_INT);
$id       = optional_param('id', 0, PARAM_INT);

$url = new moodle_url('/grade/edit/outcome/edit.php');
if ($courseid !== 0) {
    $url->param('courseid', $courseid);
}
if ($id !== 0) {
    $url->param('id', $id);
}
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

$systemcontext = context_system::instance();
$heading = null;

if ($id) {
    $heading = get_string('editoutcome', 'grades');

        if (!$outcome_rec = $DB->get_record('grade_outcomes', array('id' => $id))) {
        print_error('invalidoutcome');
    }
    if ($outcome_rec->courseid) {
        $outcome_rec->standard = 0;
        if (!$course = $DB->get_record('course', array('id' => $outcome_rec->courseid))) {
            print_error('invalidcourseid');
        }
        require_login($course);
        $context = context_course::instance($course->id);
        require_capability('moodle/grade:manage', $context);
        $courseid = $course->id;
    } else {
        if ($courseid) {
            if (!$course = $DB->get_record('course', array('id' => $courseid))) {
                print_error('invalidcourseid');
            }
        }
        $outcome_rec->standard = 1;
        $outcome_rec->courseid = $courseid;
        require_login();
        require_capability('moodle/grade:manage', $systemcontext);
        $PAGE->set_context($systemcontext);
    }

} else if ($courseid){
    $heading = get_string('addoutcome', 'grades');
        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    require_login($course);
    $context = context_course::instance($course->id);
    require_capability('moodle/grade:manage', $context);
    navigation_node::override_active_url(new moodle_url('/grade/edit/outcome/course.php', array('id'=>$courseid)));

    $outcome_rec = new stdClass();
    $outcome_rec->standard = 0;
    $outcome_rec->courseid = $courseid;
} else {
    require_login();
    require_capability('moodle/grade:manage', $systemcontext);
    $PAGE->set_context($systemcontext);

        $outcome_rec = new stdClass();
    $outcome_rec->standard = 1;
    $outcome_rec->courseid = 0;
}

if (!$courseid) {
    require_once $CFG->libdir.'/adminlib.php';
    admin_externalpage_setup('outcomes');
}

$gpr = new grade_plugin_return();
$returnurl = $gpr->get_return_url('index.php?id='.$courseid);
$editoroptions = array(
    'maxfiles'  => EDITOR_UNLIMITED_FILES,
    'maxbytes'  => $CFG->maxbytes,
    'trusttext' => false,
    'noclean'   => true,
    'context'   => $systemcontext
);

if (!empty($outcome_rec->id)) {
    $editoroptions['subdirs'] = file_area_contains_subdirs($systemcontext, 'grade', 'outcome', $outcome_rec->id);
    $outcome_rec = file_prepare_standard_editor($outcome_rec, 'description', $editoroptions, $systemcontext, 'grade', 'outcome', $outcome_rec->id);
} else {
    $editoroptions['subdirs'] = false;
    $outcome_rec = file_prepare_standard_editor($outcome_rec, 'description', $editoroptions, $systemcontext, 'grade', 'outcome', null);
}

$mform = new edit_outcome_form(null, compact('gpr', 'editoroptions'));

$mform->set_data($outcome_rec);

if ($mform->is_cancelled()) {
    redirect($returnurl);

} else if ($data = $mform->get_data()) {
    $outcome = new grade_outcome(array('id'=>$id));
    $data->usermodified = $USER->id;

    if (empty($outcome->id)) {
        $data->description = $data->description_editor['text'];
        grade_outcome::set_properties($outcome, $data);
        if (!has_capability('moodle/grade:manage', $systemcontext)) {
            $data->standard = 0;
        }
        $outcome->courseid = !empty($data->standard) ? null : $courseid;
        if (empty($outcome->courseid)) {
            $outcome->courseid = null;
        }
        $outcome->insert();

        $data = file_postupdate_standard_editor($data, 'description', $editoroptions, $systemcontext, 'grade', 'outcome', $outcome->id);
        $DB->set_field($outcome->table, 'description', $data->description, array('id'=>$outcome->id));
    } else {
        $data = file_postupdate_standard_editor($data, 'description', $editoroptions, $systemcontext, 'grade', 'outcome', $id);
        grade_outcome::set_properties($outcome, $data);
        if (isset($data->standard)) {
            $outcome->courseid = !empty($data->standard) ? null : $courseid;
        } else {
            unset($outcome->courseid);         }
        $outcome->update();
    }

    redirect($returnurl);
}

if ($courseid) {
    print_grade_page_head($courseid, 'outcome', 'edit', $heading);
} else {
    echo $OUTPUT->header();
}

if (!grade_scale::fetch_all_local($courseid) && !grade_scale::fetch_all_global()) {
    echo $OUTPUT->confirm(get_string('noscales', 'grades'), $CFG->wwwroot.'/grade/edit/scale/edit.php?courseid='.$courseid, $returnurl);
    echo $OUTPUT->footer();
    die();
}

$mform->display();
echo $OUTPUT->footer();
