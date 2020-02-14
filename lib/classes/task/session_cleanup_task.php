<?php


namespace core\task;


class session_cleanup_task extends scheduled_task {

    
    public function get_name() {
        return get_string('tasksessioncleanup', 'admin');
    }

    
    public function execute() {
        global $DB;

        $timenow = time();

        \core\session\manager::gc();

                        $DB->delete_records_select('external_tokens', 'lastaccess < :onedayago AND tokentype = :tokentype',
                        array('onedayago' => $timenow - DAYSECS, 'tokentype' => EXTERNAL_TOKEN_EMBEDDED));
    }

}
