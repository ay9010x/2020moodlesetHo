<?php
   
require(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/course/format/lib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once('lib.php');
require_once('locallib.php');
require_once('editinfo_form.php');

$id       = required_param('id', PARAM_INT);
$returnto = optional_param('returnto', 0, PARAM_ALPHANUM);

$PAGE->set_pagelayout('course');
$params = array('id'=>$id);

$PAGE->set_url('/local/mooccourse/editinfo.php', $params);

if ($id == SITEID){
    print_error('cannoteditsiteform');
}
$course = course_get_format($id)->get_course();
$category = $DB->get_record('course_categories', array('id'=>$course->category), '*', MUST_EXIST);
$context = context_course::instance($course->id);
require_login($course);
require_capability('moodle/course:update', $context);

$overviewfilesoptions = course_overviewfiles_options($course);
if ($overviewfilesoptions) {
    file_prepare_standard_filemanager($course, 'overviewfiles', $overviewfilesoptions, $context, 'course', 'overviewfiles', 0);
    }

$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes'=>$course->maxbytes, 'trusttext'=>false, 'noclean'=>true);
$editoroptions['context'] = $context;
$course = file_prepare_standard_editor($course, 'summary', $editoroptions, $context, 'course', 'summary', 0);

$course->outlineformat = $course->pointformat = $course->officehourformat = $course->bibleformat = $course->qnaformat = FORMAT_HTML;   // by YCJ
$course = file_prepare_standard_editor($course, 'outline', $editoroptions, $context, 'course', 'outline', 0);
$course = file_prepare_standard_editor($course, 'point', $editoroptions, $context, 'course', 'point', 0);
$course = file_prepare_standard_editor($course, 'officehour', $editoroptions, $context, 'course', 'officehour', 0);
$course = file_prepare_standard_editor($course, 'bible', $editoroptions, $context, 'course', 'bible', 0);    // by YCJ
$course = file_prepare_standard_editor($course, 'qna', $editoroptions, $context, 'course', 'qna', 0);    // by YCJ

$aliases = $DB->get_records('role_names', array('contextid'=>$context->id));
foreach($aliases as $alias) {
    $course->{'role_'.$alias->roleid} = $alias->name;
}

$editform = new local_mooccourse_edit_info_form(NULL, array('course'=>$course, 'editoroptions'=>$editoroptions, 'returnto' => $returnto));
if ($editform->is_cancelled()) {
    redirect(new moodle_url('/blocks/course_menu/information.php', array('id' => $course->id)));
} else if ($data = $editform->get_data()) {
    $data->forbiddens = '';
    if(!empty($data->forbidden)){
        foreach($data->forbidden as $key => $value){
            $data->forbiddens .= $key.',';
        }
    }
    
    local_mooccourse_update_courseinfo($data, $editoroptions);
    local_mooccourse_information_standard_log_update($course);
    redirect(new moodle_url('/blocks/course_menu/information.php', array('id' => $course->id)));
}

$str_editcoursesettings = get_string("editcourseinfo", 'local_mooccourse');

$PAGE->navbar->add($str_editcoursesettings);
$title = $str_editcoursesettings;
$fullname = $course->fullname;
$PAGE->set_title($title);
$PAGE->set_heading($fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($str_editcoursesettings);
$editform->display();
echo $OUTPUT->footer();