<?php

function xmldb_local_mooccourse_install() {
    global $CFG, $DB;
    $dbman = $DB->get_manager();
    $table = new xmldb_table('course');
    
    $field = new xmldb_field('enddate', XMLDB_TYPE_INTEGER, 10, null, null, null, null);
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    $field = new xmldb_field('semester', XMLDB_TYPE_TEXT, null, null, null, null, null);
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    $field = new xmldb_field('forbiddens', XMLDB_TYPE_CHAR, 254, null, null, null, null);
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
        $field = new xmldb_field('outline', XMLDB_TYPE_TEXT, null, null, null, null, null);
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    $field = new xmldb_field('point', XMLDB_TYPE_TEXT, null, null, null, null, null);
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    $field = new xmldb_field('officehour', XMLDB_TYPE_TEXT, null, null, null, null, null);
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    
    return true;
}