<?php



defined('MOODLE_INTERNAL') || die();


function xmldb_scorm_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014072500) {

                $table = new xmldb_table('scorm');
        $field = new xmldb_field('autocommit', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'displayactivityname');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2014072500, 'scorm');
    }

        
    if ($oldversion < 2015031800) {

                        $alreadyset = $DB->record_exists('config_plugins', array('plugin' => 'scorm', 'name' => 'aiccuserid'));
        if (!$alreadyset) {
            $hasaicc = $DB->record_exists('scorm', array('version' => 'AICC'));
            if ($hasaicc) {
                set_config('aiccuserid', 0, 'scorm');
            } else {
                                set_config('aiccuserid', 1, 'scorm');
            }
        }
                upgrade_mod_savepoint(true, 2015031800, 'scorm');
    }

        
    if ($oldversion < 2015091400) {
        $table = new xmldb_table('scorm');

                $field = new xmldb_field('forcecompleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'maxattempt');
                $dbman->change_field_default($table, $field);

                $field = new xmldb_field('displaycoursestructure', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'displayattemptstatus');
                $dbman->change_field_default($table, $field);

                upgrade_mod_savepoint(true, 2015091400, 'scorm');
    }

        
        if ($oldversion < 2016021000) {
        $table = new xmldb_table('scorm');

        $field = new xmldb_field('masteryoverride', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'lastattemptlock');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2016021000, 'scorm');
    }

        
    return true;
}
