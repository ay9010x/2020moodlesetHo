<?php



defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_manual_upgrade($oldversion) {
    global $CFG;

        
        
    if ($oldversion < 2015091500) {
                set_config('enrolstart', 3, 'enrol_manual');
        upgrade_plugin_savepoint(true, 2015091500, 'enrol', 'manual');
    }

        
        
    return true;
}
