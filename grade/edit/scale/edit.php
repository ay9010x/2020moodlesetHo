<?php



require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/lib.php';
require_once 'edit_form.php';

$courseid = optional_param('courseid', 0, PARAM_INT);
$id       = optional_param('id', 0, PARAM_INT);

$PAGE->set_url('/grade/edit/scale/edit.php', array('id' => $id, 'courseid' => $courseid));
$PAGE->set_pagelayout('admin');
navigation_node::override_active_url(new moodle_url('/grade/edit/scale/index.php',
    array('id' => $courseid)));

$systemcontext = context_system::instance();
$heading = '';

if ($id) {
    $heading = get_string('editscale', 'grades');

        if (!$scale_rec = $DB->get_record('scale', array('id' => $id))) {
        print_error('invalidscaleid');
    }
    if ($scale_rec->courseid) {
        $scale_rec->standard = 0;
        if (!$course = $DB->get_record('course', array('id' => $scale_rec->courseid))) {
            print_error('invalidcourseid');
        }
        require_login($course);
        $context = context_course::instance($course->id);
        require_capability('moodle/course:managescales', $context);
        $courseid = $course->id;
    } else {
        if ($courseid) {
            if (!$course = $DB->get_record('course', array('id' => $courseid))) {
                print_error('invalidcourseid');
            }
        }
        $scale_rec->standard = 1;
        $scale_rec->courseid = $courseid;
        require_login($courseid);
        require_capability('moodle/course:managescales', $systemcontext);
    }

} else if ($courseid){
    $heading = get_string('addscale', 'grades');
        if (!$course = $DB->get_record('course', array('id' => $courseid))) {
        print_error('nocourseid');
    }
    $scale_rec = new stdClass();
    $scale_rec->standard = 0;
    $scale_rec->courseid = $courseid;
    require_login($course);
    $context = context_course::instance($course->id);
    require_capability('moodle/course:managescales', $context);

} else {
        $scale_rec = new stdClass();
    $scale_rec->standard = 1;
    $scale_rec->courseid = 0;
    require_login();
    require_capability('moodle/course:managescales', $systemcontext);
}

if (!$courseid) {
    require_once $CFG->libdir.'/adminlib.php';
    admin_externalpage_setup('scales');
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

if (!empty($scale_rec->id)) {
    $editoroptions['subdirs'] = file_area_contains_subdirs($systemcontext, 'grade', 'scale', $scale_rec->id);
    $scale_rec = file_prepare_standard_editor($scale_rec, 'description', $editoroptions, $systemcontext, 'grade', 'scale', $scale_rec->id);
} else {
    $editoroptions['subdirs'] = false;
    $scale_rec = file_prepare_standard_editor($scale_rec, 'description', $editoroptions, $systemcontext, 'grade', 'scale', null);
}
$mform = new edit_scale_form(null, compact('gpr', 'editoroptions'));

$mform->set_data($scale_rec);

if ($mform->is_cancelled()) {
    redirect($returnurl);

} else if ($data = $mform->get_data()) {
    $scale = new grade_scale(array('id'=>$id));
    $data->userid = $USER->id;

    if (empty($scale->id)) {
        $data->description = $data->description_editor['text'];
        $data->descriptionformat = $data->description_editor['format'];
        grade_scale::set_properties($scale, $data);
        if (!has_capability('moodle/grade:manage', $systemcontext)) {
            $data->standard = 0;
        }
        $scale->courseid = !empty($data->standard) ? 0 : $courseid;
        $scale->insert();
        $data = file_postupdate_standard_editor($data, 'description', $editoroptions, $systemcontext, 'grade', 'scale', $scale->id);
        $DB->set_field($scale->table, 'description', $data->description, array('id'=>$scale->id));
    } else {
        $data = file_postupdate_standard_editor($data, 'description', $editoroptions, $systemcontext, 'grade', 'scale', $id);
        grade_scale::set_properties($scale, $data);
        if (isset($data->standard)) {
            $scale->courseid = !empty($data->standard) ? 0 : $courseid;
        } else {
            unset($scale->courseid);         }
        $scale->update();
    }
    redirect($returnurl);
}

print_grade_page_head($COURSE->id, 'scale', null, $heading, false, false, false);

$mform->display();

echo $OUTPUT->footer();
