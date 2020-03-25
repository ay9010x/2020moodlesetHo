<?php


namespace core\task;


class plagiarism_cron_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskplagiarismcron', 'admin');
    }

    
    public function execute() {
        global $CFG;

        require_once($CFG->libdir.'/plagiarismlib.php');
        plagiarism_cron();
    }

}
