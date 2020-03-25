<?php


namespace core\task;


class completion_regular_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskcompletionregular', 'admin');
    }

    
    public function execute() {
        global $CFG;

        if ($CFG->enablecompletion) {
                        require_once($CFG->dirroot.'/completion/cron.php');
            completion_cron_criteria();
            completion_cron_completions();
        }
    }

}
