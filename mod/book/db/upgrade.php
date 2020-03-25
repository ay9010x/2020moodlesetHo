<?php


defined('MOODLE_INTERNAL') || die;


function xmldb_book_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

        
    if ($oldversion < 2014111800) {

                $table = new xmldb_table('book');
        $field = new xmldb_field('navstyle', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1', 'numbering');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2014111800, 'book');
    }

        
        
        
    return true;
}
