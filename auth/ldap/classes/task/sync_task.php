<?php


namespace auth_ldap\task;


class sync_task extends \core\task\scheduled_task {

    
    public function get_name() {
        return get_string('synctask', 'auth_ldap');
    }

    
    public function execute() {
        global $CFG;
        if (is_enabled_auth('ldap')) {
            $auth = get_auth_plugin('ldap');
            $auth->sync_users(true);
        }
    }

}
