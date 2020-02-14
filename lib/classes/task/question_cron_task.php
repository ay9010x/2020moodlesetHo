<?php


namespace core\task;


class question_cron_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskquestioncron', 'admin');
    }

    
    public function execute() {
        global $CFG;

                require_once($CFG->libdir . '/questionlib.php');
        \question_bank::cron();

    }

}
