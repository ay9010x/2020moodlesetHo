<?php




defined('MOODLE_INTERNAL') || die();



function xmldb_quiz_grading_install() {
    global $DB;

    $record = new stdClass();
    $record->name         = 'grading';
    $record->displayorder = '6000';
    $record->capability   = 'mod/quiz:grade';

    $DB->insert_record('quiz_reports', $record);
}
