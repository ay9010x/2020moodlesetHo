<?php


namespace core\task;


class grade_cron_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskgradecron', 'admin');
    }

    
    public function execute() {
        global $CFG;

        require_once($CFG->libdir.'/gradelib.php');
        grade_cron();
    }

}
