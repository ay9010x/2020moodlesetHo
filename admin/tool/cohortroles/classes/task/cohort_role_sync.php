<?php


namespace tool_cohortroles\task;

use core\task\scheduled_task;
use tool_cohortroles\api;


class cohort_role_sync extends scheduled_task {

    
    public function get_name() {
                return get_string('taskname', 'tool_cohortroles');
    }

    
    public function execute() {
        mtrace('Sync cohort roles...');
        $result = api::sync_all_cohort_roles();

        mtrace('Added ' . count($result['rolesadded']));
        mtrace('Removed ' . count($result['rolesremoved']));
    }
}
