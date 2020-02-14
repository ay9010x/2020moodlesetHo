<?php



defined('MOODLE_INTERNAL') || die();


function quiz_statistics_question_preview_pluginfile($previewcontext, $questionid,
        $filecontext, $filecomponent, $filearea, $args, $forcedownload, $options = array()) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/quiz/locallib.php');

    list($context, $course, $cm) = get_context_info_array($previewcontext->id);
    require_login($course, false, $cm);

            require_capability('quiz/statistics:view', $context);

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/{$filecontext->id}/{$filecomponent}/{$filearea}/{$relativepath}";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        send_file_not_found();
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
}


function quiz_statistics_cron() {
    global $DB;

    mtrace("\n  Cleaning up old quiz statistics cache records...", '');

    $expiretime = time() - 5*HOURSECS;
    $DB->delete_records_select('quiz_statistics', 'timemodified < ?', array($expiretime));

    return true;
}
