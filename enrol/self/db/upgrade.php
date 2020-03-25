<?php



defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_self_upgrade($oldversion) {
    global $CFG;

        
        
        
        
    if ($oldversion < 2016052301) {
        global $DB;
                $managerroles = get_archetype_roles('manager');
        if (!empty($managerroles)) {
                        foreach ($managerroles as $role) {
                $DB->execute("DELETE
                                FROM {role_capabilities}
                               WHERE roleid = ? AND capability = ? AND permission = ?",
                        array($role->id, 'enrol/self:holdkey', CAP_PROHIBIT));
            }
        }
        upgrade_plugin_savepoint(true, 2016052301, 'enrol', 'self');

    }

    return true;
}
