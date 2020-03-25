<?php



defined('MOODLE_INTERNAL') || die();


function xmldb_assignfeedback_editpdf_install() {
    global $CFG;

        $defaultstamps = array('smile.png', 'sad.png', 'tick.png', 'cross.png');

        $filerecord = new stdClass;
    $filerecord->component = 'assignfeedback_editpdf';
    $filerecord->contextid = context_system::instance()->id;
    $filerecord->userid    = get_admin()->id;
    $filerecord->filearea  = 'stamps';
    $filerecord->filepath  = '/';
    $filerecord->itemid    = 0;

    $fs = get_file_storage();

        foreach ($defaultstamps as $stamp) {
        $filerecord->filename = $stamp;
        $fs->create_file_from_pathname($filerecord,
            $CFG->dirroot . '/mod/assign/feedback/editpdf/pix/' . $filerecord->filename);
    }
}
