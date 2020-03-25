<?php


namespace auth_cas\task;


class sync_task extends \core\task\scheduled_task {

    
    public function get_name() {
        return get_string('synctask', 'auth_cas');
    }

    
    public function execute() {
        global $CFG;
        if (is_enabled_auth('cas')) {
            $auth = get_auth_plugin('cas');
            $auth->sync_users(true);
        }
    }

}
