<?php



defined('MOODLE_INTERNAL') || die();


function xmldb_atto_equation_upgrade($oldversion) {
    require_once(__DIR__ . '/upgradelib.php');

    if ($oldversion < 2015083100) {
        atto_equation_update_librarygroup4_setting();

                upgrade_plugin_savepoint(true, 2015083100, 'atto', 'equation');
    }

        
        
    return true;
}