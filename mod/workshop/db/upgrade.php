<?php



defined('MOODLE_INTERNAL') || die();


function xmldb_workshop_upgrade($oldversion) {
    global $CFG, $DB;

        
        
        
    $dbman = $DB->get_manager();

    if ($oldversion < 2016022200) {
                $table = new xmldb_table('workshop');
        $field = new xmldb_field('submissionfiletypes', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'nattachments');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                $field = new xmldb_field('overallfeedbackfiletypes',
                XMLDB_TYPE_CHAR, '255', null, null, null, null, 'overallfeedbackfiles');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2016022200, 'workshop');
    }

        
    return true;
}
