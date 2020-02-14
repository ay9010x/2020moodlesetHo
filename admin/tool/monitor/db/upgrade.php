<?php



defined('MOODLE_INTERNAL') || die();


function xmldb_tool_monitor_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014102000) {

                $table = new xmldb_table('tool_monitor_subscriptions');
        $field = new xmldb_field('lastnotificationsent', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timecreated');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_plugin_savepoint(true, 2014102000, 'tool', 'monitor');
    }

        
        
        
        
    if ($oldversion < 2016052305) {

                $table = new xmldb_table('tool_monitor_subscriptions');
        $field = new xmldb_field('inactivedate', XMLDB_TYPE_INTEGER, '10', null, true, null, 0, 'lastnotificationsent');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_plugin_savepoint(true, 2016052305, 'tool', 'monitor');
    }

    return true;
}
