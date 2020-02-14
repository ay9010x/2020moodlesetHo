<?php




require('../../config.php');
require_once("$CFG->dirroot/mod/folder/locallib.php");
require_once("$CFG->dirroot/mod/folder/edit_form.php");
require_once("$CFG->dirroot/repository/lib.php");

$id = required_param('id', PARAM_INT);  
$cm = get_coursemodule_from_id('folder', $id, 0, true, MUST_EXIST);
$context = context_module::instance($cm->id, MUST_EXIST);
$folder = $DB->get_record('folder', array('id'=>$cm->instance), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_login($course, false, $cm);
require_capability('mod/folder:managefiles', $context);

$PAGE->set_url('/mod/folder/edit.php', array('id' => $cm->id));
$PAGE->set_title($course->shortname.': '.$folder->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($folder);

$data = new stdClass();
$data->id = $cm->id;
$maxbytes = get_user_max_upload_file_size($context, $CFG->maxbytes);
$options = array('subdirs' => 1, 'maxbytes' => $maxbytes, 'maxfiles' => -1, 'accepted_types' => '*');
file_prepare_standard_filemanager($data, 'files', $options, $context, 'mod_folder', 'content', 0);

$mform = new mod_folder_edit_form(null, array('data'=>$data, 'options'=>$options));
if ($folder->display == FOLDER_DISPLAY_INLINE) {
    $redirecturl = course_get_url($cm->course, $cm->sectionnum);
} else {
    $redirecturl = new moodle_url('/mod/folder/view.php', array('id' => $cm->id));
}

if ($mform->is_cancelled()) {
    redirect($redirecturl);

} else if ($formdata = $mform->get_data()) {
    $formdata = file_postupdate_standard_filemanager($formdata, 'files', $options, $context, 'mod_folder', 'content', 0);
    $DB->set_field('folder', 'revision', $folder->revision+1, array('id'=>$folder->id));

        $folder->revision = $folder->revision + 1;

    $params = array(
        'context' => $context,
        'objectid' => $folder->id
    );
    $event = \mod_folder\event\folder_updated::create($params);
    $event->add_record_snapshot('folder', $folder);
    $event->trigger();

    redirect($redirecturl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($folder->name));
echo $OUTPUT->box_start('generalbox foldertree');
$mform->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
