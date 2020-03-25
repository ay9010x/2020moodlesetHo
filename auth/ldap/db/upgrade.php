<?php



defined('MOODLE_INTERNAL') || die();


function xmldb_auth_ldap_upgrade($oldversion) {
    global $CFG, $DB;

        
    if ($oldversion < 2014111001) {
                if (is_enabled_auth('ldap')
                && ($DB->get_field('config_plugins', 'value', array('name' => 'user_type', 'plugin' => 'auth/ldap')) === 'ad')
                && ($DB->get_field('config_plugins', 'value', array('name' => 'objectclass', 'plugin' => 'auth/ldap')) === '')) {
                        set_config('objectclass', 'user', 'auth/ldap');
        }

        upgrade_plugin_savepoint(true, 2014111001, 'auth', 'ldap');
    }

        
        
        
    return true;
}
