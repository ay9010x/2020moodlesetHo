<?php



defined('MOODLE_INTERNAL') || die();


function xmldb_editor_atto_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014081400) {

                $table = new xmldb_table('editor_atto_autosave');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('elementid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('pagehash', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('drafttext', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('draftid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('pageinstance', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('autosave_uniq_key', XMLDB_KEY_UNIQUE, array('elementid', 'contextid', 'userid', 'pagehash'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_plugin_savepoint(true, 2014081400, 'editor', 'atto');
    }

    if ($oldversion < 2014081900) {

                $table = new xmldb_table('editor_atto_autosave');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'pageinstance');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_plugin_savepoint(true, 2014081900, 'editor', 'atto');
    }

        
        
        
        
    return true;
}
