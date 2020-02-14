<?php




defined('MOODLE_INTERNAL') || die();



function xmldb_quiz_overview_install() {
    global $DB;

    $record = new stdClass();
    $record->name         = 'overview';
    $record->displayorder = '10000';

    $DB->insert_record('quiz_reports', $record);
}
