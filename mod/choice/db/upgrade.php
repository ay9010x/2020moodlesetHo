<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_choice_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014051201) {

                $table = new xmldb_table('choice');
        $field = new xmldb_field('allowmultiple', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'allowupdate');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2014051201, 'choice');
    }

        
    if ($oldversion < 2014111001) {

                $table = new xmldb_table('choice');
        $field = new xmldb_field('showpreview', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'timeclose');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2014111001, 'choice');
    }

    if ($oldversion < 2014111002) {

                $table = new xmldb_table('choice');
        $field = new xmldb_field('includeinactive', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1', 'showunanswered');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2014111002, 'choice');
    }

        
        
        
    return true;
}
