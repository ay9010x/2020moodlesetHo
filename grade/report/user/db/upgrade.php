<?php




function xmldb_gradereport_user_upgrade($oldversion) {

    if ($oldversion < 2014101500) {
                set_config('grade_report_user_showweight', 1);

                upgrade_plugin_savepoint(true, 2014101500, 'gradereport', 'user');
    }

        
        
        
        
    return true;
}
