<?php



define('AJAX_SCRIPT', true);
define('NO_MOODLE_COOKIES', true);

use \assignfeedback_editpdf\document_services;
require_once('../../../../config.php');

try {
    $assignmentid = required_param('assignmentid', PARAM_INT);
    $userid = required_param('userid', PARAM_INT);
    $attemptnumber = required_param('attemptnumber', PARAM_INT);

        require_once($CFG->dirroot . '/mod/assign/locallib.php');
    $cm = get_coursemodule_from_instance('assign', $assignmentid, 0, false, MUST_EXIST);
    $context = context_module::instance($cm->id);
    $assignment = new assign($context, null, null);

        $grade = $assignment->get_user_grade($userid, false, $attemptnumber);

        if (empty($grade)) {
        throw new coding_exception('grade not found');
    }

        $component = 'assignfeedback_editpdf';
    $filearea = document_services::PAGE_IMAGE_FILEAREA;
    $filepath = '/';
    $fs = get_file_storage();
    $files = $fs->get_directory_files($context->id, $component, $filearea, $grade->id, $filepath);

        echo $OUTPUT->header();
    echo json_encode(count($files));
    echo $OUTPUT->footer();
} catch (Exception $e) {
            if (substr(php_sapi_name(), 0, 3) == 'cgi') {
        header("Status: 500 Internal Server Error");
    } else {
        header('HTTP/1.0 500 Internal Server Error');
    }
    throw new moodle_exception('An exception was caught but can not be returned for security purpose.
        To easily debug, comment the try catch.');
}
