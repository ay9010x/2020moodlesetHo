<?php




defined('MOODLE_INTERNAL') || die();



function xmldb_quiz_responses_install() {
    global $DB;

    $record = new stdClass();
    $record->name         = 'responses';
    $record->displayorder = '9000';

    $DB->insert_record('quiz_reports', $record);
}
