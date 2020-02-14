<?php



defined('MOODLE_INTERNAL') || die();


function xmldb_quiz_statistics_install() {
    global $DB;

    $dbman = $DB->get_manager();

    $record = new stdClass();
    $record->name         = 'statistics';
    $record->displayorder = 8000;
    $record->capability   = 'quiz/statistics:view';

    if ($dbman->table_exists('quiz_reports')) {
        $DB->insert_record('quiz_reports', $record);
    } else {
        $DB->insert_record('quiz_report', $record);
    }
}
