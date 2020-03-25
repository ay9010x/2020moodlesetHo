<?php





define('AJAX_SCRIPT', true);


define('NO_MOODLE_COOKIES', true);

require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once($CFG->dirroot . '/webservice/lib.php');
$filepath = optional_param('filepath', '/', PARAM_PATH);
$filearea = optional_param('filearea', 'private', PARAM_ALPHA);
$itemid = optional_param('itemid', 0, PARAM_INT);

echo $OUTPUT->header();

$token = required_param('token', PARAM_ALPHANUM);
$webservicelib = new webservice();
$authenticationinfo = $webservicelib->authenticate_user($token);
$fileuploaddisabled = empty($authenticationinfo['service']->uploadfiles);
if ($fileuploaddisabled) {
    throw new webservice_access_exception('Web service file upload must be enabled in external service settings');
}

$context = context_user::instance($USER->id);

if ($filearea === 'private') {
    throw new moodle_exception('privatefilesupload');
}

if ($filearea !== 'draft') {
        throw new file_exception('error');
}

$fs = get_file_storage();

$totalsize = 0;
$files = array();
foreach ($_FILES as $fieldname=>$uploaded_file) {
        if (!empty($_FILES[$fieldname]['error'])) {
        switch ($_FILES[$fieldname]['error']) {
        case UPLOAD_ERR_INI_SIZE:
            throw new moodle_exception('upload_error_ini_size', 'repository_upload');
            break;
        case UPLOAD_ERR_FORM_SIZE:
            throw new moodle_exception('upload_error_form_size', 'repository_upload');
            break;
        case UPLOAD_ERR_PARTIAL:
            throw new moodle_exception('upload_error_partial', 'repository_upload');
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new moodle_exception('upload_error_no_file', 'repository_upload');
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            throw new moodle_exception('upload_error_no_tmp_dir', 'repository_upload');
            break;
        case UPLOAD_ERR_CANT_WRITE:
            throw new moodle_exception('upload_error_cant_write', 'repository_upload');
            break;
        case UPLOAD_ERR_EXTENSION:
            throw new moodle_exception('upload_error_extension', 'repository_upload');
            break;
        default:
            throw new moodle_exception('nofile');
        }
    }

        \core\antivirus\manager::scan_file($_FILES[$fieldname]['tmp_name'], $_FILES[$fieldname]['name'], true);

    $file = new stdClass();
    $file->filename = clean_param($_FILES[$fieldname]['name'], PARAM_FILE);
        if (($_FILES[$fieldname]['size'] > get_max_upload_file_size($CFG->maxbytes))) {
                        $file->errortype = 'fileoversized';
        $file->error = get_string('maxbytes', 'error');
    } else {
        $file->filepath = $_FILES[$fieldname]['tmp_name'];
                $totalsize += $_FILES[$fieldname]['size'];
    }
    $files[] = $file;
}

$fs = get_file_storage();

if ($filearea == 'draft' && $itemid <= 0) {
    $itemid = file_get_unused_draft_itemid();
}

$maxareabytes = FILE_AREA_MAX_BYTES_UNLIMITED;
$maxupload = get_user_max_upload_file_size($context, $CFG->maxbytes);

if ($maxupload !== USER_CAN_IGNORE_FILE_SIZE_LIMITS && $totalsize > $maxupload) {
    throw new file_exception('userquotalimit');
}

$results = array();
foreach ($files as $file) {
    if (!empty($file->error)) {
                $results[] = $file;
        continue;
    }
    $file_record = new stdClass;
    $file_record->component = 'user';
    $file_record->contextid = $context->id;
    $file_record->userid    = $USER->id;
    $file_record->filearea  = $filearea;
    $file_record->filename = $file->filename;
    $file_record->filepath  = $filepath;
    $file_record->itemid    = $itemid;
    $file_record->license   = $CFG->sitedefaultlicense;
    $file_record->author    = fullname($authenticationinfo['user']);
    $file_record->source    = serialize((object)array('source' => $file->filename));

        $existingfile = $fs->file_exists($file_record->contextid, $file_record->component, $file_record->filearea,
                $file_record->itemid, $file_record->filepath, $file_record->filename);
    if ($existingfile) {
        $file->errortype = 'filenameexist';
        $file->error = get_string('filenameexist', 'webservice', $file->filename);
        $results[] = $file;
    } else {
        $stored_file = $fs->create_file_from_pathname($file_record, $file->filepath);
        $results[] = $file_record;
    }
}
echo json_encode($results);
