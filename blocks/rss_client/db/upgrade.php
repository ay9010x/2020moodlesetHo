<?php


defined('MOODLE_INTERNAL') || die();


function xmldb_block_rss_client_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2015071700) {
                $table = new xmldb_table('block_rss_client');
                $field = new xmldb_field('skiptime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'url');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
                $field = new xmldb_field('skipuntil', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'skiptime');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_block_savepoint(true, 2015071700, 'rss_client');
    }

        
        
    return true;
}
