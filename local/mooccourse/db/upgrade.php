<?php

function xmldb_local_mooccourse_upgrade($oldversion=0) {
    global $CFG, $DB;
    $result = true;
    $dbman = $DB->get_manager();
    if($oldversion < 2017010102){
        $table = new xmldb_table('course');
        $field = new xmldb_field('enddate', XMLDB_TYPE_INTEGER, 10, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('semester', XMLDB_TYPE_TEXT, null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('whetheropen', XMLDB_TYPE_INTEGER, 1, null, null, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017010102, 'local', 'mooccourse');
    }
    if($oldversion < 2017010106){
        $table = new xmldb_table('course');
        $field = new xmldb_field('whetheropen', XMLDB_TYPE_INTEGER, 1, null, null, null, 0);
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        $field = new xmldb_field('forbiddens', XMLDB_TYPE_CHAR, 254, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017010106, 'local', 'mooccourse');
    }
    return $result;
}