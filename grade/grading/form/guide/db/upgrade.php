<?php



defined('MOODLE_INTERNAL') || die();


function xmldb_gradingform_guide_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2016051100) {
                $sql = $DB->sql_isempty('gradingform_guide_comments', 'description', true, true);
        $sql .= " OR description IS NULL ";
        $DB->delete_records_select('gradingform_guide_comments', $sql);
                upgrade_plugin_savepoint(true, 2016051100, 'gradingform', 'guide');
    }

        
    return true;
}
