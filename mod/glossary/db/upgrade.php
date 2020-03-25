<?php

function xmldb_glossary_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

        
        
    if ($oldversion < 2015060200) {

                $table = new xmldb_table('glossary_formats');
        $field = new xmldb_field('showtabs', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'showgroup');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2015060200, 'glossary');
    }

        
        
    return true;
}
