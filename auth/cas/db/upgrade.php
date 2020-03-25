<?php



defined('MOODLE_INTERNAL') || die();


function xmldb_auth_cas_upgrade($oldversion) {
    global $CFG, $DB;

        
    if ($oldversion < 2014111001) {
                if (is_enabled_auth('cas')
                && ($DB->get_field('config_plugins', 'value', array('name' => 'user_type', 'plugin' => 'auth/cas')) === 'ad')
                && ($DB->get_field('config_plugins', 'value', array('name' => 'objectclass', 'plugin' => 'auth/cas')) === '')) {
                        set_config('objectclass', 'user', 'auth/cas');
        }

        upgrade_plugin_savepoint(true, 2014111001, 'auth', 'cas');
    }

        
        
        
    return true;
}
