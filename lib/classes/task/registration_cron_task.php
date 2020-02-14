<?php


namespace core\task;


class registration_cron_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskregistrationcron', 'admin');
    }

    
    public function execute() {
        global $CFG;

        require_once($CFG->dirroot . '/' . $CFG->admin . '/registration/lib.php');
        $registrationmanager = new \registration_manager();
        $registrationmanager->cron();
    }

}
