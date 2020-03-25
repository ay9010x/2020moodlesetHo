<?php



require('../../../../../config.php');
require_once('manage_form.php');
require_once($CFG->libdir.'/filestorage/file_storage.php');

$itemid = required_param('itemid', PARAM_INT);
$maxbytes = optional_param('maxbytes', 0, PARAM_INT);
$subdirs = optional_param('subdirs', 0, PARAM_INT);
$accepted_types = optional_param('accepted_types', '*', PARAM_RAW); $return_types = optional_param('return_types', null, PARAM_INT);
$areamaxbytes = optional_param('areamaxbytes', FILE_AREA_MAX_BYTES_UNLIMITED, PARAM_INT);
$contextid = optional_param('context', SYSCONTEXTID, PARAM_INT);

$context = context::instance_by_id($contextid);
if ($context->contextlevel == CONTEXT_MODULE) {
        $cm = $DB->get_record('course_modules', array('id' => $context->instanceid));
    require_login($cm->course, true, $cm);
} else if (($coursecontext = $context->get_course_context(false)) && $coursecontext->id != SITEID) {
        require_login($coursecontext->instanceid);
    $PAGE->set_context($context);
} else {
        require_login();
    $PAGE->set_context($context);
}
if (isguestuser()) {
        print_error('noguest');
}

$title = get_string('manageareafiles', 'tinymce_managefiles');

$PAGE->set_url('/lib/editor/tinymce/plugins/managefiles/manage.php');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('popup');

if ($return_types !== null) {
    $return_types = $return_types ^ 1; }

$options = array(
    'subdirs' => $subdirs,
    'maxbytes' => $maxbytes,
    'maxfiles' => -1,
    'accepted_types' => $accepted_types,
    'areamaxbytes' => $areamaxbytes,
    'return_types' => $return_types,
    'context' => $context
);

$usercontext = context_user::instance($USER->id);
$fs = get_file_storage();
$files = $fs->get_directory_files($usercontext->id, 'user', 'draft', $itemid, '/', !empty($subdirs), false);
$filenames = array();
foreach ($files as $file) {
    $filenames[] = ltrim($file->get_filepath(), '/'). $file->get_filename();
}

$mform = new tinymce_managefiles_manage_form(null,
        array('options' => $options, 'draftitemid' => $itemid, 'files' => $filenames),
        'post', '', array('id' => 'tinymce_managefiles_manageform'));

if ($data = $mform->get_data()) {
    if (!empty($data->deletefile)) {
        foreach (array_keys($data->deletefile) as $filename) {
            $filepath = '/';
            if (!empty($subdirs) && strlen(dirname($filename))  ) {
                $filepath = '/'. dirname($filename). '/';
            }
            if ($file = $fs->get_file($usercontext->id, 'user', 'draft', $itemid,
                    $filepath, basename($filename))) {
                $file->delete();
            }
        }
        $filenames = array_diff($filenames, array_keys($data->deletefile));
        $mform = new tinymce_managefiles_manage_form(null,
                array('options' => $options, 'draftitemid' => $itemid, 'files' => $filenames),
                'post', '', array('id' => 'tinymce_managefiles_manageform'));
    }
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
