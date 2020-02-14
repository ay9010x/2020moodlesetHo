<?php



defined('MOODLE_INTERNAL') || die();

function xmldb_folder_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager(); 
        
        
        

        if ($oldversion < 2016020201) {
        $table = new xmldb_table('folder');
        $field = new xmldb_field('showdownloadfolder', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'showexpanded');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field, 'showdownloadfolder');
        }

        upgrade_mod_savepoint(true, 2016020201, 'folder');
    }

            
    if($oldversion < 2017020101){
        $table = new xmldb_table('folder');
        $field = new xmldb_field('assign', XMLDB_TYPE_INTEGER, '10');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2017020101, 'folder');
    }
    return true;
}
