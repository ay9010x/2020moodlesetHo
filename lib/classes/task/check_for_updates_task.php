<?php


namespace core\task;


class check_for_updates_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskcheckforupdates', 'admin');
    }

    
    public function execute() {
        global $CFG;
                if (empty($CFG->disableupdatenotifications)) {
            $updateschecker = \core\update\checker::instance();
            $updateschecker->cron();
        }

    }

}
