<?php



require_once(__DIR__ . "/../../config.php");

$id = required_param('id', PARAM_INT);  $cm = get_coursemodule_from_id('folder', $id, 0, true, MUST_EXIST);

$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/folder:view', $context);

$folder = $DB->get_record('folder', array('id' => $cm->instance), '*', MUST_EXIST);

$downloadable = folder_archive_available($folder, $cm);
if (!$downloadable) {
    print_error('cannotdownloaddir', 'repository');
}

folder_downloaded($folder, $course, $cm, $context);

$fs = get_file_storage();
$file = $fs->get_file($context->id, 'mod_folder', 'content', 0, '/', '.');
if (!$file) {
    print_error('cannotdownloaddir', 'repository');
}

$zipper   = get_file_packer('application/zip');
$filename = clean_filename($folder->name . "-" . date("Ymd")) . ".zip";
$temppath = make_request_directory() . $filename;

if ($zipper->archive_to_pathname(array('/' => $file), $temppath)) {
    send_temp_file($temppath, $filename);
} else {
    print_error('cannotdownloaddir', 'repository');
}
