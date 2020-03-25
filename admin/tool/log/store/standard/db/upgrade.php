<?php



defined('MOODLE_INTERNAL') || die();

function xmldb_logstore_standard_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

        
        
        
    if ($oldversion < 2016041200) {
                upgrade_set_timeout(3600);

                $table = new xmldb_table('logstore_standard_log');
        $key = new xmldb_key('contextid', XMLDB_KEY_FOREIGN, array('contextid'), 'context', array('id'));

                $dbman->add_key($table, $key);

                upgrade_plugin_savepoint(true, 2016041200, 'logstore', 'standard');
    }

        
    return true;
}
