<?php


namespace core\task;


class badges_cron_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskbadgescron', 'admin');
    }

    
    public function execute() {
        global $CFG;
                require_once($CFG->dirroot . '/badges/cron.php');
        badge_cron();
    }

}
