<?php


namespace core\task;


class completion_daily_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskcompletiondaily', 'admin');
    }

    
    public function execute() {
        global $CFG;

        if ($CFG->enablecompletion) {
                        require_once($CFG->dirroot.'/completion/cron.php');
            completion_cron_mark_started();
        }
    }

}
