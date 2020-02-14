<?php



defined('MOODLE_INTERNAL') || die();


function xmldb_assignfeedback_editpdf_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

        
        
        
    if ($oldversion < 2016021600) {

                $table = new xmldb_table('assignfeedback_editpdf_queue');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('submissionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('submissionattempt', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_plugin_savepoint(true, 2016021600, 'assignfeedback', 'editpdf');
    }

        
    return true;
}
