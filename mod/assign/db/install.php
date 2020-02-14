<?php

defined('MOODLE_INTERNAL') || die;

function xmldb_assign_install() {
    global $DB;
    $dbman = $DB->get_manager();
    
    
    $table = new xmldb_table('assign_user_flags');
    $field = new xmldb_field('patternstate', XMLDB_TYPE_INTEGER, '10');
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
}
