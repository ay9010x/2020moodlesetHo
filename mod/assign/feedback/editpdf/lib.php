<?php



defined('MOODLE_INTERNAL') || die();


function assignfeedback_editpdf_pluginfile($course,
                                           $cm,
                                           context $context,
                                           $filearea,
                                           $args,
                                           $forcedownload) {
    global $USER, $DB, $CFG;

    if ($context->contextlevel == CONTEXT_MODULE) {

        require_login($course, false, $cm);
        $itemid = (int)array_shift($args);

        if (!$assign = $DB->get_record('assign', array('id'=>$cm->instance))) {
            return false;
        }

        $record = $DB->get_record('assign_grades', array('id'=>$itemid), 'userid,assignment', MUST_EXIST);
        $userid = $record->userid;
        if ($assign->id != $record->assignment) {
            return false;
        }

                if ($USER->id != $userid and !has_capability('mod/assign:grade', $context)) {
            return false;
        }

        $relativepath = implode('/', $args);

        $fullpath = "/{$context->id}/assignfeedback_editpdf/$filearea/$itemid/$relativepath";

        $fs = get_file_storage();
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }
                send_stored_file($file, 0, 0, true);    }

}
