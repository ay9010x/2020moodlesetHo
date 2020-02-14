<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_data_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

        
    if ($oldversion < 2015030900) {
                $table = new xmldb_table('data_fields');
        $field = new xmldb_field('required', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'description');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2015030900, 'data');
    }

        
    if ($oldversion < 2015092200) {

                $table = new xmldb_table('data');
        $field = new xmldb_field('manageapproved', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1', 'approval');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2015092200, 'data');
    }

        
    if ($oldversion < 2016030300) {

                $table = new xmldb_table('data');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'notification');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2016030300, 'data');
    }


        
    return true;
}
